<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-700">{{ $t.your_stores || 'Your Stores' }}</h3>
      <a href="/stores/create" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700">{{ $t.add_store || '+ Add Store' }}</a>
    </div>

    <div v-if="message" :class="message.type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'"
      class="border rounded p-3 text-sm mb-4 flex items-center justify-between">
      <span>{{ message.text }}</span>
      <button @click="message = null" class="ml-4 opacity-60 hover:opacity-100">&#x2715;</button>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-400">{{ $t.loading || 'Loading...' }}</div>

    <div v-else-if="stores.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
      {{ $t.no_stores_connect || 'No stores yet.' }}
      <a href="/channels/create" class="text-indigo-600 hover:underline">{{ $t.connect || 'Connect a channel' }}</a>
    </div>

    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.store_name || 'Name' }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.platform || 'Platform' }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.status || 'Status' }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.last_sync || 'Last Sync' }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.products || 'Products' }}</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="store in stores" :key="store.id">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ store.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ store.channel_integration?.channel_type }}</td>
            <td class="px-6 py-4">
              <span :class="syncBadge(store.sync_status)" class="px-2 py-1 rounded text-xs font-medium">
                {{ store.syncing ? ($t.loading || 'syncing...') : store.sync_status }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-400">{{ store.last_synced_at || ($t.never || 'Never') }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ store.products_count ?? '—' }}</td>
            <td class="px-6 py-4 text-right space-x-3">
              <button @click="sync(store)" :disabled="store.syncing"
                class="text-sm text-green-600 hover:underline disabled:opacity-40 font-medium">
                {{ store.syncing ? ($t.importing || 'Importing...') : ($t.import_products || 'Import Products') }}
              </button>
              <a :href="`/stores/${store.id}`" class="text-sm text-indigo-600 hover:underline">{{ $t.view || 'View' }}</a>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="loadingMore" class="text-center py-4 text-gray-400 text-sm">{{ $t.loading || 'Loading...' }}</div>
      <div v-else-if="!hasMore" class="text-center py-3 text-gray-400 text-xs border-t">
        {{ ($t.all_stores_loaded || 'All :total stores loaded').replace(':total', total) }}
      </div>
    </div>

    <div ref="sentinel" class="h-1"></div>
  </div>
</template>

<script>
export default {
  name: 'StoresApp',

  data() {
    return {
      stores: [],
      loading: true,
      loadingMore: false,
      message: null,
      currentPage: 1,
      lastPage: 1,
      total: 0,
      observer: null,
    };
  },

  computed: {
    hasMore() {
      return this.currentPage < this.lastPage;
    },
    $t() { return window.trans || {}; },
  },

  async created() {
    await this.fetchStores(1, true);
  },

  mounted() {
    this.$nextTick(() => {
      this.observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting && this.hasMore && !this.loadingMore) {
          this.fetchStores(this.currentPage + 1, false);
        }
      }, { rootMargin: '200px' });
      if (this.$refs.sentinel) this.observer.observe(this.$refs.sentinel);
    });
  },

  beforeUnmount() {
    this.observer?.disconnect();
  },

  methods: {
    async fetchStores(page = 1, replace = false) {
      if (replace) {
        this.loading = true;
        this.stores = [];
      } else {
        this.loadingMore = true;
      }

      try {
        const data = await window.api(`/api/stores?page=${page}`);
        const rows = data.data.map(s => ({ ...s, syncing: false }));
        this.stores = replace ? rows : [...this.stores, ...rows];
        this.currentPage = data.current_page;
        this.lastPage = data.last_page;
        this.total = data.total;
      } finally {
        this.loading = false;
        this.loadingMore = false;
      }
    },

    async sync(store) {
      store.syncing = true;
      store.sync_status = 'syncing';
      this.message = null;

      try {
        const res = await window.api(`/stores/${store.id}/sync`, { method: 'POST' });
        store.sync_status = 'idle';
        store.last_synced_at = new Date().toLocaleString();
        this.message = { type: 'success', text: res.message };
        await this.fetchStores(1, true);
      } catch (e) {
        store.sync_status = 'error';
        this.message = { type: 'error', text: e.data?.message || 'Sync failed. Check your channel credentials.' };
      } finally {
        store.syncing = false;
      }
    },

    syncBadge(status) {
      return {
        idle:    'bg-gray-100 text-gray-600',
        syncing: 'bg-yellow-100 text-yellow-700',
        error:   'bg-red-100 text-red-700',
      }[status] ?? 'bg-gray-100 text-gray-600';
    },
  },
};
</script>

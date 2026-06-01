<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-700">{{ $t.campaigns || 'Ad Campaigns' }}</h3>
      <a href="/campaigns/create" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700">{{ $t.add_campaign || '+ New Campaign' }}</a>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-400">{{ $t.loading || 'Loading...' }}</div>

    <div v-else-if="campaigns.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
      {{ $t.no_campaigns || 'No campaigns yet.' }}
    </div>

    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.campaign || 'Campaign' }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.channel || 'Channel' }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.budget || 'Budget' }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.status || 'Status' }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t.ai_content || 'AI Content' }}</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="c in campaigns" :key="c.id">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ c.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ c.channel_integration?.channel_type?.replace('_', ' ') }}</td>
            <td class="px-6 py-4 text-sm text-gray-700">{{ c.budget ? `€${c.budget}` : '—' }}</td>
            <td class="px-6 py-4">
              <span :class="statusBadge(c.status)" class="px-2 py-1 rounded text-xs font-medium">{{ c.status }}</span>
            </td>
            <td class="px-6 py-4 text-sm">
              <span v-if="c.ai_content?.length" class="text-green-600">{{ ($t.ads_generated || ':count ads generated').replace(':count', c.ai_content.length) }}</span>
              <span v-else class="text-gray-400">{{ $t.not_generated || 'Not generated' }}</span>
            </td>
            <td class="px-6 py-4 text-right">
              <a :href="`/campaigns/${c.id}`" class="text-sm text-indigo-600 hover:underline">{{ $t.view || 'View' }}</a>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="loadingMore" class="text-center py-4 text-gray-400 text-sm">{{ $t.loading || 'Loading...' }}</div>
      <div v-else-if="!hasMore" class="text-center py-3 text-gray-400 text-xs border-t">
        {{ ($t.all_loaded || 'All :total campaigns loaded').replace(':total', total) }}
      </div>
    </div>

    <div ref="sentinel" class="h-1"></div>
  </div>
</template>

<script>
export default {
  name: 'CampaignsApp',

  data() {
    return {
      campaigns: [],
      loading: true,
      loadingMore: false,
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
    await this.fetchCampaigns(1, true);
  },

  mounted() {
    this.$nextTick(() => {
      this.observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting && this.hasMore && !this.loadingMore) {
          this.fetchCampaigns(this.currentPage + 1, false);
        }
      }, { rootMargin: '200px' });
      if (this.$refs.sentinel) this.observer.observe(this.$refs.sentinel);
    });
  },

  beforeUnmount() {
    this.observer?.disconnect();
  },

  methods: {
    async fetchCampaigns(page = 1, replace = false) {
      if (replace) {
        this.loading = true;
        this.campaigns = [];
      } else {
        this.loadingMore = true;
      }

      try {
        const data = await window.api(`/api/campaigns?page=${page}`);
        this.campaigns = replace ? data.data : [...this.campaigns, ...data.data];
        this.currentPage = data.current_page;
        this.lastPage = data.last_page;
        this.total = data.total;
      } finally {
        this.loading = false;
        this.loadingMore = false;
      }
    },

    statusBadge(status) {
      return {
        draft:  'bg-gray-100 text-gray-600',
        active: 'bg-green-100 text-green-700',
        paused: 'bg-yellow-100 text-yellow-700',
        ended:  'bg-gray-100 text-gray-400',
        error:  'bg-red-100 text-red-700',
      }[status] ?? 'bg-gray-100 text-gray-600';
    },
  },
};
</script>

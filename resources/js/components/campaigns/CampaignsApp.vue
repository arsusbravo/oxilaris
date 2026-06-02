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

    <!-- Mobile: Card layout -->
    <div v-if="!loading && campaigns.length > 0" class="sm:hidden space-y-3 px-2">
      <div v-for="c in campaigns" :key="c.id" class="bg-white rounded-lg shadow p-4 space-y-3">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h4 class="font-medium text-gray-900">{{ c.name }}</h4>
            <p class="text-xs text-gray-400 capitalize mt-1">{{ c.channel_integration?.channel_type?.replace('_', ' ') }}</p>
          </div>
          <span :class="statusBadge(c.status)" class="px-2 py-1 rounded text-xs font-medium shrink-0">{{ c.status }}</span>
        </div>
        <div class="grid grid-cols-2 gap-3 text-xs border-t border-gray-100 pt-3">
          <div>
            <p class="text-gray-400">{{ $t.budget || 'Budget' }}</p>
            <p class="font-medium text-gray-900">{{ c.budget ? `€${c.budget}` : '—' }}</p>
          </div>
          <div>
            <p class="text-gray-400">{{ $t.ai_content || 'AI Content' }}</p>
            <p class="font-medium" :class="c.ai_content?.length ? 'text-green-600' : 'text-gray-400'">
              {{ c.ai_content?.length ? c.ai_content.length + ' ads' : 'Not generated' }}
            </p>
          </div>
        </div>
        <div class="border-t border-gray-100 pt-3">
          <a :href="`/campaigns/${c.id}`" class="w-full text-center text-xs text-indigo-600 hover:text-indigo-700 font-medium py-1.5 rounded hover:bg-indigo-50">{{ $t.view || 'View' }}</a>
        </div>
      </div>
      <div v-if="loadingMore" class="text-center py-4 text-gray-400 text-sm">{{ $t.loading || 'Loading...' }}</div>
      <div v-else-if="!hasMore" class="text-center py-3 text-gray-400 text-xs">
        {{ ($t.all_loaded || 'All :total campaigns loaded').replace(':total', total) }}
      </div>
    </div>

    <!-- Desktop: Table layout -->
    <div v-if="!loading && campaigns.length > 0" class="bg-white rounded-lg shadow overflow-hidden hidden sm:block">
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

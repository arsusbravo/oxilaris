<template>
  <div class="space-y-6">

    <!-- Quick Connect — OAuth platforms with one click -->
    <div v-if="quickConnectPlatforms.length" class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
      <h3 class="font-semibold text-slate-700 text-sm mb-1">{{ $t.ch_quick_connect || 'Quick Connect' }}</h3>
      <p class="text-xs text-slate-400 mb-4">{{ $t.ch_quick_connect_sub || 'Connect in one click — no API keys needed' }}</p>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a v-for="p in quickConnectPlatforms" :key="p.type"
           :href="`/channels/create?type=${p.type}`"
           class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 border-transparent hover:border-indigo-200 hover:bg-indigo-50 transition-colors group">
          <span class="text-2xl">{{ p.emoji }}</span>
          <span class="text-xs font-semibold text-slate-600 group-hover:text-indigo-700 text-center leading-tight">{{ p.label }}</span>
          <span class="text-[10px] text-slateald-400 group-hover:text-indigo-500 font-medium">{{ $t.ch_connect_btn || 'Connect →' }}</span>
        </a>
      </div>
    </div>

    <!-- Header row -->
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-700">{{ $t.channels || 'Connected Channels' }}</h3>
      <a href="/channels/create" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">{{ $t.add_channel || '+ Add Channel' }}</a>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-400">{{ $t.loading || 'Loading...' }}</div>

    <div v-else-if="channels.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
      {{ $t.no_channels || 'No channels connected yet. Add a store, marketplace or advertising channel to get started.' }}
    </div>

    <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="ch in channels" :key="ch.id" class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
        <div class="flex items-start justify-between">
          <div class="min-w-0">
            <div class="font-medium text-gray-800 truncate">{{ ch.name }}</div>
            <div class="text-sm text-gray-400 mt-0.5 capitalize">{{ ch.channel_type.replace(/_/g, ' ') }}</div>
          </div>
          <span :class="statusBadge(ch.status)" class="px-2 py-0.5 rounded text-xs font-medium shrink-0 ml-2">{{ ch.status }}</span>
        </div>

        <div class="mt-4 flex gap-2">
          <a :href="`/channels/${ch.id}/connect`"
            class="flex-1 text-center text-sm bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-3 py-1.5 rounded-lg font-medium transition-colors">
            {{ ch.status === 'active' ? ($t.ch_reconnect || 'Re-authorize') : ($t.connect || 'Connect') }}
          </a>
          <a :href="`/channels/${ch.id}/edit`"
            class="flex-1 text-center text-sm bg-gray-50 text-gray-600 hover:bg-gray-100 px-3 py-1.5 rounded-lg font-medium transition-colors">
            {{ $t.edit || 'Settings' }}
          </a>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
const PLATFORM_META = {
  shopify:     { emoji: '🛍️', label: 'Shopify' },
  woocommerce: { emoji: '🔌', label: 'WooCommerce' },
  tiktok_shop: { emoji: '🎵', label: 'TikTok Shop' },
  shopee:      { emoji: '🟠', label: 'Shopee' },
};

export default {
  name: 'ChannelsApp',

  computed: {
    $t() { return window.trans || {}; },
    quickConnectPlatforms() {
      const el      = document.getElementById('channels-app');
      const enabled = JSON.parse(el?.dataset.oauthPlatforms || '[]');
      return enabled.map(type => ({ type, ...PLATFORM_META[type] })).filter(p => p.label);
    },
  },

  data() {
    return {
      channels: [],
      loading: true,
    };
  },

  async created() {
    try {
      const data = await window.api('/api/channels');
      this.channels = data;
    } finally {
      this.loading = false;
    }
  },

  methods: {
    statusBadge(status) {
      return {
        active:   'bg-emerald-100 text-emerald-700',
        inactive: 'bg-gray-100 text-gray-500',
        error:    'bg-red-100 text-red-600',
      }[status] ?? 'bg-gray-100 text-gray-500';
    },
  },
};
</script>

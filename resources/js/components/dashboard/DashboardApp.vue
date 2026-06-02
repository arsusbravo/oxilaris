<template>
  <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

    <!-- Stats grid -->
    <div v-if="!allStatsZero" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <div v-for="stat in stats" :key="stat.label"
           class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 sm:p-5 flex items-start justify-between hover:shadow-md transition-shadow"
           :class="`border-l-4 ${stat.borderColor}`">
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wider" :class="stat.labelColor">{{ stat.label }}</p>
          <p class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1 leading-none">{{ stat.value }}</p>
          <a :href="stat.href" class="inline-flex items-center gap-1 text-xs font-medium mt-2 transition-colors" :class="stat.linkColor">
            {{ $t.view_all || 'Lihat' }}
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>
        <div class="rounded-xl p-2 sm:p-2.5 ml-2 shrink-0" :class="stat.iconBg">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" :class="stat.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" />
          </svg>
        </div>
      </div>
    </div>

    <!-- CTA banner -->
    <a href="/products/create"
       class="flex items-center gap-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-2xl p-5 shadow-md transition-all active:scale-[0.99] group">
      <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-white font-bold text-base leading-snug">{{ $t.qa_add_product || 'Tambah Produk' }}</p>
        <p class="text-indigo-200 text-sm mt-0.5 leading-snug">{{ $t.qa_add_product_sub || 'Upload foto, isi detail, lalu push ke Tokopedia, Shopee & lainnya.' }}</p>
      </div>
      <svg class="w-5 h-5 text-white/50 group-hover:text-white/80 shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </a>

    <!-- How it works -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-slate-800 text-sm">{{ $t.how_it_works || 'Cara kerjanya' }}</h3>
        <p class="text-xs text-slate-400 mt-0.5">{{ $t.how_it_works_sub || 'Dari produk ke pembeli dalam 4 langkah' }}</p>
      </div>

      <!-- Mobile: vertical list -->
      <div class="sm:hidden divide-y divide-slate-100">

        <a href="/products/create" class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 active:bg-slate-100 transition-colors">
          <div class="w-9 h-9 rounded-full bg-sky-100 text-sky-600 font-bold text-sm flex items-center justify-center shrink-0">1</div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-slate-800">{{ $t.flow_add_product || 'Tambah Produk' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $t.flow_add_product_sub || 'Isi info & upload foto' }}</p>
          </div>
          <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>

        <a href="/channels" class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 active:bg-slate-100 transition-colors">
          <div class="w-9 h-9 rounded-full bg-violet-100 text-violet-600 font-bold text-sm flex items-center justify-center shrink-0">2</div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-slate-800">{{ $t.flow_connect_channel || 'Hubungkan Channel' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $t.flow_connect_channel_sub || 'Tokopedia, Shopee, dll.' }}</p>
          </div>
          <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>

        <a href="/listings" class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 active:bg-slate-100 transition-colors">
          <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 font-bold text-sm flex items-center justify-center shrink-0">3</div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-slate-800">{{ $t.flow_push_listings || 'Push ke Listings' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $t.flow_push_listings_sub || 'Pilih produk & channel' }}</p>
          </div>
          <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>

        <div class="flex items-center gap-4 px-5 py-4 bg-emerald-50">
          <div class="w-9 h-9 rounded-full bg-emerald-500 text-white font-bold text-sm flex items-center justify-center shrink-0">✓</div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-emerald-700">{{ $t.flow_live || 'Produk Tayang!' }}</p>
            <p class="text-xs text-emerald-500 mt-0.5">{{ $t.flow_live_sub || 'Terlihat oleh pembeli' }}</p>
          </div>
        </div>

      </div>

      <!-- Desktop: horizontal cards -->
      <div class="hidden sm:flex items-center gap-2 p-5">

        <a href="/products/create" class="flex-1 rounded-xl bg-sky-50 border-2 border-sky-200 p-4 text-center hover:border-sky-400 transition-colors">
          <div class="w-8 h-8 rounded-full bg-sky-500 text-white text-sm font-bold flex items-center justify-center mx-auto mb-2">1</div>
          <svg class="w-5 h-5 text-sky-500 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
          <p class="text-xs font-bold text-sky-700">{{ $t.flow_add_product || 'Tambah Produk' }}</p>
          <p class="text-[10px] text-sky-400 mt-0.5">{{ $t.flow_add_product_sub || 'Isi info & upload foto' }}</p>
        </a>

        <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>

        <a href="/channels" class="flex-1 rounded-xl bg-violet-50 border-2 border-violet-200 p-4 text-center hover:border-violet-400 transition-colors">
          <div class="w-8 h-8 rounded-full bg-violet-500 text-white text-sm font-bold flex items-center justify-center mx-auto mb-2">2</div>
          <svg class="w-5 h-5 text-violet-500 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
          <p class="text-xs font-bold text-violet-700">{{ $t.flow_connect_channel || 'Hubungkan Channel' }}</p>
          <p class="text-[10px] text-violet-400 mt-0.5">{{ $t.flow_connect_channel_sub || 'Tokopedia, Shopee, dll.' }}</p>
        </a>

        <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>

        <a href="/listings" class="flex-1 rounded-xl bg-indigo-50 border-2 border-indigo-200 p-4 text-center hover:border-indigo-400 transition-colors">
          <div class="w-8 h-8 rounded-full bg-indigo-500 text-white text-sm font-bold flex items-center justify-center mx-auto mb-2">3</div>
          <svg class="w-5 h-5 text-indigo-500 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
          </svg>
          <p class="text-xs font-bold text-indigo-700">{{ $t.flow_push_listings || 'Push ke Listings' }}</p>
          <p class="text-[10px] text-indigo-400 mt-0.5">{{ $t.flow_push_listings_sub || 'Pilih produk & channel' }}</p>
        </a>

        <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>

        <div class="flex-1 rounded-xl bg-emerald-500 border-2 border-emerald-500 p-4 text-center">
          <div class="w-8 h-8 rounded-full bg-white/20 text-white text-sm font-bold flex items-center justify-center mx-auto mb-2">✓</div>
          <svg class="w-5 h-5 text-emerald-100 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="text-xs font-bold text-white">{{ $t.flow_live || 'Produk Tayang!' }}</p>
          <p class="text-[10px] text-emerald-100 mt-0.5">{{ $t.flow_live_sub || 'Terlihat oleh pembeli' }}</p>
        </div>

      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'DashboardApp',

  data() {
    return {
      stats: [
        {
          label: 'Stores', value: '—', href: '/stores',
          borderColor: 'border-indigo-500', labelColor: 'text-indigo-500',
          linkColor: 'text-indigo-600 hover:text-indigo-800',
          iconBg: 'bg-indigo-50', iconColor: 'text-indigo-500',
          icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        },
        {
          label: 'Products', value: '—', href: '/products',
          borderColor: 'border-emerald-500', labelColor: 'text-emerald-600',
          linkColor: 'text-emerald-600 hover:text-emerald-800',
          iconBg: 'bg-emerald-50', iconColor: 'text-emerald-500',
          icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        },
        {
          label: 'Active Listings', value: '—', href: '/listings',
          borderColor: 'border-violet-500', labelColor: 'text-violet-600',
          linkColor: 'text-violet-600 hover:text-violet-800',
          iconBg: 'bg-violet-50', iconColor: 'text-violet-500',
          icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        },
        {
          label: 'Active Campaigns', value: '—', href: '/campaigns',
          borderColor: 'border-amber-500', labelColor: 'text-amber-600',
          linkColor: 'text-amber-600 hover:text-amber-800',
          iconBg: 'bg-amber-50', iconColor: 'text-amber-500',
          icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        },
      ],

      marketplaces: JSON.parse(document.getElementById('dashboard-app')?.dataset.marketplaces || '[]'),
      adChannels:   JSON.parse(document.getElementById('dashboard-app')?.dataset.adChannels   || '[]'),
    };
  },

  computed: {
    $t() { return window.trans || {}; },
    allStatsZero() {
      return this.stats.every(stat => Number(stat.value) === 0);
    },
  },

  async created() {
    const t = window.trans || {};
    this.stats[0].label = t.stat_stores    || 'Stores';
    this.stats[1].label = t.stat_products  || 'Products';
    this.stats[2].label = t.stat_listings  || 'Active Listings';
    this.stats[3].label = t.stat_campaigns || 'Active Campaigns';
    try {
      const data = await window.api('/api/dashboard/stats');
      this.stats[0].value = data.stores;
      this.stats[1].value = data.products;
      this.stats[2].value = data.listings;
      this.stats[3].value = data.campaigns;
    } catch {
      // silently degrade
    }
  },
};
</script>

<template>
  <div class="p-6 space-y-6">

    <!-- Stats grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.label"
           class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex items-start justify-between hover:shadow-md transition-shadow"
           :class="`border-l-4 ${stat.borderColor}`">
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wider" :class="stat.labelColor">{{ stat.label }}</p>
          <p class="text-3xl font-bold text-slate-800 mt-1 leading-none">{{ stat.value }}</p>
          <a :href="stat.href" class="inline-flex items-center gap-1 text-xs font-medium mt-2 transition-colors" :class="stat.linkColor">
            View all
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>
        <div class="rounded-xl p-2.5 ml-3 shrink-0" :class="stat.iconBg">
          <svg class="w-5 h-5" :class="stat.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Quick actions + flowchart -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <!-- Quick Actions -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
          <h3 class="font-semibold text-slate-800 text-sm">Quick Actions</h3>
          <p class="text-xs text-slate-400 mt-0.5">Jump right in</p>
        </div>
        <div class="p-3 space-y-2">
          <a v-for="action in quickActions" :key="action.href" :href="action.href"
             class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors group"
             :class="action.bg">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :class="action.iconBg">
              <svg class="w-4 h-4" :class="action.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon" />
              </svg>
            </div>
            <span class="text-sm font-medium flex-1" :class="action.text">{{ action.label }}</span>
            <svg class="w-4 h-4 opacity-40 group-hover:opacity-80 transition-opacity" :class="action.iconColor"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>
      </div>

      <!-- Workflow Flowchart (spans 2 columns) -->
      <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
          <h3 class="font-semibold text-slate-800 text-sm">How it works</h3>
          <p class="text-xs text-slate-400 mt-0.5">Your complete product distribution workflow</p>
        </div>
        <div class="p-5 space-y-3">

          <!-- Step 1: Create Channels (full width — must happen first) -->
          <div class="rounded-lg bg-rose-50 border-2 border-rose-200 p-3">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-6 h-6 rounded-full bg-rose-500 text-white text-xs font-bold flex items-center justify-center shrink-0">1</div>
              <div>
                <p class="text-xs font-bold text-rose-700">Create your channels</p>
                <p class="text-[10px] text-rose-400">Go to <strong>Channels → Add Channel</strong> and add credentials for each platform you want to use</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div class="bg-white rounded-md p-2 border border-rose-100">
                <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Store channels</p>
                <div class="flex flex-wrap gap-1">
                  <span class="text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-medium">WooCommerce</span>
                  <span class="text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-medium">Shopify</span>
                  <span class="text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-medium">Magento</span>
                  <span class="text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-medium">CS-Cart</span>
                </div>
              </div>
              <div class="bg-white rounded-md p-2 border border-rose-100">
                <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Marketplace &amp; Ad channels</p>
                <div class="flex flex-wrap gap-1">
                  <span v-for="m in [...marketplaces, ...adChannels]" :key="m"
                    class="text-[10px] bg-violet-100 text-violet-600 px-1.5 py-0.5 rounded font-medium">{{ m }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Down arrow -->
          <div class="flex justify-center"><div class="w-px h-3 bg-slate-300"></div></div>

          <!-- Steps 2–3: Connect store + import -->
          <div class="flex items-center gap-1.5">
            <div class="flex-1 rounded-lg bg-indigo-50 border border-indigo-100 p-3 text-center">
              <div class="w-6 h-6 rounded-full bg-indigo-500 text-white text-xs font-bold flex items-center justify-center mx-auto mb-1">2</div>
              <p class="text-xs font-semibold text-indigo-700">Connect a Store</p>
              <p class="text-[10px] text-indigo-400 mt-0.5">Add store → test connection</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <div class="flex-1 rounded-lg bg-emerald-50 border border-emerald-100 p-3 text-center">
              <div class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center mx-auto mb-1">3</div>
              <p class="text-xs font-semibold text-emerald-700">Import Products</p>
              <p class="text-[10px] text-emerald-400 mt-0.5">Sync or add manually</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <div class="flex-1 rounded-lg bg-sky-50 border border-sky-100 p-3 text-center">
              <div class="w-6 h-6 rounded-full bg-sky-500 text-white text-xs font-bold flex items-center justify-center mx-auto mb-1">4</div>
              <p class="text-xs font-semibold text-sky-700">Product Catalog</p>
              <p class="text-[10px] text-sky-400 mt-0.5">Ready to distribute</p>
            </div>
          </div>

          <!-- Down + fork -->
          <div class="flex justify-center"><div class="w-px h-3 bg-slate-300"></div></div>
          <div class="relative h-3">
            <div class="absolute left-[25%] right-[25%] top-0 h-px bg-slate-300"></div>
            <div class="absolute left-[25%] top-0 w-px h-full bg-slate-300"></div>
            <div class="absolute right-[25%] top-0 w-px h-full bg-slate-300"></div>
          </div>

          <!-- Phase 2: Two paths -->
          <div :class="marketplaces.length && adChannels.length ? 'grid-cols-2' : 'grid-cols-1 max-w-sm mx-auto'" class="grid gap-3">

            <!-- Path A: Marketplace -->
            <div v-if="marketplaces.length" class="space-y-2">
              <div class="rounded-lg bg-violet-50 border border-violet-100 p-3">
                <div class="flex items-center gap-2 mb-1.5">
                  <div class="w-5 h-5 rounded-full bg-violet-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">5</div>
                  <p class="text-xs font-semibold text-violet-700">Push to Marketplace</p>
                </div>
                <div class="flex flex-wrap gap-1">
                  <span v-for="m in marketplaces" :key="m" class="text-[10px] bg-violet-100 text-violet-600 px-1.5 py-0.5 rounded font-medium">{{ m }}</span>
                </div>
              </div>
              <div class="flex justify-center"><div class="w-px h-3 bg-slate-200"></div></div>
              <div class="rounded-lg bg-violet-600 p-3 text-center">
                <svg class="w-4 h-4 text-violet-200 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs font-bold text-white">Live Listings</p>
                <p class="text-[10px] text-violet-200 mt-0.5">Products visible to buyers</p>
              </div>
            </div>

            <!-- Path B: Advertising -->
            <div v-if="adChannels.length" class="space-y-2">
              <div class="rounded-lg bg-amber-50 border border-amber-100 p-3">
                <div class="flex items-center gap-2 mb-1.5">
                  <div class="w-5 h-5 rounded-full bg-amber-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">6</div>
                  <p class="text-xs font-semibold text-amber-700">AI Ad Campaign</p>
                </div>
                <div class="flex flex-wrap gap-1">
                  <span v-for="a in adChannels" :key="a" class="text-[10px] bg-amber-100 text-amber-600 px-1.5 py-0.5 rounded font-medium">{{ a }}</span>
                </div>
              </div>
              <div class="flex justify-center"><div class="w-px h-3 bg-slate-200"></div></div>
              <div class="rounded-lg bg-amber-500 p-3 text-center">
                <svg class="w-4 h-4 text-amber-100 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <p class="text-xs font-bold text-white">Running Ads</p>
                <p class="text-[10px] text-amber-100 mt-0.5">AI-generated copy live</p>
              </div>
            </div>

          </div>
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

      quickActions: [
        {
          label: 'Connect a channel', href: '/channels/create',
          bg: 'bg-indigo-50 hover:bg-indigo-100', iconBg: 'bg-indigo-600',
          iconColor: 'text-white', text: 'text-indigo-800',
          icon: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
        },
        {
          label: 'Add a store & import', href: '/stores',
          bg: 'bg-emerald-50 hover:bg-emerald-100', iconBg: 'bg-emerald-600',
          iconColor: 'text-white', text: 'text-emerald-800',
          icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        },
        {
          label: 'Push products to market', href: '/listings',
          bg: 'bg-violet-50 hover:bg-violet-100', iconBg: 'bg-violet-600',
          iconColor: 'text-white', text: 'text-violet-800',
          icon: 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
        },
        {
          label: 'Create an AI ad campaign', href: '/campaigns/create',
          bg: 'bg-amber-50 hover:bg-amber-100', iconBg: 'bg-amber-500',
          iconColor: 'text-white', text: 'text-amber-800',
          icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        },
      ],

      marketplaces: JSON.parse(document.getElementById('dashboard-app')?.dataset.marketplaces || '[]'),
      adChannels:   JSON.parse(document.getElementById('dashboard-app')?.dataset.adChannels   || '[]'),
    };
  },

  async created() {
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

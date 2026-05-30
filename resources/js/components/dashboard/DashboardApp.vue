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

    <!-- Quick actions + getting started -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

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

      <!-- Getting Started -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
          <h3 class="font-semibold text-slate-800 text-sm">Getting Started</h3>
          <p class="text-xs text-slate-400 mt-0.5">Follow these steps to go live</p>
        </div>
        <div class="p-5 space-y-4">
          <div v-for="(step, i) in steps" :key="i" class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold text-white"
                 :class="step.color">
              {{ i + 1 }}
            </div>
            <div>
              <p class="text-sm font-medium text-slate-700">{{ step.title }}</p>
              <p class="text-xs text-slate-400 mt-0.5">{{ step.desc }}</p>
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
          label: 'Stores',
          value: '—',
          href: '/stores',
          borderColor: 'border-indigo-500',
          labelColor: 'text-indigo-500',
          linkColor: 'text-indigo-600 hover:text-indigo-800',
          iconBg: 'bg-indigo-50',
          iconColor: 'text-indigo-500',
          icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        },
        {
          label: 'Products',
          value: '—',
          href: '/products',
          borderColor: 'border-emerald-500',
          labelColor: 'text-emerald-600',
          linkColor: 'text-emerald-600 hover:text-emerald-800',
          iconBg: 'bg-emerald-50',
          iconColor: 'text-emerald-500',
          icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        },
        {
          label: 'Active Listings',
          value: '—',
          href: '/listings',
          borderColor: 'border-violet-500',
          labelColor: 'text-violet-600',
          linkColor: 'text-violet-600 hover:text-violet-800',
          iconBg: 'bg-violet-50',
          iconColor: 'text-violet-500',
          icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        },
        {
          label: 'Active Campaigns',
          value: '—',
          href: '/campaigns',
          borderColor: 'border-amber-500',
          labelColor: 'text-amber-600',
          linkColor: 'text-amber-600 hover:text-amber-800',
          iconBg: 'bg-amber-50',
          iconColor: 'text-amber-500',
          icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        },
      ],

      quickActions: [
        {
          label: 'Connect a channel',
          href: '/channels/create',
          bg: 'bg-indigo-50 hover:bg-indigo-100',
          iconBg: 'bg-indigo-600',
          iconColor: 'text-white',
          text: 'text-indigo-800',
          icon: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
        },
        {
          label: 'Add a store & import products',
          href: '/stores',
          bg: 'bg-emerald-50 hover:bg-emerald-100',
          iconBg: 'bg-emerald-600',
          iconColor: 'text-white',
          text: 'text-emerald-800',
          icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        },
        {
          label: 'Create an ad campaign',
          href: '/campaigns/create',
          bg: 'bg-violet-50 hover:bg-violet-100',
          iconBg: 'bg-violet-600',
          iconColor: 'text-white',
          text: 'text-violet-800',
          icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        },
      ],

      steps: [
        { title: 'Connect your store',          desc: 'WooCommerce, Shopify, Magento or CS-Cart',       color: 'bg-indigo-500' },
        { title: 'Sync your products',          desc: 'Import your product catalog automatically',       color: 'bg-emerald-500' },
        { title: 'Connect a sales channel',     desc: 'BOL.com, Amazon, Google Ads or Facebook Ads',    color: 'bg-sky-500' },
        { title: 'Push listings to marketplace',desc: 'Select products and push to any marketplace',    color: 'bg-violet-500' },
        { title: 'Launch an AI ad campaign',    desc: 'Generate ad copy with AI and push to your ads',  color: 'bg-amber-500' },
      ],
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

<template>
  <div>
    <div v-if="message" :class="message.type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'"
      class="border rounded p-3 text-sm mb-4 flex items-center justify-between">
      <span>{{ message.text }}</span>
      <button @click="message = null" class="ml-4 opacity-60 hover:opacity-100">✕</button>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">

      <!-- Left: product selector -->
      <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b flex items-center justify-between">
          <h3 class="font-semibold text-gray-700">Select Products</h3>
          <span class="text-sm text-gray-400">{{ selectedProducts.length }} selected</span>
        </div>
        <div class="px-4 py-2 border-b flex gap-2">
          <input v-model="search" @input="debouncedSearch" type="text" placeholder="Search products…"
            class="flex-1 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />

          <!-- Store filter dropdown -->
          <div class="relative" ref="storeDropdownRef">
            <button @click="storeDropdownOpen = !storeDropdownOpen" type="button"
              class="flex items-center gap-1.5 border rounded px-3 py-1.5 text-sm whitespace-nowrap transition-colors"
              :class="selectedStoreIds.length ? 'border-indigo-400 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-600 hover:border-gray-400'">
              {{ selectedStoreIds.length ? `${selectedStoreIds.length} store${selectedStoreIds.length > 1 ? 's' : ''}` : 'All stores' }}
              <svg class="w-3 h-3 shrink-0 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-show="storeDropdownOpen"
              class="absolute right-0 top-full mt-1 w-52 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1">
              <div v-if="stores.length === 0" class="px-3 py-2 text-sm text-gray-400">No stores available</div>
              <label v-for="store in stores" :key="store.id" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" :value="store.id" v-model="selectedStoreIds" @change="fetchProducts(1, true)" class="rounded text-indigo-600" />
                <span class="text-sm text-gray-700 truncate">{{ store.name }}</span>
              </label>
              <div v-if="selectedStoreIds.length" class="border-t mt-1 pt-1">
                <button @click="clearStoreFilter" type="button" class="w-full text-left px-3 py-1.5 text-xs text-red-500 hover:bg-red-50">Clear filter</button>
              </div>
            </div>
          </div>
        </div>
        <div v-if="loadingProducts && products.length === 0" class="text-center py-8 text-gray-400 text-sm">Loading…</div>
        <div v-else-if="products.length === 0" class="text-center py-8 text-gray-400 text-sm">No products found.</div>
        <div v-else>
          <div class="px-4 py-2 border-b bg-gray-50 flex items-center gap-2 text-sm text-gray-500">
            <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded" />
            <span>Select all on this page ({{ products.length }})</span>
          </div>
          <div class="max-h-96 overflow-y-auto divide-y divide-gray-100" ref="scrollContainer" @scroll="onScroll">
            <label v-for="product in products" :key="product.id" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer">
              <input type="checkbox" :value="product.id" v-model="selectedProducts" class="rounded" />
              <img v-if="product.images?.[0]" :src="product.images[0]" class="w-8 h-8 object-cover rounded" />
              <div v-else class="w-8 h-8 bg-gray-100 rounded"></div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 truncate">{{ product.title }}</div>
                <div class="text-xs text-gray-400">€{{ product.price }} · {{ product.store?.name || '—' }}</div>
              </div>
            </label>
            <div v-if="loadingMore" class="text-center py-3 text-gray-400 text-xs">Loading more…</div>
            <div v-else-if="!hasMore && products.length > 0" class="text-center py-3 text-gray-400 text-xs">All {{ products.length }} products loaded</div>
          </div>
        </div>
      </div>

      <!-- Right: channels + action -->
      <div class="space-y-4">
        <div class="bg-white rounded-lg shadow p-4">
          <h3 class="font-semibold text-gray-700 mb-3">Push to Channels</h3>

          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Marketplaces</p>
          <div v-if="marketplaceChannels.length === 0" class="text-sm text-gray-400 mb-3">No active marketplace channels.</div>
          <div v-else class="space-y-1.5 mb-3">
            <label v-for="channel in marketplaceChannels" :key="channel.id" class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" :value="channel.id" v-model="selectedChannels" class="rounded" />
              <span class="text-sm text-gray-700">{{ channel.name }}</span>
              <span class="text-xs text-gray-400 capitalize">({{ channel.channel_type }})</span>
            </label>
          </div>

          <div class="border-t pt-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Stores</p>
            <div v-if="storeChannels.length === 0" class="text-sm text-gray-400">No active store channels.</div>
            <div v-else class="space-y-1.5">
              <label v-for="channel in storeChannels" :key="channel.id" class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" :value="channel.id" v-model="selectedChannels" class="rounded" />
                <span class="text-sm text-gray-700">{{ channel.name }}</span>
                <span class="text-xs text-gray-400 capitalize">({{ channel.channel_type.replace('_', ' ') }})</span>
              </label>
            </div>
          </div>
        </div>

        <button @click="pushToMarketplaces" :disabled="!canPush || pushing"
          class="w-full bg-indigo-600 text-white text-sm px-4 py-2.5 rounded hover:bg-indigo-700 disabled:opacity-40 font-medium">
          {{ pushing ? 'Creating listings…' : `Push ${selectedProducts.length} product(s) to ${selectedChannels.length} channel(s)` }}
        </button>
        <p class="text-xs text-gray-400 text-center">
          Creates {{ selectedProducts.length * selectedChannels.length }} listing(s). Existing listings will be skipped.
        </p>
      </div>
    </div>

    <!-- Existing listings -->
    <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">

      <!-- Listings header + filters -->
      <div class="px-4 py-3 border-b space-y-2">
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <h3 class="font-semibold text-gray-700">
            Existing Listings
            <span class="text-gray-400 font-normal text-sm ml-1">({{ listingsTotal }})</span>
          </h3>
          <div class="flex items-center gap-2 flex-wrap">
            <!-- Channel/store filter -->
            <select v-model="listingsChannelFilter" @change="fetchListings(1, true)"
              class="border border-gray-300 rounded px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
              <option value="">All channels</option>
              <optgroup v-if="marketplaceChannels.length" label="Marketplaces">
                <option v-for="c in marketplaceChannels" :key="c.id" :value="c.id">{{ c.name }}</option>
              </optgroup>
              <optgroup v-if="storeChannels.length" label="Stores">
                <option v-for="c in storeChannels" :key="c.id" :value="c.id">{{ c.name }}</option>
              </optgroup>
            </select>

            <!-- Status filter -->
            <select v-model="listingsStatusFilter" @change="fetchListings(1, true)"
              class="border border-gray-300 rounded px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
              <option value="">All statuses</option>
              <option value="pending">Pending</option>
              <option value="active">Active</option>
              <option value="error">Error</option>
              <option value="delisted">Delisted</option>
            </select>

            <button @click="fetchListings(1, true)" class="text-xs text-indigo-600 hover:underline px-1">Refresh</button>
          </div>
        </div>

        <!-- Bulk action bar — shown when listings are selected -->
        <div v-if="selectedListings.length" class="flex items-center gap-3 pt-1">
          <span class="text-sm text-gray-600 font-medium">{{ selectedListings.length }} selected</span>
          <button @click="bulkPush" :disabled="bulkActing"
            class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded font-medium disabled:opacity-50">
            {{ bulkActing ? '…' : 'Re-push selected' }}
          </button>
          <button @click="bulkDelete" :disabled="bulkActing"
            class="text-xs bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded font-medium disabled:opacity-50">
            Delete selected
          </button>
          <button @click="selectedListings = []" class="text-xs text-gray-400 hover:text-gray-600">Clear</button>
        </div>
      </div>

      <div v-if="loadingListings" class="text-center py-8 text-gray-400 text-sm">Loading…</div>
      <div v-else-if="listings.length === 0" class="text-center py-8 text-gray-400 text-sm">No listings found.</div>
      <template v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 w-8">
                <input type="checkbox" :checked="allListingsSelected" @change="toggleAllListings" class="rounded" />
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Channel</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Pushed</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="listing in listings" :key="listing.id" :class="selectedListings.includes(listing.id) ? 'bg-indigo-50' : ''">
              <td class="px-4 py-3">
                <input type="checkbox" :value="listing.id" v-model="selectedListings" class="rounded" />
              </td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ listing.product?.title }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ listing.channel_integration?.name }}</td>
              <td class="px-4 py-3">
                <span :class="statusBadge(listing.status)" class="px-2 py-1 rounded text-xs font-medium">{{ listing.status }}</span>
                <div v-if="listing.status === 'error' && listing.error_message" class="text-xs text-red-500 mt-0.5 max-w-xs truncate" :title="listing.error_message">
                  {{ listing.error_message }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-400">{{ listing.last_pushed_at || 'Never' }}</td>
              <td class="px-4 py-3 text-right space-x-2">
                <button @click="push(listing)" class="text-xs text-green-600 hover:text-green-800 font-medium">Re-push</button>
                <button @click="remove(listing)" class="text-xs text-red-500 hover:text-red-700">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="listingsLoadingMore" class="text-center py-4 text-gray-400 text-sm">Loading more…</div>
        <div v-else-if="!listingsHasMore" class="text-center py-3 text-gray-400 text-xs border-t">
          All {{ listingsTotal }} listings loaded
        </div>
      </template>
    </div>

    <div ref="listingsSentinel" class="h-1"></div>
  </div>
</template>

<script>
export default {
  name: 'ListingsApp',

  data() {
    return {
      products: [],
      listings: [],
      stores: [],
      marketplaceChannels: [],
      storeChannels: [],
      selectedProducts: [],
      selectedChannels: [],
      selectedStoreIds: [],
      selectedListings: [],
      storeDropdownOpen: false,
      loadingProducts: true,
      loadingMore: false,
      loadingListings: true,
      listingsLoadingMore: false,
      pushing: false,
      bulkActing: false,
      message: null,
      search: '',
      searchTimer: null,
      currentPage: 1,
      lastPage: 1,
      listingsPage: 1,
      listingsLastPage: 1,
      listingsTotal: 0,
      listingsChannelFilter: '',
      listingsStatusFilter: '',
      listingsObserver: null,
      clickOutsideHandler: null,
    };
  },

  computed: {
    allSelected() {
      return this.products.length > 0 && this.products.every(p => this.selectedProducts.includes(p.id));
    },
    allListingsSelected() {
      return this.listings.length > 0 && this.listings.every(l => this.selectedListings.includes(l.id));
    },
    canPush() {
      return this.selectedProducts.length > 0 && this.selectedChannels.length > 0;
    },
    hasMore() {
      return this.currentPage < this.lastPage;
    },
    listingsHasMore() {
      return this.listingsPage < this.listingsLastPage;
    },
  },

  async created() {
    await Promise.all([
      this.fetchProducts(1, true),
      this.fetchListings(1, true),
      this.fetchChannels(),
      this.fetchStores(),
    ]);
  },

  mounted() {
    this.$nextTick(() => {
      this.listingsObserver = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting && this.listingsHasMore && !this.listingsLoadingMore) {
          this.fetchListings(this.listingsPage + 1, false);
        }
      }, { rootMargin: '200px' });
      if (this.$refs.listingsSentinel) this.listingsObserver.observe(this.$refs.listingsSentinel);

      this.clickOutsideHandler = (e) => {
        if (this.$refs.storeDropdownRef && !this.$refs.storeDropdownRef.contains(e.target)) {
          this.storeDropdownOpen = false;
        }
      };
      document.addEventListener('click', this.clickOutsideHandler);
    });
  },

  beforeUnmount() {
    this.listingsObserver?.disconnect();
    document.removeEventListener('click', this.clickOutsideHandler);
  },

  methods: {
    async fetchProducts(page = 1, replace = false) {
      replace ? (this.loadingProducts = true, this.products = []) : (this.loadingMore = true);
      try {
        const params = new URLSearchParams({ page });
        if (this.search) params.set('search', this.search);
        this.selectedStoreIds.forEach(id => params.append('store_ids[]', id));
        const data = await window.api(`/api/products?${params}`);
        this.products = replace ? data.data : [...this.products, ...data.data];
        this.currentPage = data.current_page;
        this.lastPage = data.last_page;
      } finally {
        this.loadingProducts = false;
        this.loadingMore = false;
      }
    },

    async fetchListings(page = 1, replace = false) {
      replace ? (this.loadingListings = true, this.listings = []) : (this.listingsLoadingMore = true);
      try {
        const params = new URLSearchParams({ page });
        if (this.listingsChannelFilter) params.set('channel_integration_id', this.listingsChannelFilter);
        if (this.listingsStatusFilter)  params.set('status', this.listingsStatusFilter);
        const data = await window.api(`/api/listings?${params}`);
        this.listings = replace ? data.data : [...this.listings, ...data.data];
        this.listingsPage = data.current_page;
        this.listingsLastPage = data.last_page;
        this.listingsTotal = data.total;
        if (replace) this.selectedListings = [];
      } finally {
        this.loadingListings = false;
        this.listingsLoadingMore = false;
      }
    },

    onScroll(e) {
      if (this.loadingMore || !this.hasMore) return;
      const el = e.target;
      if (el.scrollTop + el.clientHeight >= el.scrollHeight - 50) this.fetchProducts(this.currentPage + 1, false);
    },

    debouncedSearch() {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => this.fetchProducts(1, true), 350);
    },

    toggleAll(e) {
      const ids = this.products.map(p => p.id);
      this.selectedProducts = e.target.checked
        ? [...new Set([...this.selectedProducts, ...ids])]
        : this.selectedProducts.filter(id => !ids.includes(id));
    },

    toggleAllListings(e) {
      this.selectedListings = e.target.checked ? this.listings.map(l => l.id) : [];
    },

    async fetchStores() {
      this.stores = (await window.api('/api/stores/all')) || [];
    },

    clearStoreFilter() {
      this.selectedStoreIds = [];
      this.storeDropdownOpen = false;
      this.fetchProducts(1, true);
    },

    async fetchChannels() {
      const data = await window.api('/api/channels');
      const active = data.filter(c => c.status === 'active');
      this.marketplaceChannels = active.filter(c => ['bol', 'amazon', 'tokopedia', 'shopee', 'olx'].includes(c.channel_type));
      this.storeChannels = active.filter(c => ['woocommerce', 'shopify', 'magento', 'cs_cart'].includes(c.channel_type));
    },

    async pushToMarketplaces() {
      this.pushing = true;
      this.message = null;
      let created = 0, errors = 0;
      await Promise.allSettled(
        this.selectedProducts.flatMap(product_id =>
          this.selectedChannels.map(channel_integration_id =>
            window.api('/listings', { body: { product_id, channel_integration_id } })
              .then(() => created++)
              .catch(() => errors++)
          )
        )
      );
      await this.fetchListings(1, true);
      this.message = {
        type: errors === 0 ? 'success' : 'error',
        text: `${created} listing(s) created${errors ? `, ${errors} skipped` : ''}.`,
      };
      this.selectedProducts = [];
      this.pushing = false;
    },

    async push(listing) {
      try {
        await window.api(`/listings/${listing.id}/push`, { method: 'POST' });
        listing.status = 'pending';
        this.message = { type: 'success', text: 'Re-push queued.' };
      } catch {
        this.message = { type: 'error', text: 'Push failed.' };
      }
    },

    async remove(listing) {
      if (!confirm('Delete this listing?')) return;
      try {
        await window.api(`/listings/${listing.id}`, { method: 'DELETE' });
        this.listings = this.listings.filter(l => l.id !== listing.id);
        this.listingsTotal--;
      } catch {
        this.message = { type: 'error', text: 'Delete failed.' };
      }
    },

    async bulkPush() {
      if (!this.selectedListings.length) return;
      this.bulkActing = true;
      try {
        await window.api('/listings/bulk-push', { body: { ids: this.selectedListings } });
        this.selectedListings.forEach(id => {
          const l = this.listings.find(l => l.id === id);
          if (l) l.status = 'pending';
        });
        this.message = { type: 'success', text: `${this.selectedListings.length} listing(s) queued for re-push.` };
        this.selectedListings = [];
      } catch {
        this.message = { type: 'error', text: 'Bulk push failed.' };
      } finally {
        this.bulkActing = false;
      }
    },

    async bulkDelete() {
      if (!confirm(`Delete ${this.selectedListings.length} listing(s)?`)) return;
      this.bulkActing = true;
      try {
        await window.api('/listings/bulk-delete', { body: { ids: this.selectedListings } });
        this.listings = this.listings.filter(l => !this.selectedListings.includes(l.id));
        this.listingsTotal -= this.selectedListings.length;
        this.message = { type: 'success', text: `${this.selectedListings.length} listing(s) deleted.` };
        this.selectedListings = [];
      } catch {
        this.message = { type: 'error', text: 'Bulk delete failed.' };
      } finally {
        this.bulkActing = false;
      }
    },

    statusBadge(status) {
      return {
        pending:  'bg-yellow-100 text-yellow-700',
        active:   'bg-green-100 text-green-700',
        error:    'bg-red-100 text-red-700',
        delisted: 'bg-gray-100 text-gray-500',
      }[status] ?? 'bg-gray-100 text-gray-600';
    },
  },
};
</script>

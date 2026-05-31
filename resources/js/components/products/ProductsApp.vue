<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-700">Products</h3>
        <p v-if="total > 0" class="text-sm text-gray-400 mt-0.5">{{ total }} products total</p>
      </div>
      <div class="flex gap-2">
        <input v-model="search" @input="debouncedSearch" type="text" placeholder="Search…"
          class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
        <select v-model="storeFilter" @change="fetchProducts(1, true)" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
          <option value="">All products</option>
          <option value="none">— No store</option>
          <option v-for="s in stores" :key="s.id" :value="String(s.id)">{{ s.name }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-400">Loading…</div>

    <div v-else-if="products.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
      No products yet. Sync a store to import products.
    </div>

    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-4 py-2 border-b bg-gray-50 flex items-center justify-between text-xs text-gray-500">
        <span>Showing {{ products.length }} of {{ total }}</span>
        <span v-if="hasMore" class="text-indigo-500">Scroll down to load more</span>
      </div>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-16"></th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="product in products" :key="product.id">
            <td class="px-4 py-3">
              <img v-if="product.images?.[0]" :src="product.images[0]" class="w-10 h-10 object-cover rounded" />
              <div v-else class="w-10 h-10 bg-gray-100 rounded"></div>
            </td>
            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ product.title }}</td>
            <td class="px-4 py-3 text-sm text-gray-400 font-mono">{{ product.sku || '—' }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">€{{ product.price }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">{{ product.stock }}</td>
            <td class="px-4 py-3 text-sm text-gray-400">{{ product.store?.name }}</td>
            <td class="px-4 py-3 text-right">
              <a :href="`/products/${product.id}${storeFilter ? '?back=' + storeFilter : ''}`" class="text-sm text-indigo-600 hover:underline">View</a>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="loadingMore" class="text-center py-4 text-gray-400 text-sm">Loading more…</div>
      <div v-else-if="!hasMore" class="text-center py-3 text-gray-400 text-xs border-t">
        All {{ total }} products loaded
      </div>
    </div>

    <!-- Sentinel outside v-else so it exists in DOM from mount -->
    <div ref="sentinel" class="h-1"></div>
  </div>
</template>

<script>
export default {
  name: 'ProductsApp',

  data() {
    return {
      products: [],
      stores: [],
      loading: true,
      loadingMore: false,
      search: '',
      storeFilter: new URLSearchParams(window.location.search).get('store_id') || '',
      currentPage: 1,
      lastPage: 1,
      total: 0,
      searchTimer: null,
      observer: null,
    };
  },

  computed: {
    hasMore() {
      return this.currentPage < this.lastPage;
    },
  },

  async created() {
    const [, storeData] = await Promise.all([
      this.fetchProducts(1, true),
      window.api('/api/stores/all'),
    ]);
    this.stores = storeData || [];
  },

  mounted() {
    this.$nextTick(() => {
      this.observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting && this.hasMore && !this.loadingMore) {
          this.fetchProducts(this.currentPage + 1, false);
        }
      }, { rootMargin: '200px' });
      if (this.$refs.sentinel) this.observer.observe(this.$refs.sentinel);
    });
  },

  beforeUnmount() {
    this.observer?.disconnect();
  },

  methods: {
    async fetchProducts(page = 1, replace = false) {
      if (replace) {
        this.loading = true;
        this.products = [];
      } else {
        this.loadingMore = true;
      }

      try {
        const params = new URLSearchParams({ page });
        if (this.search) params.set('search', this.search);
        if (this.storeFilter) params.set('store_id', this.storeFilter);
        // Update the browser URL to reflect the current filter (without page reload)
        const url = new URL(window.location);
        this.storeFilter ? url.searchParams.set('store_id', this.storeFilter) : url.searchParams.delete('store_id');
        window.history.replaceState({}, '', url);

        const data = await window.api(`/api/products?${params}`);
        this.products = replace ? data.data : [...this.products, ...data.data];
        this.currentPage = data.current_page;
        this.lastPage = data.last_page;
        this.total = data.total;
      } finally {
        this.loading = false;
        this.loadingMore = false;
      }
    },

    debouncedSearch() {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => this.fetchProducts(1, true), 350);
    },
  },
};
</script>

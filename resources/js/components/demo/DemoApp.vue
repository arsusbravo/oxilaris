<template>
  <div class="min-h-screen bg-slate-50 pb-16">

    <!-- Step 1: Scan -->
    <div v-if="step === 1 && scansLeft === 0" class="max-w-xl mx-auto px-4 pt-10">
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
        <p class="text-lg font-bold text-slate-900 mb-2">Scan gratis sudah digunakan</p>
        <p class="text-slate-500 text-sm mb-6">Daftar gratis untuk scan tanpa batas dan akses semua fitur OXIlaris.</p>
        <a href="/register"
           class="block w-full py-3.5 rounded-xl font-bold text-white text-sm mb-3 transition-all hover:opacity-90"
           style="background-color:#C0391A;">
          Daftar Gratis →
        </a>
        <a href="/login" class="text-xs text-slate-400 hover:text-slate-600 transition-colors">
          Sudah punya akun? Masuk
        </a>
      </div>
    </div>

    <div v-else-if="step === 1" class="max-w-xl mx-auto px-4 pt-10">

      <!-- Header -->
      <div class="text-center mb-8">
        <span class="inline-block bg-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1 rounded-full mb-3">
          Demo Gratis
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-snug">
          Scan foto produk,<br>AI isi detailnya otomatis
        </h1>
        <p class="text-slate-500 mt-3 text-sm leading-relaxed">
          Upload foto produk Anda dan biarkan AI membuat judul, deskripsi,<br class="hidden sm:block"> kategori, dan spesifikasi dalam hitungan detik.
        </p>
      </div>

      <!-- Upload card -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200">

        <!-- Image preview -->
        <div v-if="previewUrl"
             class="relative bg-slate-100 rounded-t-2xl flex items-center justify-center"
             style="min-height:200px">
          <img :src="previewUrl" class="max-h-64 w-full object-contain rounded-t-2xl" />
          <button @click="clearImage"
                  class="absolute top-3 right-3 bg-white/80 hover:bg-white rounded-full p-1.5 shadow text-slate-500 hover:text-red-500 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Drop zone (when no image) -->
        <label v-else
               class="flex flex-col items-center justify-center gap-4 cursor-pointer hover:bg-slate-50 transition-colors rounded-t-2xl border-b border-slate-100"
               style="padding: 3rem 1.5rem;"
               @dragover.prevent @drop.prevent="onDrop">
          <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center">
            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <div class="text-center">
            <p class="text-sm font-semibold text-slate-700">Ketuk untuk ambil foto atau pilih gambar</p>
            <p class="text-xs text-slate-400 mt-1">JPG, PNG, WebP — maks 8MB</p>
          </div>
          <input ref="fileInput" type="file" accept="image/*" capture="environment"
                 class="hidden" @change="onFileChange" />
        </label>

        <div style="padding: 1rem 1rem 1.25rem;">
          <!-- Error -->
          <div v-if="error" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2 mb-3">{{ error }}</div>

          <!-- Turnstile CAPTCHA -->
          <div v-show="!captchaVerified && previewUrl" ref="turnstileContainer" class="mb-4 p-4 rounded-lg bg-amber-50 border border-amber-200">
            <p class="text-xs text-amber-700 font-semibold mb-3">Verifikasi keamanan diperlukan untuk melanjutkan</p>
            <div id="turnstile-widget" class="cf-turnstile" :data-sitekey="turnstileSiteKey" data-theme="light" data-callback="turnstileCallback"></div>
          </div>

          <!-- CAPTCHA Required Message (fallback if widget doesn't show) -->
          <div v-if="previewUrl && !captchaVerified && turnstileSiteKey" class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200">
            <p class="text-sm text-amber-700 font-medium">Tunggu... widget keamanan sedang dimuat...</p>
          </div>

          <!-- Submit -->
          <button @click="doScan" :disabled="scanning || !previewUrl || !captchaVerified"
                  class="w-full rounded-xl font-bold text-white text-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                  style="background-color:#C0391A; padding: 0.875rem;"
                  :title="!captchaVerified && previewUrl ? 'Selesaikan verifikasi CAPTCHA terlebih dahulu' : ''">
            <span v-if="scanning" class="flex items-center justify-center gap-2">
              <svg style="width:1rem;height:1rem;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                <circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              AI sedang menganalisis...
            </span>
            <span v-else-if="!captchaVerified && previewUrl">Verifikasi CAPTCHA terlebih dahulu →</span>
            <span v-else>✦ Scan dengan AI →</span>
          </button>
        </div>
      </div>

      <!-- Footer note -->
      <p class="text-center text-xs text-slate-400 mt-4">
        {{ scansLeft }} scan gratis tersisa &bull; Tidak perlu daftar untuk mencoba
      </p>
    </div>

    <!-- Step 2: Product Form -->
    <div v-else-if="step === 2" class="max-w-xl mx-auto px-4 pt-10">
      <div class="flex items-center gap-3 mb-6">
        <button v-if="scansLeft > 0" @click="step = 1" class="text-slate-400 hover:text-slate-600 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
        </button>
        <h2 class="font-bold text-lg text-slate-900">Cek & edit detail produk</h2>
      </div>

      <!-- Product image preview -->
      <div v-if="previewUrl" class="mb-4 rounded-xl overflow-hidden border border-slate-200 bg-white">
        <img :src="previewUrl" class="w-full max-h-48 object-contain bg-slate-50" />
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">

        <!-- Title -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Produk *</label>
          <input v-model="form.title" type="text"
                 class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none" />
        </div>

        <!-- Price / Stock / SKU -->
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Harga (Rp)</label>
            <input v-model="form.price" type="number" min="0" placeholder="0"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Stok</label>
            <input v-model="form.stock" type="number" min="0" placeholder="0"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">SKU</label>
            <input v-model="form.sku" type="text" placeholder="—"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none" />
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi</label>
          <textarea v-model="form.description" rows="5"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none resize-none leading-relaxed"></textarea>
        </div>

        <!-- Categories -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
          <input v-model="form.categories" type="text" placeholder="Elektronik, Audio, Speaker"
                 class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none" />
        </div>

        <!-- Specifications -->
        <div v-if="form.attributes.length">
          <label class="block text-xs font-semibold text-slate-600 mb-2">Spesifikasi</label>
          <div class="space-y-2">
            <div v-for="(attr, i) in form.attributes" :key="i" class="flex gap-2">
              <input v-model="attr.name" type="text" placeholder="Nama"
                     class="w-1/3 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none" />
              <input v-model="attr.values" type="text" placeholder="Nilai"
                     class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none" />
            </div>
          </div>
        </div>

        <button @click="saveProduct" :disabled="!form.title"
                class="w-full py-3.5 rounded-xl font-bold text-white text-sm transition-all disabled:opacity-40"
                style="background-color:#C0391A;">
          Simpan Produk →
        </button>
      </div>
    </div>

    <!-- Step 3: After save + channels -->
    <div v-else-if="step === 3" class="max-w-xl mx-auto px-4 pt-10">

      <!-- Success (only shown if a product was saved this session) -->
      <div v-if="savedProduct" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 mb-6 flex items-start gap-4">
        <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-emerald-800 text-sm">Produk berhasil dibuat!</p>
          <p class="text-emerald-700 text-sm mt-0.5 font-medium">{{ savedProduct.title }}</p>
          <p class="text-emerald-600 text-xs mt-1">Daftar untuk menyimpan produk ini dan push ke marketplace.</p>
        </div>
      </div>

      <!-- Returning visitor (scan already used, no product this session) -->
      <div v-else class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6 flex items-start gap-4">
        <div class="w-10 h-10 rounded-full bg-amber-400 text-white flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-amber-800 text-sm">Scan gratis sudah digunakan</p>
          <p class="text-amber-700 text-xs mt-1">Daftar gratis untuk scan tanpa batas dan akses semua fitur.</p>
        </div>
      </div>

      <!-- Product detail -->
      <div v-if="savedProduct" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="font-bold text-slate-900 text-sm">Detail Produk</h3>
          <span class="text-xs bg-emerald-100 text-emerald-700 font-semibold px-2 py-0.5 rounded-full">Aktif</span>
        </div>

        <!-- Image -->
        <div v-if="previewUrl" class="bg-slate-50 border-b border-slate-100">
          <img :src="previewUrl" class="w-full max-h-52 object-contain" />
        </div>

        <div class="p-5 space-y-4">
          <!-- Title -->
          <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Nama Produk</p>
            <p class="text-slate-900 font-semibold text-base leading-snug">{{ savedProduct.title }}</p>
          </div>

          <!-- Price / Stock / SKU -->
          <div class="grid grid-cols-3 gap-3">
            <div class="bg-slate-50 rounded-xl p-3 text-center">
              <p class="text-xs text-slate-400 mb-1">Harga</p>
              <p class="font-bold text-slate-800 text-sm">{{ savedProduct.price ? 'Rp ' + Number(savedProduct.price).toLocaleString('id-ID') : '—' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 text-center">
              <p class="text-xs text-slate-400 mb-1">Stok</p>
              <p class="font-bold text-slate-800 text-sm">{{ savedProduct.stock || '—' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 text-center">
              <p class="text-xs text-slate-400 mb-1">SKU</p>
              <p class="font-bold text-slate-800 text-sm font-mono truncate">{{ savedProduct.sku || '—' }}</p>
            </div>
          </div>

          <!-- Categories -->
          <div v-if="savedProduct.categories">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Kategori</p>
            <div class="flex flex-wrap gap-1.5">
              <span v-for="cat in savedProduct.categories.split(',')" :key="cat"
                    class="text-xs bg-indigo-50 text-indigo-700 font-medium px-2.5 py-1 rounded-full">
                {{ cat.trim() }}
              </span>
            </div>
          </div>

          <!-- Description -->
          <div v-if="savedProduct.description">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Deskripsi</p>
            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ savedProduct.description }}</p>
          </div>

          <!-- Specifications -->
          <div v-if="savedProduct.attributes && savedProduct.attributes.length">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Spesifikasi</p>
            <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 overflow-hidden">
              <div v-for="attr in savedProduct.attributes" :key="attr.name"
                   class="flex items-center px-3 py-2.5 bg-white">
                <span class="text-xs text-slate-500 w-1/3 shrink-0">{{ attr.name }}</span>
                <span class="text-xs font-medium text-slate-800">{{ attr.values }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Channels section -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100">
          <h3 class="font-bold text-slate-900 text-sm">Langkah berikutnya: hubungkan platform penjualan</h3>
          <p class="text-xs text-slate-400 mt-0.5">Klik platform untuk mulai berjualan di sana</p>
        </div>
        <div class="p-4 grid grid-cols-2 gap-3">
          <button v-for="p in platforms" :key="p"
                  @click="selectedPlatform = p"
                  class="flex items-center gap-3 px-4 py-3.5 rounded-xl border-2 border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 transition-all text-left group">
            <span class="text-2xl">{{ meta[p].emoji }}</span>
            <div>
              <p class="text-sm font-semibold text-slate-800 group-hover:text-indigo-700">{{ meta[p].label }}</p>
              <p class="text-xs text-slate-400">Sambungkan →</p>
            </div>
          </button>
          <div v-if="!platforms.length"
               class="col-span-2 text-sm text-slate-400 text-center py-4">
            Tidak ada platform yang dikonfigurasi.
          </div>
        </div>
      </div>

      <!-- Register CTA -->
      <div class="bg-slate-900 rounded-2xl p-6 text-center">
        <p class="text-white font-bold text-base mb-1">Daftar untuk akses penuh</p>
        <p class="text-slate-400 text-sm mb-5">Simpan produk, hubungkan channel, dan mulai berjualan di semua platform.</p>
        <a href="/register"
           class="block w-full py-3.5 rounded-xl font-bold text-white text-sm mb-3 transition-all hover:opacity-90"
           style="background-color:#C0391A;">
          Daftar Gratis →
        </a>
        <a href="/login" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">
          Sudah punya akun? Masuk
        </a>
      </div>
    </div>

    <!-- Platform connect overlay -->
    <div v-if="selectedPlatform"
         class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center p-4"
         @click.self="selectedPlatform = null">
      <div class="bg-white rounded-2xl w-full max-w-sm p-6 text-center shadow-xl">
        <div class="text-4xl mb-3">{{ meta[selectedPlatform].emoji }}</div>
        <h3 class="font-bold text-slate-900 text-lg mb-2">
          Hubungkan {{ meta[selectedPlatform].label }}
        </h3>
        <p class="text-slate-500 text-sm mb-6">
          Daftar gratis untuk menghubungkan {{ meta[selectedPlatform].label }} dan mulai push produk Anda ke sana.
        </p>
        <a href="/register"
           class="block w-full py-3 rounded-xl font-bold text-white text-sm mb-3 transition-all hover:opacity-90"
           style="background-color:#C0391A;">
          Daftar & Hubungkan →
        </a>
        <button @click="selectedPlatform = null"
                class="text-sm text-slate-400 hover:text-slate-600 transition-colors">
          Nanti saja
        </button>
      </div>
    </div>

  </div>
</template>

<script>
const PLATFORM_META = {
  tiktok_shop: { emoji: '🟢', label: 'Tokopedia' },
  shopee:      { emoji: '🟠', label: 'Shopee' },
  shopify:     { emoji: '🛍️', label: 'Shopify' },
  woocommerce: { emoji: '🔌', label: 'WooCommerce' },
}

export default {
  name: 'DemoApp',

  data() {
    const el = document.getElementById('demo-app')
    return {
      step:               1,
      scanning:           false,
      error:              null,
      previewUrl:         null,
      imageBase64:        null,
      platforms:          JSON.parse(el?.dataset.platforms || '[]'),
      scansLeft:          parseInt(el?.dataset.scansLeft || '2'),
      csrf:               el?.dataset.csrf || '',
      turnstileSiteKey:   el?.dataset.turnstileKey || '',
      turnstileToken:     null,
      captchaVerified:    false,
      meta:               PLATFORM_META,
      selectedPlatform:   null,
      savedProduct:       null,
      form: {
        title:       '',
        description: '',
        price:       '',
        stock:       '',
        sku:         '',
        categories:  '',
        attributes:  [],
      },
    }
  },

  created() {
    if (this.scansLeft === 0) this.step = 3
  },

  mounted() {
    window.turnstileCallback = (token) => {
      this.turnstileToken = token
      this.captchaVerified = true
    }

    // Watch for when preview image is set and render Turnstile
    this.$watch('previewUrl', (newVal) => {
      if (newVal && window.turnstile) {
        this.$nextTick(() => {
          const container = document.getElementById('turnstile-widget')
          if (container && !container.querySelector('iframe')) {
            window.turnstile.render('#turnstile-widget', {
              sitekey: this.turnstileSiteKey,
              theme: 'light',
              callback: 'turnstileCallback'
            })
          }
        })
      }
    })
  },

  methods: {
    onFileChange(e) {
      const file = e.target.files[0]
      if (!file) return

      const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
      if (!allowed.includes(file.type)) {
        this.error = `Format file tidak didukung (${file.type || 'tidak diketahui'}). Gunakan JPG, PNG, WebP, atau GIF.`
        if (this.$refs.fileInput) this.$refs.fileInput.value = ''
        return
      }

      this.error = null
      const reader = new FileReader()
      reader.onload = (ev) => {
        this.imageBase64 = ev.target.result
        this.previewUrl  = ev.target.result
      }
      reader.readAsDataURL(file)
    },

    onDrop(e) {
      const file = e.dataTransfer.files[0]
      if (!file) return
      const fakeEvent = { target: { files: [file] } }
      this.onFileChange(fakeEvent)
    },

    clearImage() {
      this.imageBase64 = null
      this.previewUrl  = null
      this.turnstileToken = null
      this.captchaVerified = false
      if (this.$refs.fileInput) this.$refs.fileInput.value = ''
      // Reset Turnstile widget
      if (window.turnstile) {
        try {
          window.turnstile.reset()
        } catch (e) {
          // Widget might not be rendered yet
          const container = document.getElementById('turnstile-widget')
          if (container) {
            container.innerHTML = ''
          }
        }
      }
    },

    async doScan() {
      this.error   = null
      this.scanning = true

      // Warn if base64 image is too large (> 10MB)
      if (this.imageBase64 && this.imageBase64.length > 10 * 1024 * 1024) {
        this.error = 'Foto terlalu besar. Gunakan foto yang lebih kecil (maks 8MB).'
        this.scanning = false
        return
      }

      const body = {
        image_data: this.imageBase64,
        'cf-turnstile-response': this.turnstileToken,
      }

      try {
        const r = await fetch('/demo/scan', {
          method:  'POST',
          headers: {
            'Content-Type':  'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  this.csrf,
          },
          body: JSON.stringify(body),
        })

        const data = await r.json()

        if (!r.ok) {
          if (r.status === 429) {
            if (this.scansLeft === 0) {
              this.step = 3
            } else {
              this.error = 'Terlalu banyak percobaan. Tunggu beberapa saat lalu coba lagi.'
            }
          } else {
            this.error = data.error || 'Terjadi kesalahan. Coba lagi.'
          }
          return
        }

        this.form.title       = data.title || ''
        this.form.description = data.description || ''
        this.form.categories  = Array.isArray(data.categories) ? data.categories.join(', ') : (data.categories || '')
        this.form.attributes  = (data.specifications || []).map(s => ({
          name:   s.name,
          values: Array.isArray(s.values) ? s.values.join(', ') : s.values,
        }))

        this.scansLeft = Math.max(0, this.scansLeft - 1)
        this.step = 2
      } catch {
        this.error = 'Koneksi gagal. Periksa internet Anda dan coba lagi.'
      } finally {
        this.scanning = false
      }
    },

    saveProduct() {
      this.savedProduct = { ...this.form }
      this.step = 3
    },
  },
}
</script>

<style>
@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
</style>

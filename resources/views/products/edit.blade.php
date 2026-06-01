<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.show', $product) }}"
               class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-md hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-semibold text-gray-800">{{ __('ui.edit_product') }}</h2>
                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $product->title }}</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 max-w-3xl mx-auto">
        <form method="POST" action="{{ route('products.update', $product) }}"
              x-data="{
                images: {{ json_encode(count($product->images ?? []) ? array_map(fn($u) => ['url' => $u], $product->images) : [['url' => '']]) }},
                attributes: {{ json_encode(count($product->attributes ?? []) ? array_map(fn($a) => ['name' => $a['name'] ?? '', 'values' => implode(', ', $a['values'] ?? [])], $product->attributes) : [['name' => '', 'values' => '']]) }},
                uploading: false,
                analyzing: false,
                uploadError: null,
                analyzeError: null,
                selectedForAnalysis: null,
                aiLocale: '{{ auth()->user()->ui_locale ?? 'en' }}',
                addImage() { this.images.push({ url: '' }) },
                removeImage(i) { this.images.splice(i, 1) },
                addAttr() { this.attributes.push({ name: '', values: '' }) },
                removeAttr(i) { this.attributes.splice(i, 1) },
                getCsrf() {
                  const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
                  return m ? decodeURIComponent(m[1]) : '';
                },
                async uploadImages(event) {
                  const files = Array.from(event.target.files);
                  if (!files.length) return;
                  this.uploading = true; this.uploadError = null;
                  try {
                    for (const file of files) {
                      const fd = new FormData(); fd.append('image', file);
                      const r = await fetch('/api/products/upload-image', {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'X-XSRF-TOKEN': this.getCsrf() },
                        body: fd,
                      });
                      if (!r.ok) throw new Error('Upload failed for ' + file.name);
                      const d = await r.json();
                      this.images = this.images.filter(i => i.url);
                      this.images.push({ url: d.url });
                      if (!this.selectedForAnalysis) this.selectedForAnalysis = d.url;
                    }
                  } catch(e) { this.uploadError = e.message; }
                  finally { this.uploading = false; event.target.value = ''; }
                },
                async analyzeSelected() {
                  if (!this.selectedForAnalysis) return;
                  this.analyzing = true; this.analyzeError = null;
                  try {
                    const r = await fetch('/api/products/analyze-image', {
                      method: 'POST',
                      headers: { Accept: 'application/json', 'X-XSRF-TOKEN': this.getCsrf(), 'Content-Type': 'application/json' },
                      body: JSON.stringify({ url: this.selectedForAnalysis, locale: this.aiLocale }),
                    });
                    if (!r.ok) throw new Error('Analysis failed');
                    const d = await r.json();
                    if (d.error) { this.analyzeError = d.error; return; }
                    if (d.title)       document.getElementById('title').value       = d.title;
                    if (d.description) document.getElementById('description').value = d.description.replace(/\\n/g, '\n');
                  } catch(e) { this.analyzeError = e.message; }
                  finally { this.analyzing = false; }
                }
              }">
            @csrf
            @method('PUT')
            @include('products._form', ['stores' => $stores, 'product' => $product])
            <div class="flex items-center gap-3 pt-4 border-t mt-6">
                <x-primary-button>{{ __('ui.save_changes') }}</x-primary-button>
                <a href="{{ route('products.show', $product) }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('ui.cancel') }}</a>
            </div>
        </form>
    </div>
</x-app-layout>

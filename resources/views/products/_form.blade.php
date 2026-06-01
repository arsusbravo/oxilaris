@php $p = $product ?? null; @endphp

<div class="space-y-6">

    {{-- Title --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 space-y-4">
        <h3 class="font-semibold text-slate-700 text-sm">Basic Information</h3>

        <div>
            <x-input-label for="title" value="Title *" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                value="{{ old('title', $p?->title) }}" required />
            <x-input-error :messages="$errors->get('title')" class="mt-1" />
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <x-input-label for="price" value="Price (€)" />
                <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full"
                    value="{{ old('price', $p?->price) }}" placeholder="0.00" />
                <x-input-error :messages="$errors->get('price')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="stock" value="Stock" />
                <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full"
                    value="{{ old('stock', $p?->stock ?? 0) }}" />
                <x-input-error :messages="$errors->get('stock')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="sku" value="SKU" />
                <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full font-mono"
                    value="{{ old('sku', $p?->sku) }}" />
                <x-input-error :messages="$errors->get('sku')" class="mt-1" />
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'draft' => 'Draft'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $p?->status ?? 'active') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="store_id" value="Store (optional)" />
                <select id="store_id" name="store_id"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">— No store —</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ old('store_id', $p?->store_id) == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <x-input-label for="description" value="Description" />
            <textarea id="description" name="description" rows="8"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm leading-relaxed">{{ old('description', $p?->description) }}</textarea>
        </div>

        <div>
            <x-input-label for="categories" value="Categories (comma-separated)" />
            <x-text-input id="categories" name="categories" type="text" class="mt-1 block w-full"
                value="{{ old('categories', implode(', ', $p?->categories ?? [])) }}"
                placeholder="Electronics, Audio, Speakers" />
        </div>
    </div>

    {{-- Images --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">

        {{-- Header: title + action buttons --}}
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
            <h3 class="font-semibold text-slate-700 text-sm">Images</h3>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Hidden multi-file input --}}
                <input type="file" accept="image/*" multiple x-ref="fileInput"
                    @change="uploadImages($event)" class="hidden" />

                <button type="button" @click="$refs.fileInput.click()" :disabled="uploading"
                    class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span x-text="uploading ? 'Uploading…' : 'Upload Images'"></span>
                </button>

                <button type="button" @click="analyzeSelected()"
                    :disabled="!selectedForAnalysis || analyzing"
                    class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-40 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                    <span x-text="analyzing ? 'Analyzing…' : '✦ Analyze with AI'"></span>
                </button>

                <button type="button" @click="addImage()"
                    class="text-xs text-slate-500 hover:text-slate-700 font-medium px-2 py-1.5 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    + Add URL
                </button>
            </div>
        </div>

        {{-- Hint when images exist --}}
        <p x-show="images.some(i => i.url)" class="text-xs text-slate-400 mb-2">
            Select an image with the radio button, then click "Analyze with AI" to generate title &amp; description.
        </p>

        {{-- Upload error --}}
        <div x-show="uploadError" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-2">
            <span x-text="uploadError"></span>
        </div>

        {{-- Analyze error --}}
        <div x-show="analyzeError" class="text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2 mb-2">
            <span x-text="analyzeError"></span>
        </div>

        {{-- Image rows --}}
        <div class="space-y-2">
            <template x-for="(img, i) in images" :key="i">
                <div class="flex gap-2 items-center">
                    {{-- Radio: select for AI analysis --}}
                    <input type="radio" name="image-select" :value="img.url"
                        @change="selectedForAnalysis = img.url"
                        :disabled="!img.url"
                        class="shrink-0 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />

                    {{-- Thumbnail --}}
                    <div class="w-10 h-10 shrink-0 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center">
                        <img x-show="img.url" :src="img.url" class="w-full h-full object-cover" />
                        <svg x-show="!img.url" class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01" />
                        </svg>
                    </div>

                    {{-- URL input --}}
                    <input type="url" :name="`images[${i}]`" x-model="img.url"
                        placeholder="https://example.com/image.jpg"
                        class="flex-1 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" />

                    {{-- Remove --}}
                    <button type="button" @click="removeImage(i)"
                        class="text-red-400 hover:text-red-600 shrink-0 p-1" x-show="images.length > 1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Specifications / Attributes --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-slate-700 text-sm">Specifications</h3>
            <button type="button" @click="addAttr()"
                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Add specification</button>
        </div>
        <div class="space-y-2">
            <template x-for="(attr, i) in attributes" :key="i">
                <div class="flex gap-2 items-center">
                    <input type="text" :name="`attributes[${i}][name]`" x-model="attr.name"
                        placeholder="e.g. Color"
                        class="w-1/3 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    <input type="text" :name="`attributes[${i}][values]`" x-model="attr.values"
                        placeholder="Red, Blue, Green (comma-separated)"
                        class="flex-1 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    <button type="button" @click="removeAttr(i)"
                        class="text-red-400 hover:text-red-600 shrink-0 p-1" x-show="attributes.length > 1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
        <p class="text-xs text-slate-400 mt-2">Values are comma-separated. Leave blank to skip.</p>
    </div>

</div>

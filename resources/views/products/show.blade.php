<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}"
               class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-md hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="min-w-0">
                <h2 class="font-semibold text-gray-800 leading-tight truncate">{{ $product->title }}</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ $product->store->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="p-6 max-w-5xl mx-auto space-y-5">

        {{-- ── Main card: image + info ─────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="grid lg:grid-cols-2">

                {{-- Image gallery --}}
                <div class="p-6 border-b lg:border-b-0 lg:border-r border-slate-100"
                     x-data="{ active: {{ json_encode($product->images[0] ?? '') }} }">

                    @if(!empty($product->images))
                        {{-- Main image --}}
                        <div class="relative aspect-square rounded-xl overflow-hidden bg-slate-50 border border-slate-100">
                            <img :src="active" alt="{{ $product->title }}"
                                 class="w-full h-full object-contain" />
                        </div>

                        {{-- Thumbnails --}}
                        @if(count($product->images) > 1)
                            <div class="flex gap-2 mt-3 flex-wrap">
                                @foreach($product->images as $img)
                                    <button type="button"
                                            @click="active = {{ json_encode($img) }}"
                                            class="w-16 h-16 rounded-lg overflow-hidden border-2 transition-all shrink-0 focus:outline-none"
                                            :class="active === {{ json_encode($img) }}
                                                ? 'border-indigo-500 shadow-md'
                                                : 'border-transparent hover:border-slate-300'">
                                        <img src="{{ $img }}" alt="" class="w-full h-full object-cover" />
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="aspect-square rounded-xl bg-slate-100 flex flex-col items-center justify-center gap-3">
                            <svg class="w-14 h-14 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm text-slate-400">No image available</span>
                        </div>
                    @endif
                </div>

                {{-- Product info --}}
                <div class="p-6 flex flex-col gap-5">

                    {{-- Store badge + title --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-semibold text-indigo-600 uppercase tracking-widest">
                                {{ $product->store->name }}
                            </span>
                            @if(!empty($product->raw_data['product_url']))
                                <a href="{{ $product->raw_data['product_url'] }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-indigo-600 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    View in store
                                </a>
                            @endif
                        </div>
                        <h1 class="text-2xl font-bold text-slate-900 leading-snug">{{ $product->title }}</h1>
                    </div>

                    {{-- Price + stock --}}
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-3xl font-extrabold text-slate-900">
                            €{{ number_format((float) $product->price, 2) }}
                        </span>
                        @if((int) $product->stock > 0)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ $product->stock }} in stock
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-600 text-xs font-semibold rounded-full border border-red-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Out of stock
                            </span>
                        @endif
                    </div>

                    {{-- SKU / Status row --}}
                    <div class="flex items-center gap-5 py-3.5 border-y border-slate-100">
                        @if($product->sku)
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">SKU</p>
                                <p class="font-mono text-sm font-semibold text-slate-700 mt-0.5">{{ $product->sku }}</p>
                            </div>
                            <div class="w-px h-8 bg-slate-100"></div>
                        @endif
                        @if($product->status)
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Status</p>
                                <span class="mt-0.5 inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 capitalize">
                                    {{ $product->status }}
                                </span>
                            </div>
                            <div class="w-px h-8 bg-slate-100"></div>
                        @endif
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Variants</p>
                            <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ $product->variants->count() ?: '—' }}</p>
                        </div>
                    </div>

                    {{-- Categories --}}
                    @if(!empty($product->categories))
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Categories</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach((array) $product->categories as $cat)
                                    @if($cat)
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-medium rounded-full">
                                            {{ $cat }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Product-level attributes (Color: [Red][Blue], Size: [S][M][L]) --}}
                    @if(!empty($product->attributes))
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Specifications</p>
                            <div class="space-y-2.5">
                                @foreach((array) $product->attributes as $attr)
                                    @php
                                        $attrName   = is_array($attr) ? ($attr['name']   ?? '') : (string) $attr;
                                        $attrValues = is_array($attr) ? ($attr['values']  ?? []) : [];
                                    @endphp
                                    @if($attrName && !empty($attrValues))
                                        <div class="flex items-start gap-3">
                                            <span class="text-sm font-medium text-slate-500 w-20 shrink-0 pt-0.5">
                                                {{ $attrName }}
                                            </span>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach((array) $attrValues as $val)
                                                    @if((string) $val !== '')
                                                        <span class="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-md border border-indigo-100">
                                                            {{ $val }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Description --}}
            @if($product->description)
                <div class="px-6 py-5 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Description</p>
                    <div class="text-sm text-slate-600 leading-relaxed max-w-3xl whitespace-pre-line">{{ $product->description }}</div>
                </div>
            @endif
        </div>

        {{-- ── Variants ─────────────────────────────────────────────────────── --}}
        @if($product->variants->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800">Variants</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $product->variants->count() }} variant{{ $product->variants->count() !== 1 ? 's' : '' }}</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($product->variants as $variant)
                        @php
                            $attrs = [];
                            foreach ($variant->attributes ?? [] as $k => $v) {
                                if (is_array($v)) {
                                    foreach ($v as $ak => $av) { $attrs[(string) $ak] = (string) $av; }
                                } else {
                                    $attrs[(string) $k] = (string) $v;
                                }
                            }
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-slate-50 transition-colors">
                            {{-- Attribute chips --}}
                            <div class="flex flex-wrap gap-1.5 flex-1 min-w-0">
                                @forelse($attrs as $name => $value)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 shadow-sm">
                                        <span class="text-slate-400">{{ $name }}:</span>
                                        <span>{{ $value }}</span>
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-400 italic">Default variant</span>
                                @endforelse
                            </div>
                            {{-- SKU --}}
                            @if($variant->sku)
                                <span class="font-mono text-xs text-slate-400 shrink-0 hidden md:block">{{ $variant->sku }}</span>
                            @endif
                            {{-- Price --}}
                            <span class="text-sm font-bold text-slate-800 shrink-0 w-20 text-right">
                                €{{ number_format((float) $variant->price, 2) }}
                            </span>
                            {{-- Stock --}}
                            <span class="text-xs font-semibold shrink-0 w-24 text-right
                                {{ (int) $variant->stock > 0 ? 'text-emerald-600' : 'text-red-400' }}">
                                {{ (int) $variant->stock > 0 ? $variant->stock . ' in stock' : 'Out of stock' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Marketplace Listings ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-800">Marketplace Listings</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Channels this product is listed on</p>
                </div>
                <a href="{{ route('listings.index') }}"
                   class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                    Manage →
                </a>
            </div>

            @if($product->listings->isEmpty())
                <div class="px-6 py-10 text-center">
                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <p class="text-sm text-slate-400">Not listed on any marketplace yet.</p>
                    <a href="{{ route('listings.index') }}" class="mt-1 inline-block text-xs text-indigo-600 hover:underline">
                        Create a listing →
                    </a>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($product->listings as $listing)
                        @php
                            $sc = match($listing->status) {
                                'active'   => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'pending'  => 'bg-amber-50 text-amber-700 border-amber-100',
                                'error'    => 'bg-red-50 text-red-600 border-red-100',
                                default    => 'bg-slate-100 text-slate-500 border-slate-200',
                            };
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-slate-50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-700">{{ $listing->channelIntegration->name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5 capitalize">
                                    {{ str_replace('_', ' ', $listing->channelIntegration->channel_type) }}
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $sc }} capitalize">
                                {{ $listing->status }}
                            </span>
                            <span class="text-xs text-slate-400 shrink-0 w-28 text-right">
                                {{ $listing->last_pushed_at ? $listing->last_pushed_at->diffForHumans() : 'Never pushed' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>

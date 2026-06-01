<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('ui.add_channel') }}</h2>
            <a href="{{ route('channels.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← {{ __('ui.back') }}</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6"
                x-data="{ type: '{{ old('channel_type', request('type', '')) }}' }">

                <form method="POST" action="{{ route('channels.store') }}">
                    @csrf

                    {{-- Channel type --}}
                    <div class="mb-5">
                        <x-input-label for="channel_type" :value="__('ui.channel_type')" />
                        <select id="channel_type" name="channel_type" x-model="type" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">{{ __('ui.ch_select_placeholder') }}</option>
                            @php
                                $storeTypes    = ['woocommerce', 'shopify', 'magento', 'cs_cart'];
                                $marketTypes   = ['bol', 'amazon', 'tiktok_shop', 'shopee', 'olx'];
                                $adTypes       = ['google_ads', 'facebook_ads', 'tiktok_ads'];
                                $activeStores  = array_intersect_key($channelTypes, array_flip($storeTypes));
                                $activeMarkets = array_intersect_key($channelTypes, array_flip($marketTypes));
                                $activeAds     = array_intersect_key($channelTypes, array_flip($adTypes));
                                $oauthTypes    = ['shopify', 'woocommerce', 'tiktok_shop', 'shopee'];
                            @endphp
                            @if($activeStores)
                                <optgroup label="{{ __('ui.ch_optgroup_stores') }}">
                                    @foreach($activeStores as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if($activeMarkets)
                                <optgroup label="{{ __('ui.ch_optgroup_markets') }}">
                                    @foreach($activeMarkets as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if($activeAds)
                                <optgroup label="{{ __('ui.ch_optgroup_ads') }}">
                                    @foreach($activeAds as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <x-input-error :messages="$errors->get('channel_type')" class="mt-1" />
                    </div>

                    {{-- Label --}}
                    <div class="mb-6">
                        <x-input-label for="name" :value="__('ui.ch_label_field')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            value="{{ old('name') }}" required placeholder="{{ __('ui.ch_label_hint') }}" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    {{-- ── OAuth / Simple tier ────────────────────────────── --}}

                    {{-- Shopify --}}
                    <div x-show="type === 'shopify'" class="space-y-4 mb-5">
                        @if($platformAppSet['shopify'])
                            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm text-emerald-700">{{ __('ui.ch_oauth_redirect_notice', [':platform' => 'Shopify']) }}</p>
                            </div>
                            <div>
                                <x-input-label value="Shop Domain" />
                                <x-text-input type="text" name="credentials[shop_domain]" class="mt-1 block w-full"
                                    value="{{ old('credentials.shop_domain') }}" placeholder="mystore.myshopify.com" />
                                <p class="mt-1 text-xs text-gray-400">e.g. mystore.myshopify.com — without https://</p>
                            </div>
                        @else
                            @include('channels._guide', ['color' => 'indigo', 'title' => __('ui.ch_shopify_guide_title'),
                                'steps' => ['ch_shopify_step_1','ch_shopify_step_2','ch_shopify_step_3','ch_shopify_step_4'],
                                'url' => 'https://partners.shopify.com', 'urlLabel' => __('ui.ch_shopify_portal')])
                            <div>
                                <x-input-label value="Shop Domain (mystore.myshopify.com)" />
                                <x-text-input type="text" name="credentials[shop_domain]" class="mt-1 block w-full" value="{{ old('credentials.shop_domain') }}" placeholder="mystore.myshopify.com" />
                            </div>
                            <div><x-input-label value="Client ID" /><x-text-input type="text" name="credentials[client_id]" class="mt-1 block w-full" value="{{ old('credentials.client_id') }}" /></div>
                            <div><x-input-label value="Client Secret" /><x-text-input type="password" name="credentials[client_secret]" class="mt-1 block w-full" /></div>
                        @endif
                    </div>

                    {{-- WooCommerce --}}
                    <div x-show="type === 'woocommerce'" class="space-y-4 mb-5">
                        @if($platformAppSet['woocommerce'])
                            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm text-emerald-700">{{ __('ui.ch_oauth_redirect_notice', [':platform' => 'WooCommerce']) }}</p>
                            </div>
                            <div>
                                <x-input-label value="Store URL" />
                                <x-text-input type="text" name="credentials[site_url]" class="mt-1 block w-full"
                                    value="{{ old('credentials.site_url') }}" placeholder="https://yourstore.com" />
                            </div>
                        @else
                            @include('channels._guide', ['color' => 'indigo', 'title' => __('ui.ch_woo_guide_title'),
                                'steps' => ['ch_woo_step_1','ch_woo_step_2','ch_woo_step_3','ch_woo_step_4'],
                                'url' => 'https://woocommerce.com/document/woocommerce-rest-api/', 'urlLabel' => __('ui.ch_woo_portal')])
                            <div><x-input-label value="Store URL (https://yourstore.com)" /><x-text-input type="text" name="credentials[site_url]" class="mt-1 block w-full" value="{{ old('credentials.site_url') }}" /></div>
                            <div><x-input-label value="Consumer Key" /><x-text-input type="text" name="credentials[consumer_key]" class="mt-1 block w-full" value="{{ old('credentials.consumer_key') }}" /></div>
                            <div><x-input-label value="Consumer Secret" /><x-text-input type="password" name="credentials[consumer_secret]" class="mt-1 block w-full" /></div>
                        @endif
                    </div>

                    {{-- Magento --}}
                    <div x-show="type === 'magento'" class="space-y-4 mb-5">
                        @include('channels._guide', ['color' => 'indigo', 'title' => __('ui.ch_magento_guide_title'),
                            'steps' => ['ch_magento_step_1','ch_magento_step_2','ch_magento_step_3','ch_magento_step_4','ch_magento_step_5'],
                            'url' => 'https://experienceleague.adobe.com/docs/commerce-admin/systems/integrations.html', 'urlLabel' => __('ui.ch_magento_portal')])
                        <div><x-input-label value="Base URL (https://yourstore.com)" /><x-text-input type="text" name="credentials[base_url]" class="mt-1 block w-full" value="{{ old('credentials.base_url') }}" /></div>
                        <div><x-input-label value="Access Token" /><x-text-input type="password" name="credentials[access_token]" class="mt-1 block w-full" /></div>
                    </div>

                    {{-- CS-Cart --}}
                    <div x-show="type === 'cs_cart'" class="space-y-4 mb-5">
                        @include('channels._guide', ['color' => 'indigo', 'title' => __('ui.ch_cscart_guide_title'),
                            'steps' => ['ch_cscart_step_1','ch_cscart_step_2','ch_cscart_step_3','ch_cscart_step_4'],
                            'url' => 'https://docs.cs-cart.com/latest/developer_guide/api/index.html', 'urlLabel' => __('ui.ch_cscart_portal')])
                        <div><x-input-label value="Store URL (https://yourstore.com)" /><x-text-input type="text" name="credentials[store_url]" class="mt-1 block w-full" value="{{ old('credentials.store_url') }}" placeholder="https://yourstore.com" /></div>
                        <div><x-input-label value="Admin Email" /><x-text-input type="text" name="credentials[api_email]" class="mt-1 block w-full" value="{{ old('credentials.api_email') }}" placeholder="admin@yourstore.com" /></div>
                        <div><x-input-label value="API Key" /><x-text-input type="password" name="credentials[api_key]" class="mt-1 block w-full" /></div>
                    </div>

                    {{-- BOL.com --}}
                    <div x-show="type === 'bol'" class="space-y-4 mb-5">
                        @include('channels._guide', ['color' => 'violet', 'title' => __('ui.ch_bol_guide_title'),
                            'steps' => ['ch_bol_step_1','ch_bol_step_2','ch_bol_step_3','ch_bol_step_4'],
                            'url' => 'https://developer.bol.com/retailer/overview/', 'urlLabel' => __('ui.ch_bol_portal')])
                        <div><x-input-label value="Client ID" /><x-text-input type="text" name="credentials[client_id]" class="mt-1 block w-full" value="{{ old('credentials.client_id') }}" /></div>
                        <div><x-input-label value="Client Secret" /><x-text-input type="password" name="credentials[client_secret]" class="mt-1 block w-full" /></div>
                    </div>

                    {{-- Amazon --}}
                    <div x-show="type === 'amazon'" class="space-y-4 mb-5">
                        @include('channels._guide', ['color' => 'amber', 'title' => __('ui.ch_amazon_guide_title'),
                            'steps' => ['ch_amazon_step_1','ch_amazon_step_2','ch_amazon_step_3','ch_amazon_step_4','ch_amazon_step_5'],
                            'url' => 'https://sellercentral.amazon.com', 'urlLabel' => __('ui.ch_amazon_portal')])
                        <div><x-input-label value="Client ID (LWA)" /><x-text-input type="text" name="credentials[client_id]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Client Secret (LWA)" /><x-text-input type="password" name="credentials[client_secret]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Seller ID" /><x-text-input type="text" name="credentials[seller_id]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Marketplace Region (e.g. eu-west-1)" /><x-text-input type="text" name="credentials[region]" class="mt-1 block w-full" placeholder="eu-west-1" /></div>
                    </div>

                    {{-- TikTok Shop --}}
                    <div x-show="type === 'tiktok_shop'" class="space-y-4 mb-5">
                        @if($platformAppSet['tiktok_shop'])
                            <div class="rounded-xl bg-slate-900 border border-slate-700 p-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm text-slate-300">{{ __('ui.ch_oauth_redirect_notice', [':platform' => 'TikTok Shop']) }}</p>
                            </div>
                        @else
                            <div class="rounded-xl bg-slate-900 border border-slate-700 p-4">
                                <div class="flex gap-3 items-start">
                                    <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-slate-900" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.76a4.85 4.85 0 01-1.01-.07z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-white text-sm mb-2">{{ __('ui.ch_tiktok_shop_guide_title') }}</p>
                                        <ol class="space-y-1.5">
                                            @php $stepNum = 1; @endphp
                                            @foreach(['ch_tiktok_shop_step_1','ch_tiktok_shop_step_2','ch_tiktok_shop_step_3','ch_tiktok_shop_step_4','ch_tiktok_shop_step_5'] as $key)
                                            @php $text = __('ui.' . $key); @endphp
                                            @if($text)
                                            <li class="flex items-start gap-2.5 text-sm text-slate-300">
                                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-slate-700 text-slate-200 text-xs font-bold flex items-center justify-center mt-0.5">{{ $stepNum++ }}</span>
                                                <span>{{ $text }}</span>
                                            </li>
                                            @endif
                                            @endforeach
                                        </ol>
                                        <a href="https://partner.tiktokshop.com" target="_blank" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-slate-300 hover:text-white">
                                            {{ __('ui.ch_tiktok_shop_portal') }}
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div><x-input-label value="App Key" /><x-text-input type="text" name="credentials[app_key]" class="mt-1 block w-full" value="{{ old('credentials.app_key') }}" /></div>
                            <div><x-input-label value="App Secret" /><x-text-input type="password" name="credentials[app_secret]" class="mt-1 block w-full" /></div>
                            <div><x-input-label value="Access Token" /><x-text-input type="password" name="credentials[access_token]" class="mt-1 block w-full" /></div>
                            <div><x-input-label value="Shop Cipher" /><x-text-input type="text" name="credentials[shop_cipher]" class="mt-1 block w-full" value="{{ old('credentials.shop_cipher') }}" /></div>
                        @endif
                    </div>

                    {{-- Shopee --}}
                    <div x-show="type === 'shopee'" class="space-y-4 mb-5">
                        @if($platformAppSet['shopee'])
                            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm text-emerald-700">{{ __('ui.ch_oauth_redirect_notice', [':platform' => 'Shopee']) }}</p>
                            </div>
                        @else
                            @include('channels._guide', ['color' => 'rose', 'title' => __('ui.ch_shopee_guide_title'),
                                'steps' => ['ch_shopee_step_1','ch_shopee_step_2','ch_shopee_step_3','ch_shopee_step_4'],
                                'url' => 'https://open.shopee.com', 'urlLabel' => __('ui.ch_shopee_portal')])
                            <div><x-input-label value="Partner ID" /><x-text-input type="text" name="credentials[partner_id]" class="mt-1 block w-full" value="{{ old('credentials.partner_id') }}" /></div>
                            <div><x-input-label value="Partner Key" /><x-text-input type="password" name="credentials[partner_key]" class="mt-1 block w-full" /></div>
                            <div><x-input-label value="Shop ID" /><x-text-input type="text" name="credentials[shop_id]" class="mt-1 block w-full" value="{{ old('credentials.shop_id') }}" /></div>
                            <div><x-input-label value="Access Token" /><x-text-input type="password" name="credentials[access_token]" class="mt-1 block w-full" /></div>
                        @endif
                    </div>

                    {{-- OLX --}}
                    <div x-show="type === 'olx'" class="space-y-4 mb-5">
                        @include('channels._guide', ['color' => 'orange', 'title' => __('ui.ch_olx_guide_title'),
                            'steps' => ['ch_olx_step_1','ch_olx_step_2','ch_olx_step_3','ch_olx_step_4'],
                            'url' => 'https://developer.olx.com', 'urlLabel' => __('ui.ch_olx_portal')])
                        <div><x-input-label value="Client ID" /><x-text-input type="text" name="credentials[client_id]" class="mt-1 block w-full" value="{{ old('credentials.client_id') }}" /></div>
                        <div><x-input-label value="Client Secret" /><x-text-input type="password" name="credentials[client_secret]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Access Token" /><x-text-input type="password" name="credentials[access_token]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Default Category ID (optional)" /><x-text-input type="text" name="credentials[category_id]" class="mt-1 block w-full" value="{{ old('credentials.category_id') }}" placeholder="3" /></div>
                    </div>

                    {{-- TikTok Ads --}}
                    <div x-show="type === 'tiktok_ads'" class="space-y-4 mb-5">
                        <div class="rounded-xl bg-slate-900 border border-slate-700 p-4">
                            <div class="flex gap-3 items-start">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-slate-900" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.76a4.85 4.85 0 01-1.01-.07z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-white text-sm mb-2">{{ __('ui.ch_tiktok_ads_guide_title') }}</p>
                                    <ol class="space-y-1.5">
                                        @foreach(['ch_tiktok_ads_step_1','ch_tiktok_ads_step_2','ch_tiktok_ads_step_3','ch_tiktok_ads_step_4'] as $i => $key)
                                        <li class="flex items-start gap-2.5 text-sm text-slate-300">
                                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-slate-700 text-slate-200 text-xs font-bold flex items-center justify-center mt-0.5">{{ $i+1 }}</span>
                                            <span>{{ __('ui.' . $key) }}</span>
                                        </li>
                                        @endforeach
                                    </ol>
                                    <a href="https://business-api.tiktok.com" target="_blank" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-slate-300 hover:text-white">
                                        {{ __('ui.ch_tiktok_ads_portal') }}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div><x-input-label value="App ID" /><x-text-input type="text" name="credentials[app_id]" class="mt-1 block w-full" value="{{ old('credentials.app_id') }}" /></div>
                        <div><x-input-label value="App Secret" /><x-text-input type="password" name="credentials[app_secret]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Access Token" /><x-text-input type="password" name="credentials[access_token]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Advertiser ID" /><x-text-input type="text" name="credentials[advertiser_id]" class="mt-1 block w-full" value="{{ old('credentials.advertiser_id') }}" placeholder="1234567890" /></div>
                    </div>

                    {{-- Google Ads --}}
                    <div x-show="type === 'google_ads'" class="space-y-4 mb-5">
                        @include('channels._guide', ['color' => 'sky', 'title' => __('ui.ch_google_guide_title'),
                            'steps' => ['ch_google_step_1','ch_google_step_2','ch_google_step_3','ch_google_step_4','ch_google_step_5'],
                            'url' => 'https://console.cloud.google.com', 'urlLabel' => __('ui.ch_google_portal')])
                        <div><x-input-label value="Client ID" /><x-text-input type="text" name="credentials[client_id]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Client Secret" /><x-text-input type="password" name="credentials[client_secret]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Developer Token" /><x-text-input type="password" name="credentials[developer_token]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Customer ID (without dashes)" /><x-text-input type="text" name="credentials[customer_id]" class="mt-1 block w-full" placeholder="1234567890" /></div>
                    </div>

                    {{-- Facebook Ads --}}
                    <div x-show="type === 'facebook_ads'" class="space-y-4 mb-5">
                        @include('channels._guide', ['color' => 'blue', 'title' => __('ui.ch_fb_guide_title'),
                            'steps' => ['ch_fb_step_1','ch_fb_step_2','ch_fb_step_3','ch_fb_step_4'],
                            'url' => 'https://developers.facebook.com', 'urlLabel' => __('ui.ch_fb_portal')])
                        <div><x-input-label value="Access Token" /><x-text-input type="password" name="credentials[access_token]" class="mt-1 block w-full" /></div>
                        <div><x-input-label value="Ad Account ID (act_XXXXXXXX)" /><x-text-input type="text" name="credentials[ad_account_id]" class="mt-1 block w-full" placeholder="act_123456789" /></div>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t">
                        <x-primary-button>
                            <span x-show="['shopify','woocommerce','tiktok_shop','shopee'].includes(type)">{{ __('ui.ch_continue_connect') }}</span>
                            <span x-show="!['shopify','woocommerce','tiktok_shop','shopee'].includes(type)">{{ __('ui.ch_save') }}</span>
                        </x-primary-button>
                        <a href="{{ route('channels.index') }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('ui.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

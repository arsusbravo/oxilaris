<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit — {{ $channel->name }}</h2>
            <a href="{{ route('channels.show', $channel) }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('channels.update', $channel) }}">
                    @csrf @method('PUT')

                    <div class="mb-5">
                        <x-input-label for="name" value="Label" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            value="{{ old('name', $channel->name) }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div class="mb-5">
                        <p class="text-sm font-medium text-gray-700 mb-3">
                            Update credentials
                            <span class="text-gray-400 font-normal">(leave blank to keep existing)</span>
                        </p>

                        @php
                            $fields = match($channel->channel_type) {
                                'woocommerce'  => ['site_url' => 'Store URL', 'consumer_key' => 'Consumer Key', 'consumer_secret' => 'Consumer Secret'],
                                'shopify'      => ['shop_domain' => 'Shop Domain', 'client_id' => 'Client ID', 'client_secret' => 'Client Secret'],
                                'magento'      => ['base_url' => 'Base URL', 'access_token' => 'Access Token'],
                                'cs_cart'      => ['store_url' => 'Store URL', 'api_email' => 'Admin Email', 'api_key' => 'API Key'],
                                'bol'          => ['client_id' => 'Client ID', 'client_secret' => 'Client Secret'],
                                'amazon'       => ['client_id' => 'Client ID', 'client_secret' => 'Client Secret', 'seller_id' => 'Seller ID', 'region' => 'Region'],
                                'google_ads'   => ['client_id' => 'Client ID', 'client_secret' => 'Client Secret', 'developer_token' => 'Developer Token', 'customer_id' => 'Customer ID'],
                                'facebook_ads' => ['access_token' => 'Access Token', 'ad_account_id' => 'Ad Account ID'],
                                'tokopedia'    => ['client_id' => 'Client ID', 'client_secret' => 'Client Secret', 'fs_id' => 'Fulfillment Service ID', 'shop_id' => 'Shop ID'],
                                'shopee'       => ['partner_id' => 'Partner ID', 'partner_key' => 'Partner Key', 'shop_id' => 'Shop ID', 'access_token' => 'Access Token'],
                                'olx'          => ['client_id' => 'Client ID', 'client_secret' => 'Client Secret', 'access_token' => 'Access Token', 'category_id' => 'Default Category ID'],
                                default        => [],
                            };
                        @endphp

                        <div class="space-y-4">
                            @foreach($fields as $field => $label)
                                <div>
                                    <x-input-label :value="$label" />
                                    <x-text-input
                                        type="{{ str_contains($field, 'secret') || str_contains($field, 'token') ? 'password' : 'text' }}"
                                        name="credentials[{{ $field }}]"
                                        class="mt-1 block w-full"
                                        placeholder="•••••• (unchanged)" />
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2 border-t">
                        <x-primary-button>Save Changes</x-primary-button>
                        <a href="{{ route('channels.show', $channel) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

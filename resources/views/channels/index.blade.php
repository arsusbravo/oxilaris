<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('ui.channels') }}</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="channels-app"
                 data-oauth-platforms="{{ json_encode(array_keys(array_filter([
                     'shopify'     => (bool) config('services.shopify.client_id'),
                     'woocommerce' => (bool) config('services.woocommerce.app_name'),
                     'tiktok_shop' => (bool) config('services.tiktok_shop.app_key'),
                     'shopee'      => (bool) config('services.shopee.partner_id'),
                     'gofood'      => (bool) config('services.gofood.api_key'),
                     'grabfood'    => (bool) config('services.grabfood.client_id'),
                 ]))) }}">
            </div>
        </div>
    </div>
</x-app-layout>

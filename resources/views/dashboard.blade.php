<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="dashboard-app"
                 data-user="{{ auth()->id() }}"
                 data-marketplaces="{{ json_encode($activeMarketplaces) }}"
                 data-ad-channels="{{ json_encode($activeAdChannels) }}"></div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $store->name }}</h2>
            <a href="{{ route('stores.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded p-3 text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="text-sm text-gray-400">Platform: <span class="text-gray-700 capitalize">{{ $store->channelIntegration ? str_replace('_', ' ', $store->channelIntegration->channel_type) : 'Manual' }}</span></div>
                        @if($store->url)
                            <div class="text-sm text-gray-400">URL: <a href="{{ $store->url }}" target="_blank" class="text-indigo-600 hover:underline">{{ $store->url }}</a></div>
                        @endif
                        <div class="text-sm text-gray-400">
                            Last synced: {{ $store->last_synced_at ? $store->last_synced_at->diffForHumans() : 'Never' }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if(!$store->isManual())
                        <form method="POST" action="{{ route('stores.sync', $store) }}">
                            @csrf
                            <button class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                Sync Products Now
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('stores.edit', $store) }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">Edit</a>
                        <form method="POST" action="{{ route('stores.destroy', $store) }}" onsubmit="return confirm('Delete this store and all its products?')">
                            @csrf @method('DELETE')
                            <button class="px-4 py-2 bg-red-50 text-red-600 text-sm rounded hover:bg-red-100">Delete</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $productCount }} product{{ $productCount !== 1 ? 's' : '' }} in this store</p>
                    <p class="text-xs text-gray-400 mt-0.5">View and manage all products from the Products page.</p>
                </div>
                <a href="{{ route('products.index', ['store_id' => $store->id]) }}"
                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium border border-indigo-200 px-4 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
                    View Products →
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

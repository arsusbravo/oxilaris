<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Store</h2>
            <a href="{{ route('stores.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6"
                 x-data="{ manual: {{ old('channel_integration_id') === '' || old('channel_integration_id') === null ? 'true' : 'false' }} }">

                <form method="POST" action="{{ route('stores.store') }}">
                    @csrf

                    {{-- Store type toggle --}}
                    <div class="mb-5">
                        <x-input-label value="Store type" />
                        <div class="mt-2 flex gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="_store_type" value="manual" x-model="manual" :value="true"
                                    class="text-indigo-600" {{ old('channel_integration_id', '__manual__') === '__manual__' ? 'checked' : '' }} />
                                <span class="text-sm font-medium text-gray-700">Manual store</span>
                                <span class="text-xs text-gray-400">(no external connection)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="_store_type" value="connected" x-model="manual" :value="false"
                                    class="text-indigo-600" />
                                <span class="text-sm font-medium text-gray-700">Connected store</span>
                                <span class="text-xs text-gray-400">(WooCommerce, Shopify…)</span>
                            </label>
                        </div>
                    </div>

                    {{-- Channel connection (only for connected stores) --}}
                    <div x-show="!manual" class="mb-5">
                        @if($integrations->isEmpty())
                            <p class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded p-3">
                                No active store channels found.
                                <a href="{{ route('channels.create') }}" class="underline">Connect a channel</a> first.
                            </p>
                        @else
                            <x-input-label for="channel_integration_id" value="Channel connection" />
                            <select id="channel_integration_id" name="channel_integration_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">— Select —</option>
                                @foreach($integrations as $integration)
                                    <option value="{{ $integration->id }}" {{ old('channel_integration_id') == $integration->id ? 'selected' : '' }}>
                                        {{ $integration->name }} ({{ str_replace('_', ' ', $integration->channel_type) }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('channel_integration_id')" class="mt-1" />
                        @endif
                    </div>

                    <div class="mb-5">
                        <x-input-label for="name" value="Store name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            value="{{ old('name') }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div class="mb-5">
                        <x-input-label for="url" value="Store URL (optional)" />
                        <x-text-input id="url" name="url" type="url" class="mt-1 block w-full"
                            value="{{ old('url') }}" placeholder="https://mystore.com" />
                        <x-input-error :messages="$errors->get('url')" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-3 pt-2 border-t">
                        <x-primary-button>Add Store</x-primary-button>
                        <a href="{{ route('stores.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

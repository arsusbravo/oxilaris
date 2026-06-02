<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $channel->name }}</h2>
            <a href="{{ route('channels.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded p-3 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded p-3 text-sm">{{ session('error') }}</div>
            @endif

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('channels.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition-colors">
                    + Tambah channel lain
                </a>
                <a href="{{ route('listings.index') }}"
                   class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded hover:bg-slate-50 transition-colors">
                    Ke halaman Listings →
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-sm text-gray-400 uppercase tracking-wide">Channel type</div>
                        <div class="font-medium text-gray-800 mt-0.5 capitalize">{{ str_replace('_', ' ', $channel->channel_type) }}</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        {{ $channel->status === 'active' ? 'bg-green-100 text-green-700' : ($channel->status === 'error' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                        {{ $channel->status }}
                    </span>
                </div>

                @if($channel->last_used_at)
                    <p class="text-sm text-gray-400">Last used: {{ $channel->last_used_at->diffForHumans() }}</p>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('channels.connect', $channel) }}"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                        {{ $channel->status === 'active' ? 'Re-test Connection' : 'Connect' }}
                    </a>
                    <a href="{{ route('channels.edit', $channel) }}"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                        Edit Credentials
                    </a>
                    @if($channel->isStore())
                        <a href="{{ route('stores.index') }}"
                            class="px-4 py-2 bg-indigo-50 text-indigo-700 text-sm rounded hover:bg-indigo-100">
                            Manage Stores →
                        </a>
                    @endif
                    <form method="POST" action="{{ route('channels.destroy', $channel) }}"
                        onsubmit="return confirm('Remove this channel? This will also delete all linked stores and listings.')">
                        @csrf @method('DELETE')
                        <button class="px-4 py-2 bg-red-50 text-red-600 text-sm rounded hover:bg-red-100">Remove</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

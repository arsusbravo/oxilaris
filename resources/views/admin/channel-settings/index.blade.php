<x-admin-layout title="Channel Settings">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded p-3 text-sm mb-4">{{ session('success') }}</div>
    @endif

    <p class="text-sm text-gray-500 mb-5">
        Disabled channel types are hidden from the channel creation dropdown for all users.
        Existing connections are not affected.
    </p>

    <div class="space-y-4">
        @foreach($groups as $groupName => $types)
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="px-5 py-3 border-b bg-gray-50 flex items-center gap-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">{{ $groupName }}</span>
            </div>
            <table class="min-w-full divide-y divide-gray-100">
                <tbody class="divide-y divide-gray-100">
                    @foreach($types as $type)
                    @php $setting = $settings->get($type); $active = $setting?->is_active ?? true; @endphp
                    <tr class="{{ ! $active ? 'bg-gray-50' : '' }}">
                        <td class="px-5 py-3.5 flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full shrink-0 {{ $active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ \App\Services\Channels\ChannelManager::TYPES[$type] ?? $type }}
                            </span>
                            <span class="text-[11px] font-mono text-gray-400">{{ $type }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($active)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Enabled</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Disabled</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <form method="POST" action="{{ route('admin.channel-settings.toggle', $type) }}">
                                @csrf
                                <button type="submit"
                                    class="text-sm font-medium {{ $active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}"
                                    onclick="return confirm('{{ $active ? 'Disable' : 'Enable' }} {{ \App\Services\Channels\ChannelManager::TYPES[$type] ?? $type }}?')">
                                    {{ $active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</x-admin-layout>

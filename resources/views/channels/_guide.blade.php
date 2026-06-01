@php
$colors = [
    'indigo' => ['bg'=>'bg-indigo-50','border'=>'border-indigo-200','dot'=>'bg-indigo-500','stepBg'=>'bg-indigo-200','text'=>'text-indigo-700','title'=>'text-indigo-800','link'=>'text-indigo-600 hover:text-indigo-800'],
    'violet' => ['bg'=>'bg-violet-50','border'=>'border-violet-200','dot'=>'bg-violet-500','stepBg'=>'bg-violet-200','text'=>'text-violet-700','title'=>'text-violet-800','link'=>'text-violet-600 hover:text-violet-800'],
    'amber'  => ['bg'=>'bg-amber-50','border'=>'border-amber-200','dot'=>'bg-amber-500','stepBg'=>'bg-amber-200','text'=>'text-amber-700','title'=>'text-amber-800','link'=>'text-amber-600 hover:text-amber-800'],
    'rose'   => ['bg'=>'bg-rose-50','border'=>'border-rose-200','dot'=>'bg-rose-500','stepBg'=>'bg-rose-200','text'=>'text-rose-700','title'=>'text-rose-800','link'=>'text-rose-600 hover:text-rose-800'],
    'orange' => ['bg'=>'bg-orange-50','border'=>'border-orange-200','dot'=>'bg-orange-500','stepBg'=>'bg-orange-200','text'=>'text-orange-700','title'=>'text-orange-800','link'=>'text-orange-600 hover:text-orange-800'],
    'sky'    => ['bg'=>'bg-sky-50','border'=>'border-sky-200','dot'=>'bg-sky-500','stepBg'=>'bg-sky-200','text'=>'text-sky-700','title'=>'text-sky-800','link'=>'text-sky-600 hover:text-sky-800'],
    'blue'   => ['bg'=>'bg-blue-50','border'=>'border-blue-200','dot'=>'bg-blue-600','stepBg'=>'bg-blue-200','text'=>'text-blue-700','title'=>'text-blue-800','link'=>'text-blue-600 hover:text-blue-800'],
    'emerald'=> ['bg'=>'bg-emerald-50','border'=>'border-emerald-200','dot'=>'bg-emerald-500','stepBg'=>'bg-emerald-200','text'=>'text-emerald-700','title'=>'text-emerald-800','link'=>'text-emerald-600 hover:text-emerald-800'],
];
$c = $colors[$color ?? 'indigo'];
@endphp
<div class="rounded-xl {{ $c['bg'] }} {{ $c['border'] }} border p-4">
    <div class="flex gap-3 items-start">
        <div class="w-8 h-8 rounded-full {{ $c['dot'] }} flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold {{ $c['title'] }} text-sm mb-2">{{ $title }}</p>
            <ol class="space-y-1.5">
                @foreach($steps as $i => $key)
                <li class="flex items-start gap-2.5 text-sm {{ $c['text'] }}">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full {{ $c['stepBg'] }} {{ $c['text'] }} text-xs font-bold flex items-center justify-center mt-0.5">{{ $i+1 }}</span>
                    <span>{{ __('ui.' . $key) }}</span>
                </li>
                @endforeach
            </ol>
            @if(!empty($url))
            <a href="{{ $url }}" target="_blank" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold {{ $c['link'] }}">
                {{ $urlLabel ?? $url }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
            @endif
        </div>
    </div>
</div>

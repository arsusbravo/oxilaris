@props(['variant' => 'dark', 'size' => 'md'])

@php
$larisColor = $variant === 'dark' ? 'text-white' : 'text-slate-900';
$iconSizes = ['sm' => 'h-7 w-7', 'md' => 'h-9 w-9', 'lg' => 'h-12 w-12', 'xl' => 'h-14 w-14'];
$textSizes = ['sm' => 'text-base', 'md' => 'text-lg', 'lg' => 'text-2xl', 'xl' => 'text-3xl'];
$iconSize = $iconSizes[$size] ?? 'h-9 w-9';
$textSize = $textSizes[$size] ?? 'text-lg';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <img src="/images/oxilaris-icon.png" alt="OXIlaris" class="{{ $iconSize }} object-contain {{ $variant === 'dark' ? 'rounded-md bg-white p-0.5' : '' }}">
    <span class="font-extrabold {{ $textSize }} tracking-tight leading-none select-none">
        <span style="color: #C0391A;">OXI</span><span class="{{ $larisColor }}">Laris</span>
    </span>
</div>

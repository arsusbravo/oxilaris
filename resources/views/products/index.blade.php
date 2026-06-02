<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('ui.products') }}</h2>
            <div class="flex w-full sm:w-auto items-center gap-2">
                <a href="{{ route('products.export') }}"
                   class="hidden sm:flex text-sm text-slate-500 hover:text-slate-700 font-medium items-center gap-1.5 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('ui.export_csv') }}
                </a>
                <a href="{{ route('products.create') }}"
                   class="flex-1 sm:flex-none bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium transition-colors text-center">
                    {{ __('ui.add_product') }}
                </a>
            </div>
        </div>
    </x-slot>
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div id="products-app"></div>
        </div>
    </div>
</x-app-layout>

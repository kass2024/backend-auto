<x-filament-widgets::widget>
    <x-filament::section class="neamee-list-header !p-0 overflow-hidden">
        <div class="border-b border-gray-700/80 bg-gray-900/60 px-5 py-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <img
                        src="{{ asset(config('neamee.logo')) }}"
                        alt="{{ config('neamee.company_name') }}"
                        class="h-20 w-20 shrink-0 rounded-full object-cover ring-2 ring-primary-600/50"
                        onerror="this.style.display='none'"
                    >
                    <div>
                        <p class="text-base font-bold text-white tracking-wide">{{ config('neamee.company_name') }}</p>
                        <p class="text-sm text-gray-300">{{ config('neamee.address_line1') }}</p>
                        <p class="text-sm text-gray-300">{{ config('neamee.address_line2') }}</p>
                        <p class="text-sm text-gray-300">{{ config('neamee.phone') }}</p>
                    </div>
                </div>
                <a
                    href="{{ route('filament.admin.list.print', ['key' => $printKey]) }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center justify-center gap-2 self-start rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-primary-500 transition"
                >
                    <x-filament::icon icon="heroicon-o-printer" class="h-4 w-4" />
                    Print / Save PDF
                </a>
            </div>
        </div>
        <div class="px-5 py-3 text-center">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-primary-400">
                {{ $documentTitle }}
            </p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

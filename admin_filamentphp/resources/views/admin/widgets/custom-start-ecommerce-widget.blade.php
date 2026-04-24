<x-filament-widgets::widget>
    <x-filament::section>
        <div class=" grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="!bg-primary/10">
                <x-filament::card class=" text-white">
                    <div class="flex  justify-between items-center">
                        <div>
                            <p class="text-xl font-bold">{{ app(\App\Contracts\Services\CurrencyServiceInterface::class)->format($totalSales) }}</p>
                            <p class="text-sm">{{ __('Total Sales') }}</p>
                        </div>
                        <x-filament::icon icon="heroicon-o-shopping-cart" class="w-8 h-8" />
                    </div>
                </x-filament::card>
            </div>

            <x-filament::card class=" text-white">
                <div class="bg-green-400 flex justify-between items-center">
                    <div>
                        <p class="text-xl font-bold">{{ app(\App\Contracts\Services\CurrencyServiceInterface::class)->formatNumber($todayOrders) }}</p>
                        <p class="text-sm">{{ __('Today Orders') }}</p>
                    </div>
                    <x-filament::icon icon="heroicon-m-arrow-trending-up" class="w-8 h-8" />
                </div>
            </x-filament::card>

            <x-filament::card class="bg-yellow-400 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xl font-bold">{{ app(\App\Contracts\Services\CurrencyServiceInterface::class)->formatNumber($completedOrders) }}</p>
                        <p class="text-sm">{{ __('Processing Orders') }}</p>
                    </div>
                    <x-filament::icon icon="heroicon-o-shopping-cart" class="w-8 h-8" />
                </div>
            </x-filament::card>

            <x-filament::card class="bg-red-400 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xl font-bold">{{ app(\App\Contracts\Services\CurrencyServiceInterface::class)->formatNumber($pendingOrders) }}</p>
                        <p class="text-sm">{{ __('Delivered Orders') }}</p>
                    </div>
                    <x-filament::icon icon="heroicon-o-clock" class="w-8 h-8" />
                </div>
            </x-filament::card>
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
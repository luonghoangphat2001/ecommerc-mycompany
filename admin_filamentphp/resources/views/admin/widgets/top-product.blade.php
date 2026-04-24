<x-filament::widget>
    <x-filament::card>

        <div class="rounded-md bg-card text-card-foreground">
            <div class="flex space-y-1.5 border-b border-border flex-row justify-between items-center gap-4 mb-0 border-none pb-6">
                <h3 class="text-xl font-medium leading-none">
                    {{ $heading }}
                </h3>

            </div>
            <div class="p-6 pt-0">
                <div class="pt-16">
                    <!-- Top 3 Customers -->
                    <div class="sesion_topcustomer grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                        @foreach ($topThree ?? [] as $index => $product)

                        <div class="order-{{( $index == 0 ?  2 : ($index == 2 ? $index + 1 : $index ))}} {{ $index == 0 ? 'md:-mt-8' : ''}}">
                            <div class="{{ ($index == 0 ? 'bg-primary/10' : 'bg-success/10'  ) }}  relative p-6 pt-12 rounded">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 ">
                                    <div class="relative inline-block ring ring-yellow-400 rounded-full">
                                        @if($index == 0 )
                                        <span class="absolute -top-[29px] left-1/2 -translate-x-1/2  ">
                                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="h-10 w-10 text-yellow-400 iconify iconify--ph" width="1em" height="1em" viewBox="0 0 256 256">
                                                <path fill="currentColor" d="M239.75 90.81c0 .11 0 .21-.07.32L217 195a16 16 0 0 1-15.72 13H54.71A16 16 0 0 1 39 195L16.32 91.13c0-.11-.05-.21-.07-.32A16 16 0 0 1 44 77.39l33.67 36.29l35.8-80.29a1 1 0 0 0 0-.1a16 16 0 0 1 29.06 0a1 1 0 0 0 0 .1l35.8 80.29L212 77.39a16 16 0 0 1 27.71 13.42Z"></path>
                                            </svg>
                                        </span>
                                        @endif
                                        <span class="relative flex shrink-0 overflow-hidden rounded-full h-16 w-16">
                                            <img class="aspect-square h-full w-full" src="{{ asset( '/storage/'. $product->image_url ) }}">
                                        </span>
                                        <div class="inline-flex rounded-full border transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent text-primary-foreground w-[18px] h-[18px] text-[10px] font-semibold p-0 items-center justify-center absolute left-[calc(100%-14px)] top-[calc(100%-20px)] bg-yellow-400">
                                            {{$index +1}}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center gap-2 ">
                                    <div class="text-base font-semibold text-default-900 mb-1 whitespace-nowrap">
                                        {{ $product->title }}
                                    </div>
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent text-primary-foreground bg-primary/80">
                                        @if ($currency == 'đ')
                                        {{ number_format($product->total_revenue, 0, ',', '.') }} {{ $currency }}
                                        @else
                                        {{ $currency }} {{ number_format($product->total_revenue, 0, ',', '.') }}
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Other Customers -->

                    <div class="mt-8 ">
                        @foreach ($otherProducts ?? [] as $index => $product)

                        <div class="flex flex-col sm:flex-row flex-wrap gap-7 sm:gap-4 w-full p-2 px-4  hover:bg-default-50 rounded-lg">
                            <div class="flex-none flex flex-wrap items-center gap-3">
                                <div class="relative inline-block">
                                    <span class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full">
                                        <img class="aspect-square h-full w-full" src="{{ asset( '/storage/'. $product->image_url ) }}">
                                    </span>
                                    <div class="inline-flex rounded-full border transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent text-primary-foreground w-[18px] h-[18px] text-[10px] font-semibold p-0 items-center justify-center absolute left-[calc(100%-14px)] top-[calc(100%-16px)] bg-yellow-400">
                                        {{ $index + 1 }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-default-800 mb-1 whitespace-nowrap">
                                        {{ $product->title }}
                                    </div>
                                    <div class="text-xs text-default-600 whitespace-nowrap">

                                    </div>
                                </div>
                            </div>
                            <div class="flex-1 flex items-center sm:justify-center">
                                <div>
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-primary bg-opacity-10 text-primary hover:text-primary">
                                        @if ($currency == 'đ')
                                        {{ number_format($product->total_revenue, 0, ',', '.') }} {{ $currency }}
                                        @else
                                        {{ $currency }} {{ number_format($product->total_revenue, 0, ',', '.') }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </x-filament::card>
</x-filament::widget>
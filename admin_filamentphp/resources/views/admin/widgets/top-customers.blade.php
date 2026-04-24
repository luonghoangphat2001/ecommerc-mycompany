<x-filament::widget>
    @php
    $getAvatarUrl = function($customer, $index) {
    $avatarPath = 'images/avatars/customer' . ($index) . '.jpg';
    if (file_exists(public_path($avatarPath))) {
    return asset($avatarPath);
    }

    if ($customer->photo) {
    if (str_starts_with($customer->photo, 'http')) {
    return $customer->photo;
    }
    return asset('storage/' . $customer->photo);
    }

    return asset('images/default-avatar.jpg');
    };
    @endphp

    <x-filament::card class="relative overflow-hidden border-none shadow-2xl bg-white/50 dark:bg-gray-900/50 backdrop-blur-xl">
        {{-- Decorative Background Elements --}}
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-secondary-500/10 rounded-full blur-3xl"></div>

        <div class="relative z-10">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="p-2 rounded-lg bg-primary-500/10">
                        <x-filament::icon icon="heroicon-o-users" class="w-5 h-5 text-primary-600" />
                    </span>
                    {{ $heading }}
                </h3>
            </div>

            <div class="space-y-8">
                <!-- Triangle/Podium Layout -->
                <div class="relative flex flex-row justify-center items-end gap-2 md:gap-4 pt-16 pb-2">
                    @foreach ($topThree ?? [] as $index => $customer)
                    @php
                    $isFirst = $index === 0;
                    $isSecond = $index === 1;
                    $isThird = $index === 2;

                    $order = $isFirst ? 'order-2' : ($isSecond ? 'order-1' : 'order-3');
                    $height = $isFirst ? 'h-36 md:h-44' : ($isSecond ? 'h-24 md:h-32' : 'h-16 md:h-24');
                    $avatarSize = $isFirst ? 'w-20 h-20 md:w-28 md:h-28' : ($isSecond ? 'w-16 h-16 md:w-24 md:h-24' : 'w-12 h-12 md:w-20 md:h-20');
                    $gradient = $isFirst ? 'from-yellow-400 to-amber-600' : ($isSecond ? 'from-slate-300 to-slate-500' : 'from-orange-400 to-orange-700');

                    $avatarUrl = $getAvatarUrl($customer, $index + 1);
                    @endphp

                    <div class="flex flex-col items-center flex-1 min-w-0 transition-all duration-500 hover:-translate-y-2 group {{ $order }}">
                        {{-- Avatar --}}
                        <div class="relative mb-4">
                            <div class="relative p-1 rounded-full bg-gradient-to-tr {{ $gradient }} shadow-2xl transition-transform duration-500 group-hover:scale-110">
                                <div class="p-0.5 rounded-full bg-white dark:bg-gray-900">
                                    <img class="{{ $avatarSize }} rounded-full object-cover"
                                        src="{{ $avatarUrl }}"
                                        alt="{{ $customer->name }}">
                                </div>
                            </div>
                            {{-- Crown/Icon --}}
                            @if($isFirst)
                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 animate-bounce">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 md:w-12 md:h-12 text-yellow-500 drop-shadow-xl" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M2 19h20v2H2v-2zM12 5l-4 4-4-4 1 10h14l1-10-4 4-4-4z" />
                                </svg>
                            </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="text-center mb-4 w-full px-1">
                            <p class="text-xs md:text-sm font-black text-gray-900 dark:text-white truncate">
                                {{ $customer->name }}
                            </p>
                            <p class="text-[10px] md:text-xs font-black text-primary-600 bg-primary-500/5 px-2 py-0.5 rounded-full inline-block">
                                {{ app(\App\Contracts\Services\CurrencyServiceInterface::class)->format($customer->total_spent) }}
                            </p>
                        </div>

                        {{-- Pillar --}}
                        <div class="w-full {{ $height }} rounded-t-3xl bg-white/60 dark:bg-gray-800/60 backdrop-blur-md border-x border-t border-white/40 dark:border-gray-700/40 shadow-2xl relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-transparent to-white/10 dark:to-white/5"></div>
                            <div class="flex items-center justify-center h-full">
                                <span class="text-3xl md:text-5xl font-black text-gray-900/10 dark:text-white/10">{{ $index + 1 }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- List: Other Customers -->
                <div class="space-y-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                    @foreach ($otherCustomers ?? [] as $index => $customer)
                    @php
                    $rank = $index + 1;
                    $avatarUrl = $getAvatarUrl($customer, $rank);
                    @endphp
                    <div class="flex items-center gap-4 p-3 rounded-2xl transition-all duration-300 hover:bg-white dark:hover:bg-gray-800 hover:shadow-xl group border border-transparent hover:border-gray-100 dark:hover:border-gray-700">
                        <span class="text-xs font-black text-gray-400 group-hover:text-primary-500 transition-colors w-8">#{{ $rank }}</span>

                        <div class="relative">
                            <img class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-800 group-hover:ring-primary-500/30 transition-all"
                                src="{{ $avatarUrl }}"
                                alt="{{ $customer->name }}">
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $customer->name }}</p>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate opacity-0 group-hover:opacity-100 transition-all">{{ $customer->email }}</p>
                        </div>

                        <div class="text-right">
                            <p class="text-sm font-black text-primary-600">
                                {{ app(\App\Contracts\Services\CurrencyServiceInterface::class)->format($customer->total_spent) }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
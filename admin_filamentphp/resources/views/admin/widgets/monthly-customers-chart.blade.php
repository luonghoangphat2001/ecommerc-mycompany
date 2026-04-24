<div>
    <x-filament-apex-charts::chart
        :options="$chartOptions" />

    <div class="flex justify-between mt-4 text-gray-600">
        <div>
            <span class="font-bold">Tháng này:</span> {{ $currentMonth }}
        </div>
        <div>
            <span class="font-bold">Tháng trước:</span> {{ $previousMonth }}
        </div>
    </div>
</div>
<x-filament::page>
    <x-filament-panels::form wire:submit.prevent="save">
        {{$this->form}}
        <div class="flex justify-end mt-4">
            <x-filament::button type="submit">
                Lưu
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament::page>
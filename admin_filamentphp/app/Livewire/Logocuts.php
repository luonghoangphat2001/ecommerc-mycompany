<?php

namespace App\Livewire;

use Livewire\Component;
use Awcodes\Curator\Models\Media;
use Illuminate\Support\Facades\Storage;
use App\Settings\DBSettings;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class Logocuts extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function render()
    {
        $logo = app(DBSettings::class)->logo
            ? Media::find(app(DBSettings::class)->logo)?->url // Lấy URL từ Curaso
            : null;

        return view('admin.livewire.logocuts', compact('logo'));
    }
}

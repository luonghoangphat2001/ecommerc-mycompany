<?php

use App\Livewire\Form;

\Illuminate\Support\Facades\Route::get('form', Form::class);

Route::get('/docs/webhook', function () {
    return view('docs.webhook');
})->name('docs.webhook');

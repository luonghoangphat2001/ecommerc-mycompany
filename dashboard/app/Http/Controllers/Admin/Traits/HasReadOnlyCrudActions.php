<?php

namespace App\Http\Controllers\Admin\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait HasReadOnlyCrudActions
{
    public function create(): never
    {
        throw new NotFoundHttpException;
    }

    public function store(Request $request): RedirectResponse
    {
        abort(404);
    }

    public function edit(int $id): never
    {
        throw new NotFoundHttpException;
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort(404);
    }

    public function destroy(int $id): RedirectResponse
    {
        abort(404);
    }
}

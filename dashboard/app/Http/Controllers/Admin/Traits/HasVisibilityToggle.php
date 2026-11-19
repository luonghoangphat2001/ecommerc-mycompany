<?php

namespace App\Http\Controllers\Admin\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait HasVisibilityToggle
{
    public function toggleStatus(Request $request, int $id): JsonResponse | RedirectResponse
    {
        $modelClass = $this->modelClass();
        $record = $modelClass::query()->findOrFail($id);
        $record->is_visible = $request->has('is_visible')
            ? $request->boolean('is_visible')
            : ! $record->is_visible;
        $record->save();

        $this->flushStorefrontCaches();

        $entity = $this->visibilityEntityLabel();
        $message = $record->is_visible
            ? 'Đã bật hiển thị ' . $entity . ' "' . $record->display_name . '"'
            : 'Đã tạm ẩn ' . $entity . ' "' . $record->display_name . '"';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_visible' => (bool) $record->is_visible,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }

    protected function visibilityEntityLabel(): string
    {
        return 'danh mục';
    }
}

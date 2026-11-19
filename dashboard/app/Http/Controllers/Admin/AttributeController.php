<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Product\Services\ProductAttributeService;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Requests\Admin\InlineAttributeRequest;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends BaseCrudController
{
    public function __construct(private readonly ProductAttributeService $attributeService)
    {
        parent::__construct();
    }

    protected function modelClass(): string
    {
        return Attribute::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.attributes';
    }

    protected function routePrefix(): string
    {
        return 'admin.attributes';
    }

    protected function searchable(): array
    {
        return ['name', 'slug', 'type'];
    }

    protected function filters(): array
    {
        return [
            'type' => [
                'label' => 'Kiểu hiển thị',
                'placeholder' => '-- Tất cả kiểu --',
                'options' => [
                    'button' => 'Nút bấm chọn (Button)',
                    'select' => 'Menu thả xuống (Select)',
                    'color' => 'Màu sắc (Color)',
                ],
            ],
            'is_enabled' => [
                'label' => 'Trạng thái',
                'placeholder' => '-- Tất cả trạng thái --',
                'options' => [
                    '1' => 'Đang kích hoạt',
                    '0' => 'Đã tạm tắt',
                ],
            ],
        ];
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        $query->withCount('productAttributes');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('is_enabled')) {
            $query->where('is_enabled', (bool) $request->input('is_enabled'));
        }

        return $query;
    }

    protected function fields(): array
    {
        return [
            'name' => [
                'label' => 'Tên thuộc tính',
                'rules' => ['required', 'string', 'max:255'],
            ],
            'slug' => [
                'label' => 'Mã định danh (Slug)',
                'rules' => ['nullable', 'string', 'max:255'],
            ],
            'type' => [
                'label' => 'Loại hiển thị',
                'type' => 'select',
                'rules' => ['required', 'in:select,button,color'],
                'options' => [
                    'button' => 'Nút bấm chọn (Button / Pill)',
                    'select' => 'Menu thả xuống (Dropdown Select)',
                    'color' => 'Màu sắc (Color Swatch)',
                ],
            ],
            'is_enabled' => [
                'label' => 'Trạng thái',
                'type' => 'select',
                'rules' => ['nullable', 'boolean'],
                'options' => [
                    '1' => 'Kích hoạt',
                    '0' => 'Tạm tắt',
                ],
            ],
            'values' => [
                'label' => 'Danh sách giá trị mẫu (cách nhau bởi dấu phẩy)',
                'type' => 'textarea',
                'rules' => ['nullable'],
                'help' => 'Ví dụ: S, M, L, XL hoặc Đen, Trắng, Đỏ. Khi tạo sản phẩm, các giá trị này sẽ hiển thị để chọn nhanh.',
            ],
            'product_attributes_count' => [
                'label' => 'Số sản phẩm sử dụng',
                'tableOnly' => true,
            ],
        ];
    }

    protected function formData(?Model $record = null): array
    {
        if ($record && is_array($record->values)) {
            return [
                'values' => implode(', ', $record->values),
                'is_enabled' => $record->is_enabled ? '1' : '0',
            ];
        }

        return [
            'type' => 'button',
            'is_enabled' => '1',
            'values' => '',
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data = parent::mutateData($data, $record);

        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (isset($data['is_enabled'])) {
            $data['is_enabled'] = (bool) $data['is_enabled'];
        }

        if (isset($data['values'])) {
            if (is_string($data['values'])) {
                $items = preg_split('/[\r\n,]+/', $data['values']);
                $cleaned = array_values(array_filter(array_map('trim', $items), fn ($v) => $v !== ''));
                $data['values'] = $cleaned;
            } elseif (! is_array($data['values'])) {
                $data['values'] = [];
            }
        }

        return $data;
    }

    /**
     * Quick toggle enabled/disabled status.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse | RedirectResponse
    {
        $attribute = $this->attributeService->findAttributeOrFail($id);
        $attribute = $this->attributeService->toggleAttribute(
            $attribute,
            $request->has('is_enabled') ? $request->boolean('is_enabled') : null
        );

        $message = $attribute->is_enabled
            ? 'Đã kích hoạt thuộc tính "' . $attribute->name . '"'
            : 'Đã tạm tắt thuộc tính "' . $attribute->name . '"';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_enabled' => (bool) $attribute->is_enabled,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('status', $message);
    }

    /**
     * AJAX endpoint for inline creation from product form.
     */
    public function inlineStore(InlineAttributeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $name = trim($validated['name']);
        $rawValues = $validated['values'] ?? [];
        if (is_string($rawValues)) {
            $rawValues = preg_split('/[\r\n,]+/', $rawValues);
        }
        $values = array_values(array_filter(array_map('trim', (array) $rawValues), fn ($v) => $v !== ''));

        $attribute = $this->attributeService->findOrCreateAttribute(
            $name,
            null,
            $values,
            $validated['type'] ?? 'button'
        );

        return response()->json([
            'success' => true,
            'attribute' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'slug' => $attribute->slug,
                'type' => $attribute->type,
                'values' => is_array($attribute->values) ? array_values($attribute->values) : [],
            ],
            'message' => 'Đã lưu thuộc tính toàn cục thành công.',
        ]);
    }

    /**
     * AJAX endpoint returning active global attributes list.
     */
    public function listJson(): JsonResponse
    {
        $attributes = $this->attributeService->getEnabledAttributes();

        return response()->json([
            'success' => true,
            'data' => $attributes,
        ]);
    }
}

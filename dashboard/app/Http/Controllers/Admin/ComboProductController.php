<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\ComboProduct;
use App\Models\ComboProductItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComboProductController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return ComboProduct::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.combo_products';
    }

    protected function routePrefix(): string
    {
        return 'admin.combo-products';
    }

    protected function formView(): string
    {
        return 'admin.combo-products.form';
    }

    protected function searchable(): array
    {
        return ['name', 'slug'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Tên combo', 'rules' => ['required', 'string', 'max:255']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']],
            'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'combo_price' => ['label' => 'Combo price', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
            'discount_percent' => ['label' => 'Discount %', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:100']],
            'is_active' => ['label' => 'Kích hoạt', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
            'start_date' => ['label' => 'Start date', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']],
            'end_date' => ['label' => 'End date', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']],
            'items_json' => ['label' => 'Sản phẩm thành phần', 'type' => 'hidden', 'rules' => ['nullable'], 'virtual' => true, 'formOnly' => true],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        unset($data['items_json']);

        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['name']);
        }

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        $raw = $request->input('items_json');
        $items = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($items)) {
            return;
        }

        $record->items()->delete();

        foreach ($items as $item) {
            if (! isset($item['shop_product_id']) || empty($item['shop_product_id'])) {
                continue;
            }

            ComboProductItem::create([
                'combo_product_id' => $record->id,
                'shop_product_id' => (int) $item['shop_product_id'],
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'sort_order' => (int) ($item['sort_order'] ?? 0),
            ]);
        }
    }

    protected function formData(?Model $record = null): array
    {
        $products = Product::select('id', 'name', 'sku')->orderBy('name')->get();
        $items = $record ? $record->items()->get(['shop_product_id', 'quantity', 'sort_order'])->toArray() : [];

        if (empty($items) && $products->isNotEmpty()) {
            $items[] = [
                'shop_product_id' => $products->first()->id,
                'quantity' => 1,
                'sort_order' => 0,
            ];
        }

        return [
            'products' => $products,
            'initialItems' => $items,
            'items_json' => json_encode($items, JSON_UNESCAPED_UNICODE),
        ];
    }
}

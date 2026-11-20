<?php

namespace App\Http\Resources\Api;

use App\Support\Media;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;


#[OAT\Schema(
    schema: 'OrderItemResource',
    title: 'Order Item',
    description: 'Chi tiết sản phẩm trong đơn hàng',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'product_id', type: 'integer', example: 1),
        new OAT\Property(property: 'product', type: 'object', properties: [
            new OAT\Property(property: 'id', type: 'integer', nullable: true, example: 1),
            new OAT\Property(property: 'name', type: 'string', nullable: true, example: 'Sản phẩm 1'),
            new OAT\Property(property: 'sku', type: 'string', nullable: true),
            new OAT\Property(property: 'variant_attributes', type: 'object', nullable: true),
            new OAT\Property(property: 'image', type: 'object', nullable: true, properties: [
                new OAT\Property(property: 'url', type: 'string', example: 'https://example.com/image.jpg')
            ]),
        ]),
        new OAT\Property(property: 'qty', type: 'integer', example: 2),
        new OAT\Property(property: 'unit_price', type: 'number', format: 'float', example: 500000),
        new OAT\Property(property: 'total', type: 'number', format: 'float', example: 1000000),
        new OAT\Property(property: 'metadata', type: 'object', nullable: true),
    ]
)]
class OrderItemResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $imageUrl = null;

        if (! empty($this->metadata['image_url'])) {
            $imageUrl = $this->metadata['image_url'];
        } elseif ($product) {
            $imageUrl = Media::productImage($product);
        }

        return [
            'id' => $this->id,
            'product_id' => $this->shop_product_id,
            'product' => [
                'id' => $product?->id,
                'name' => $this->name ?: ($product ? $this->translateField($product, 'name') : null),
                'sku' => $this->sku,
                'variant_attributes' => $this->variant_attributes,
                'image' => $imageUrl ? [
                    'url' => $imageUrl,
                ] : null,
            ],
            'qty' => $this->qty,
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'metadata' => $this->metadata,
        ];
    }
}

<?php

namespace App\Http\Resources\Api;

use OpenApi\Attributes as OAT;


#[OAT\Schema(
    schema: "UpsellResource",
    type: "object",
    properties: [
        new OAT\Property(property: "id", type: "integer", example: 1),
        new OAT\Property(property: "sort_order", type: "integer", example: 0),
        new OAT\Property(property: "is_active", type: "boolean", example: true),
        new OAT\Property(property: "product", ref: "#/components/schemas/ProductResource")
    ]
)]
class UpsellResource extends RelatedProductResource
{
    protected function productRelation(): string
    {
        return 'upsellProduct';
    }
}

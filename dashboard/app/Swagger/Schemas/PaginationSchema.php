<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: "PaginationMeta",
    title: "Pagination Metadata",
    description: "Thông tin phân trang trả về từ API",
    properties: [
        new OAT\Property(property: "current_page", type: "integer", example: 1),
        new OAT\Property(property: "from", type: "integer", example: 1),
        new OAT\Property(property: "last_page", type: "integer", example: 5),
        new OAT\Property(
            property: "links",
            type: "array",
            items: new OAT\Items(
                properties: [
                    new OAT\Property(property: "url", type: "string", nullable: true, example: "http://localhost/api/resource?page=1"),
                    new OAT\Property(property: "label", type: "string", example: "1"),
                    new OAT\Property(property: "active", type: "boolean", example: true)
                ]
            )
        ),
        new OAT\Property(property: "path", type: "string", example: "http://localhost/api/resource"),
        new OAT\Property(property: "per_page", type: "integer", example: 15),
        new OAT\Property(property: "to", type: "integer", example: 15),
        new OAT\Property(property: "total", type: "integer", example: 75)
    ]
)]
#[OAT\Schema(
    schema: "PaginationLinks",
    title: "Pagination Links",
    description: "Các link phân trang",
    properties: [
        new OAT\Property(property: "first", type: "string", example: "http://localhost/api/resource?page=1"),
        new OAT\Property(property: "last", type: "string", example: "http://localhost/api/resource?page=5"),
        new OAT\Property(property: "prev", type: "string", nullable: true, example: null),
        new OAT\Property(property: "next", type: "string", nullable: true, example: "http://localhost/api/resource?page=2")
    ]
)]
class PaginationSchema
{
}

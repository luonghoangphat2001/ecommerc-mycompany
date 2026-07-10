<?php

namespace App\Swagger\Storefront;

use OpenApi\Attributes as OAT;

#[OAT\Info(
    version: "1.0.0",
    description: "Tài liệu API dành cho Storefront (Client/App)",
    title: "Storefront API",
)]
#[OAT\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "Primary API Server"
)]
#[OAT\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "bearerAuth",
    in: "header",
    bearerFormat: "JWT",
    scheme: "bearer"
)]
class StorefrontInfo
{
}

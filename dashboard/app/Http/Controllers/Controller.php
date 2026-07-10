<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use OpenApi\Attributes as OAT;

#[OAT\Info(
    version: "1.0.0",
    description: "Comprehensive API documentation for HP Platform, including Admin and Storefront APIs.\n\n" .
                 "## Authentication Guide\n\n" .
                 "This API uses **Bearer Token Authentication** (Laravel Sanctum).\n\n" .
                 "### Authentication Flow\n" .
                 "1. **Login**: Send a `POST` request to `/api/storefront/auth/login` with your `email`, `password`, and `device_name`.\n" .
                 "2. **Receive Token**: On success, the API returns a response containing the token.\n" .
                 "3. **Use Token**: Add the token to the `Authorization` header for subsequent requests (`Authorization: Bearer <your_token>`).\n\n" .
                 "### Example Login Response\n" .
                 "```json\n" .
                 "{\n" .
                 "  \"success\": true,\n" .
                 "  \"data\": {\n" .
                 "    \"access_token\": \"1|your_access_token_here\",\n" .
                 "    \"token_type\": \"Bearer\",\n" .
                 "    \"expires_in\": 3600,\n" .
                 "    \"user\": {\n" .
                 "      \"id\": 1,\n" .
                 "      \"name\": \"John Doe\",\n" .
                 "      \"email\": \"customer@example.com\"\n" .
                 "    }\n" .
                 "  },\n" .
                 "  \"message\": \"Login successful.\"\n" .
                 "}\n" .
                 "```\n\n" .
                 "### How to use Bearer Token in Swagger\n" .
                 "1. Click the **Authorize** button at the top of this page.\n" .
                 "2. Enter your token in the **Value** field (just the token, no need to type 'Bearer').\n" .
                 "3. Click **Authorize** and then **Close**.\n" .
                 "4. Now you can test all API endpoints that require authentication.\n\n" .
                 "### API Access Levels\n" .
                 "- **Protected APIs**: Marked with a lock icon (🔒). These require a valid Bearer Token.\n" .
                 "- **Public APIs**: Endpoints without the lock icon can be accessed freely.\n" .
                 "- **Roles & Permissions**: Access to specific APIs depends on the roles and permissions assigned to your user account (e.g., Customer, Admin). Accessing an unauthorized endpoint will result in a `403 Forbidden` response.\n\n" .
                 "### Logout & Token Revocation\n" .
                 "To revoke your current token, send a `POST` request to `/api/storefront/auth/logout`. This will invalidate the token.",
    title: "HP Platform API Documentation",
    contact: new OAT\Contact(email: "admin@example.com")
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
class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
}

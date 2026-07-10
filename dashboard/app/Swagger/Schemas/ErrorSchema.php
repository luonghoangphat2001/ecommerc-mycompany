<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: "ErrorResponse",
    title: "Standard Error Response",
    description: "Standard system error response",
    properties: [
        new OAT\Property(property: "message", type: "string", example: "Something went wrong")
    ]
)]
#[OAT\Schema(
    schema: "ValidationErrorResponse",
    title: "Validation Error Response",
    description: "Error when input data is invalid",
    properties: [
        new OAT\Property(property: "message", type: "string", example: "The given data was invalid."),
        new OAT\Property(
            property: "errors",
            type: "object",
            additionalProperties: new OAT\AdditionalProperties(
                type: "array",
                items: new OAT\Items(type: "string")
            ),
            example: [
                "email" => ["The email field is required.", "The email must be a valid email address."],
                "password" => ["The password must be at least 8 characters."]
            ]
        )
    ]
)]
#[OAT\Schema(
    schema: "UnauthorizedErrorResponse",
    title: "Unauthorized Error Response",
    description: "Authentication error (not logged in or invalid token)",
    properties: [
        new OAT\Property(property: "message", type: "string", example: "Unauthenticated.")
    ]
)]
#[OAT\Schema(
    schema: "ForbiddenErrorResponse",
    title: "Forbidden Error Response",
    description: "Authorization error (no permission to access this resource)",
    properties: [
        new OAT\Property(property: "message", type: "string", example: "This action is unauthorized.")
    ]
)]
#[OAT\Schema(
    schema: "NotFoundErrorResponse",
    title: "Not Found Error Response",
    description: "Requested resource not found",
    properties: [
        new OAT\Property(property: "message", type: "string", example: "No query results for model [App\\Models\\User] 1")
    ]
)]
#[OAT\Schema(
    schema: "ServerErrorResponse",
    title: "Server Error Response",
    description: "System server error",
    properties: [
        new OAT\Property(property: "message", type: "string", example: "Server error occurred.")
    ]
)]
class ErrorSchema
{
}

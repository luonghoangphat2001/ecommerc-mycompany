<?php

namespace App\Swagger\Attributes;

use OpenApi\Attributes as OAT;

#[\Attribute(\Attribute::TARGET_METHOD)]
class ApiPost extends OAT\Post
{
    public function __construct(
        string $path,
        string $summary,
        string $tags = 'Default',
        $requestBody = null,
        bool $requiresAuth = true,
        $responses = null,
        string|array $responseData = null
    ) {
        $security = $requiresAuth ? [['bearerAuth' => []]] : [];

        $dataProperty = null;
        if (is_string($responseData)) {
            $dataProperty = new OAT\Property(property: 'data', ref: $responseData);
        } elseif (is_array($responseData)) {
            $dataProperty = new OAT\Property(property: 'data', properties: $responseData, type: 'object');
        } else {
            $dataProperty = new OAT\Property(property: 'data', type: 'object');
        }

        $defaultResponses = [
            new OAT\Response(
                response: 201,
                description: 'Created successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'success', type: 'boolean', example: true),
                        $dataProperty,
                        new OAT\Property(property: 'message', type: 'string', example: 'Created successfully')
                    ]
                )
            ),
            new OAT\Response(response: 400, description: 'Validation Error', content: new OAT\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OAT\Response(response: 401, description: 'Unauthenticated', content: new OAT\JsonContent(ref: '#/components/schemas/UnauthorizedErrorResponse')),
            new OAT\Response(response: 403, description: 'Forbidden', content: new OAT\JsonContent(ref: '#/components/schemas/ForbiddenErrorResponse')),
            new OAT\Response(response: 500, description: 'Server Error', content: new OAT\JsonContent(ref: '#/components/schemas/ServerErrorResponse')),
        ];

        parent::__construct(
            path: $path,
            summary: $summary,
            security: $security,
            tags: [$tags],
            requestBody: $requestBody,
            responses: $responses ?? $defaultResponses
        );
    }
}

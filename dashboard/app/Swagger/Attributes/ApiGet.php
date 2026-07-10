<?php

namespace App\Swagger\Attributes;

use OpenApi\Attributes as OAT;

#[\Attribute(\Attribute::TARGET_METHOD)]
class ApiGet extends OAT\Get
{
    public function __construct(
        string $path,
        string $summary,
        string $tags = 'Default',
        bool $requiresAuth = true,
        $parameters = null,
        $responses = null,
        string|array $responseData = null
    ) {
        if (!str_starts_with($path, '/api/v1')) {
            $path = '/api/v1/' . ltrim($path, '/');
        }

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
                response: 200,
                description: 'Success',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'success', type: 'boolean', example: true),
                        $dataProperty,
                        new OAT\Property(property: 'message', type: 'string', example: 'Success')
                    ]
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated', content: new OAT\JsonContent(ref: '#/components/schemas/UnauthorizedErrorResponse')),
            new OAT\Response(response: 403, description: 'Forbidden', content: new OAT\JsonContent(ref: '#/components/schemas/ForbiddenErrorResponse')),
            new OAT\Response(response: 404, description: 'Not Found', content: new OAT\JsonContent(ref: '#/components/schemas/NotFoundErrorResponse')),
        ];

        parent::__construct(
            path: $path,
            summary: $summary,
            security: $security,
            tags: [$tags],
            parameters: $parameters ?? [],
            responses: $responses ?? $defaultResponses
        );
    }
}

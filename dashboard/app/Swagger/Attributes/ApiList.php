<?php

namespace App\Swagger\Attributes;

use OpenApi\Attributes as OAT;

#[\Attribute(\Attribute::TARGET_METHOD)]
class ApiList extends OAT\Get
{
    public function __construct(
        string $path,
        string $summary,
        string $tags = 'Default',
        bool $requiresAuth = true,
        $parameters = null,
        string|array $responseData = null
    ) {
        if (!str_starts_with($path, '/api/v1')) {
            $path = '/api/v1/' . ltrim($path, '/');
        }

        $security = $requiresAuth ? [['bearerAuth' => []]] : [];

        // Add default pagination parameters
        $paginationParams = [
            new OAT\Parameter(name: 'page', in: 'query', required: false, description: 'Page number (default: 1)', schema: new OAT\Schema(type: 'integer', default: 1)),
            new OAT\Parameter(name: 'per_page', in: 'query', required: false, description: 'Items per page', schema: new OAT\Schema(type: 'integer', default: 15)),
        ];

        $mergedParameters = $parameters ? array_merge($paginationParams, $parameters) : $paginationParams;

        $dataProperty = null;
        if (is_string($responseData)) {
            $dataProperty = new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(ref: $responseData));
        } elseif (is_array($responseData)) {
            $dataProperty = new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(properties: $responseData, type: 'object'));
        } else {
            $dataProperty = new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(type: 'object'));
        }

        parent::__construct(
            path: $path,
            summary: $summary,
            security: $security,
            tags: [$tags],
            parameters: $mergedParameters,
            responses: [
                new OAT\Response(
                    response: 200,
                    description: 'Paginated list of items',
                    content: new OAT\JsonContent(
                        properties: [
                            new OAT\Property(property: 'success', type: 'boolean', example: true),
                            $dataProperty,
                            new OAT\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
                            new OAT\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                            new OAT\Property(property: 'message', type: 'string', example: 'Success')
                        ]
                    )
                ),
                new OAT\Response(response: 401, description: 'Unauthenticated', content: new OAT\JsonContent(ref: '#/components/schemas/UnauthorizedErrorResponse')),
                new OAT\Response(response: 403, description: 'Forbidden', content: new OAT\JsonContent(ref: '#/components/schemas/ForbiddenErrorResponse')),
            ]
        );
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\ComboResource;
use App\Ecommerce\Combo\Contracts\ComboServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;

class ComboController extends BaseApiController
{

    protected ComboServiceInterface $comboService;

    public function __construct(ComboServiceInterface $comboService)
    {
        $this->comboService = $comboService;
    }

    #[ApiList(
        path: '/combos',
        summary: 'List of Combos',
        tags: 'Storefront - Combos',
        requiresAuth: false,
        responseData: '#/components/schemas/ComboResource'
    )]
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ]);

        $combos = $this->comboService->getActiveCombos();

        return $this->ok(ComboResource::collection($combos->paginate($validated['per_page'] ?? 15)));
    }

    #[ApiGet(
        path: '/combos/{slug}',
        summary: 'Combo Details',
        tags: 'Storefront - Combos',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'slug', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'summer-combo'), description: 'Combo Slug')
        ],
        responseData: '#/components/schemas/ComboResource'
    )]
    public function show(string $slug): JsonResponse
    {
        $combo = $this->comboService->getComboBySlug($slug);

        if (!$combo) {
            return $this->notFound(__('messages.api.combo_not_found'));
        }

        return $this->ok(new ComboResource($combo));
    }
}

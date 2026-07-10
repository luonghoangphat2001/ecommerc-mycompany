<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Api\MenuResource;
use App\Ecommerce\Menu\Services\MenuService;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiGet;
use OpenApi\Attributes as OAT;

class MenuController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    #[ApiList(
        path: '/menus',
        summary: 'List of Menus',
        tags: 'Storefront - Menus',
        requiresAuth: false,
        responseData: '#/components/schemas/MenuResource'
    )]
    public function index()
    {
        $menus = $this->menuService->getAllMenus();
        return MenuResource::collection($menus);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    #[ApiGet(
        path: '/menus/{slug}',
        summary: 'Menu Details (by Slug)',
        tags: 'Storefront - Menus',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'slug', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'main-menu'), description: 'Menu Slug')
        ],
        responseData: '#/components/schemas/MenuResource'
    )]
    public function show($slug)
    {
        $menu = $this->menuService->getMenuBySlug($slug);
        if (!$menu) {
            return response()->json(['success' => false, 'message' => 'Menu not found'], 404);
        }
        return new MenuResource($menu);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

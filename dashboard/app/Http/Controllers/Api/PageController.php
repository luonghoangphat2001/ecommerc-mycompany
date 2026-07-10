<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Page\StorePageRequest;
use App\Http\Requests\API\Page\UpdatePageRequest;
use App\Http\Resources\Api\PageResource;
use App\Ecommerce\Page\Contracts\PageServiceInterface;
use App\Ecommerce\Page\DTOs\Page\PageDTO;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class PageController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PageServiceInterface $pageService
    ) {}

    #[ApiList(
        path: '/api/storefront/v1/pages',
        summary: 'List of Pages',
        tags: 'Storefront - Pages',
        requiresAuth: false
    )]
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $pages = $this->pageService->getPaginatedPages($perPage);
        return $this->ok(PageResource::collection($pages));
    }

    #[ApiPost(
        path: '/api/storefront/v1/pages',
        summary: 'Create Page',
        tags: 'Storefront - Pages',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['title', 'slug', 'content'],
                properties: [
                    new OAT\Property(property: 'title', type: 'string', example: 'About Us'),
                    new OAT\Property(property: 'slug', type: 'string', example: 'about-us'),
                    new OAT\Property(property: 'content', type: 'string', example: 'Content here'),
                    new OAT\Property(property: 'status', type: 'string', example: 'published'),
                ]
            )
        )
    )]
    public function store(StorePageRequest $request)
    {
        $dto = PageDTO::fromRequest($request);
        $page = $this->pageService->createPage($dto->toArray());
        
        return $this->created(new PageResource($page));
    }

    #[ApiGet(
        path: '/api/storefront/v1/pages/{page}',
        summary: 'Page Details',
        tags: 'Storefront - Pages',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'page', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'about-us'), description: 'Page ID or Slug')
        ]
    )]
    public function show($page)
    {
        $pageModel = is_numeric($page)
            ? $this->pageService->getPageById((int) $page)
            : $this->pageService->getPageBySlug((string) $page);

        if (!$pageModel) {
            return $this->notFound();
        }

        return $this->ok(new PageResource($pageModel));
    }

    #[ApiUpdate(
        path: '/api/storefront/v1/pages/{page}',
        summary: 'Update Page',
        tags: 'Storefront - Pages',
        parameters: [
            new OAT\Parameter(name: 'page', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: '1'), description: 'Page ID')
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'title', type: 'string', example: 'About Us Updated'),
                ]
            )
        )
    )]
    public function update(UpdatePageRequest $request, $id)
    {
        $dto = PageDTO::fromRequest($request);
        $page = $this->pageService->updatePage($id, $dto->toArray());
        
        return $this->ok(new PageResource($page));
    }

    #[ApiDelete(
        path: '/api/storefront/v1/pages/{page}',
        summary: 'Delete Page',
        tags: 'Storefront - Pages',
        parameters: [
            new OAT\Parameter(name: 'page', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: '1'), description: 'Page ID')
        ]
    )]
    public function destroy($id)
    {
        $this->pageService->deletePage($id);
        return $this->noContent();
    }
}

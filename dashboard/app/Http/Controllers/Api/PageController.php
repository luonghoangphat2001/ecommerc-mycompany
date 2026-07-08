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

/**
 * @group CMS
 *
 * APIs for managing CMS pages.
 */
class PageController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PageServiceInterface $pageService
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $pages = $this->pageService->getPaginatedPages($perPage);
        return $this->ok(PageResource::collection($pages));
    }

    public function store(StorePageRequest $request)
    {
        $dto = PageDTO::fromRequest($request);
        $page = $this->pageService->createPage($dto->toArray());
        
        return $this->created(new PageResource($page));
    }

    public function show($id)
    {
        $page = $this->pageService->getPageById($id);
        if (!$page) {
            return $this->notFound();
        }
        return $this->ok(new PageResource($page));
    }

    public function update(UpdatePageRequest $request, $id)
    {
        $dto = PageDTO::fromRequest($request);
        $page = $this->pageService->updatePage($id, $dto->toArray());
        
        return $this->ok(new PageResource($page));
    }

    public function destroy($id)
    {
        $this->pageService->deletePage($id);
        return $this->noContent();
    }
}

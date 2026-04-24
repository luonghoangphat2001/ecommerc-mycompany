<?php

namespace App\Services;

use App\Contracts\Repositories\PageRepositoryInterface;
use App\Contracts\Services\PageServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PageService implements PageServiceInterface
{
    protected $pageRepository;

    public function __construct(PageRepositoryInterface $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function getPaginatedPages(int $perPage = 10): LengthAwarePaginator
    {
        return $this->pageRepository->paginate($perPage);
    }

    public function getAllPages(): Collection
    {
        return $this->pageRepository->all();
    }

    public function getPageById($id): ?Model
    {
        return $this->pageRepository->find($id);
    }

    public function createPage(array $data): Model
    {
        return $this->pageRepository->create($data);
    }

    public function updatePage($id, array $data): bool
    {
        return $this->pageRepository->update($id, $data);
    }

    public function deletePage($id): bool
    {
        return $this->pageRepository->delete($id);
    }

    public function getPageBySlug(string $slug): ?Model
    {
        return $this->pageRepository->findBySlug($slug);
    }
}

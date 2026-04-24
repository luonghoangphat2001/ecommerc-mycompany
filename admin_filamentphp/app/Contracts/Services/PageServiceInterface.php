<?php

namespace App\Contracts\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface PageServiceInterface
{
    public function getPaginatedPages(int $perPage = 10): LengthAwarePaginator;

    public function getAllPages(): Collection;

    public function getPageById($id): ?Model;

    public function createPage(array $data): Model;

    public function updatePage($id, array $data): bool;

    public function deletePage($id): bool;

    public function getPageBySlug(string $slug): ?Model;
}

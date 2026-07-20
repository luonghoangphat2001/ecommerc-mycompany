<?php

namespace App\Ecommerce\Workspace\Repositories;

interface CskhRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array;
    public function getReviews(string $period = 'all');
    public function getCoupons();
    public function createReview(array $data);
    public function updateReview($id, array $data);
    public function deleteReview($id);
}

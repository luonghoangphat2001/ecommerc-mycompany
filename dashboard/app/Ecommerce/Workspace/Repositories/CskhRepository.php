<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Ecommerce\Workspace\Repositories\Concerns\HasWorkspaceCrudOperations;
use App\Models\Coupon;
use App\Models\DepartmentCustomerReview;

class CskhRepository implements CskhRepositoryInterface
{
    use HasWorkspaceCrudOperations;

    public function getMetrics(string $period = 'all'): array
    {
        $reviewMetrics = DepartmentCustomerReview::query()
            ->selectRaw('AVG(rating) as avg_rating')
            ->selectRaw('COUNT(*) as total_reviews')
            ->selectRaw("SUM(CASE WHEN sentiment = 'negative' THEN 1 ELSE 0 END) as negative_reviews")
            ->selectRaw("SUM(CASE WHEN sentiment = 'positive' THEN 1 ELSE 0 END) as positive_reviews")
            ->selectRaw('SUM(CASE WHEN coupon_id IS NOT NULL THEN 1 ELSE 0 END) as coupons_given')
            ->selectRaw('SUM(CASE WHEN reply_content IS NULL THEN 1 ELSE 0 END) as open_tickets')
            ->first();
        $avgRating = $reviewMetrics->avg_rating ?? 0;
        $totalReviews = (int) ($reviewMetrics->total_reviews ?? 0);
        $negativeReviews = (int) ($reviewMetrics->negative_reviews ?? 0);
        $positiveReviews = (int) ($reviewMetrics->positive_reviews ?? 0);
        $couponsGiven = (int) ($reviewMetrics->coupons_given ?? 0);

        $sentimentScore = $totalReviews > 0 ? max(0, round((($positiveReviews - $negativeReviews) / $totalReviews) * 100)) : 0;

        return [
            'avg_rating' => round($avgRating, 1),
            'open_tickets' => (int) ($reviewMetrics->open_tickets ?? 0),
            'sentiment' => $sentimentScore . '%',
            'coupons' => $couponsGiven,
        ];
    }

    public function getReviews(string $period = 'all')
    {
        return DepartmentCustomerReview::with(['user', 'coupon'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCoupons()
    {
        return Coupon::all(); // get all coupons
    }

    public function createReview(array $data)
    {
        return $this->createWorkspaceModel(DepartmentCustomerReview::class, $data);
    }

    public function updateReview($id, array $data)
    {
        return $this->updateWorkspaceModel(DepartmentCustomerReview::class, $id, $data);
    }

    public function deleteReview($id)
    {
        return $this->deleteWorkspaceModel(DepartmentCustomerReview::class, $id);
    }
}

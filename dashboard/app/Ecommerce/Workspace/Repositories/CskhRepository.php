<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Models\DepartmentCustomerReview;
use App\Models\Coupon;

class CskhRepository implements CskhRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array
    {
        $avgRating = DepartmentCustomerReview::avg('rating') ?? 0;
        $totalReviews = DepartmentCustomerReview::count();
        $negativeReviews = DepartmentCustomerReview::where('sentiment', 'negative')->count();
        $positiveReviews = DepartmentCustomerReview::where('sentiment', 'positive')->count();
        $couponsGiven = DepartmentCustomerReview::whereNotNull('coupon_id')->count();
        
        $sentimentScore = $totalReviews > 0 ? max(0, round((($positiveReviews - $negativeReviews) / $totalReviews) * 100)) : 0;

        return [
            'avg_rating' => round($avgRating, 1),
            'open_tickets' => DepartmentCustomerReview::whereNull('reply_content')->count(),
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
        return DepartmentCustomerReview::create($data);
    }

    public function updateReview($id, array $data)
    {
        $review = DepartmentCustomerReview::findOrFail($id);
        $review->update($data);
        return $review;
    }

    public function deleteReview($id)
    {
        $review = DepartmentCustomerReview::findOrFail($id);
        return $review->delete();
    }
}

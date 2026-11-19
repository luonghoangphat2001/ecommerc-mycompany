<?php

namespace App\Ecommerce\Core\Helpers;

use Illuminate\Database\Eloquent\Builder;

final class CatalogQueryFilters
{
    public static function searchTerm(array $filters): string
    {
        return trim((string) ($filters['search'] ?? $filters['q'] ?? $filters['keyword'] ?? ''));
    }

    public static function visibility(Builder $query, array $filters): Builder
    {
        if (! array_key_exists('is_visible', $filters)) {
            return $query->where('is_visible', true);
        }

        if ($filters['is_visible'] !== 'all' && $filters['is_visible'] !== null && $filters['is_visible'] !== '') {
            $query->where('is_visible', filter_var($filters['is_visible'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    public static function relation(Builder $query, mixed $value, string $relation, string $idColumn, string $slugColumn): Builder
    {
        if ($value === null || $value === '' || $value === 'all') {
            return $query;
        }

        return $query->whereHas($relation, function (Builder $related) use ($value, $idColumn, $slugColumn): void {
            if (is_numeric($value)) {
                $related->where($idColumn, (int) $value);
            } else {
                $related->where($slugColumn, (string) $value);
            }
        });
    }
}

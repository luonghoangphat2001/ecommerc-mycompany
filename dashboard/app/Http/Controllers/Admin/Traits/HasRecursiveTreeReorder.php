<?php

namespace App\Http\Controllers\Admin\Traits;

trait HasRecursiveTreeReorder
{
    protected function walkTree(array $tree, ?int $parentId, callable $persist): void
    {
        foreach ($tree as $index => $item) {
            $persist($item, $index, $parentId);

            if (isset($item['children']) && is_array($item['children'])) {
                $this->walkTree($item['children'], $item['id'] ?? null, $persist);
            }
        }
    }
}

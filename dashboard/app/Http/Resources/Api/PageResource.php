<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class PageResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale() ?: 'vi';
        $blocks = is_array($this->blocks) ? $this->localizeBlocks($this->blocks, $locale) : [];
        $content = $this->translate('content');

        if (empty($content) && !empty($blocks)) {
            $content = $this->renderBlocksToHtml($blocks);
        }

        return array_merge([
            'id' => $this->id,
            'title' => $this->translate('title'),
            'slug' => $this->slug,
            'content' => $content,
            'layout' => $this->layout,
            'parent_id' => $this->parent_id ?? null,
            'blocks' => $blocks,
        ], $this->getTimestamps());
    }

    protected function localizeBlocks(array $blocks, string $locale): array
    {
        return array_map(function ($block) use ($locale) {
            if (!is_array($block)) {
                return $block;
            }

            foreach (['value', 'caption', 'title', 'subtitle', 'content', 'heading'] as $key) {
                if (isset($block[$key]) && is_array($block[$key])) {
                    $block[$key] = $block[$key][$locale] ?? $block[$key]['vi'] ?? $block[$key]['en'] ?? reset($block[$key]);
                }
            }

            if (isset($block['data']) && is_array($block['data'])) {
                foreach ($block['data'] as $dKey => $dVal) {
                    if (is_array($dVal) && (isset($dVal['vi']) || isset($dVal['en']))) {
                        $block['data'][$dKey] = $dVal[$locale] ?? $dVal['vi'] ?? $dVal['en'] ?? reset($dVal);
                    }
                }
            }

            return $block;
        }, $blocks);
    }

    protected function renderBlocksToHtml(array $blocks): string
    {
        $parts = [];
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }
            $type = $block['type'] ?? 'markdown';
            $val = $block['value'] ?? $block['content'] ?? ($block['data']['content'] ?? $block['data']['heading'] ?? '');
            if (!is_string($val) || trim($val) === '') {
                continue;
            }
            if ($type === 'heading' || isset($block['data']['heading'])) {
                $lvl = (int) ($block['level'] ?? 2);
                if ($lvl < 1 || $lvl > 6) {
                    $lvl = 2;
                }
                $parts[] = "<h{$lvl}>" . e($val) . "</h{$lvl}>";
            } elseif ($type === 'media') {
                $parts[] = '<div class="media-block"><img src="' . e($val) . '" alt="" class="img-fluid" /></div>';
            } elseif ($type === 'gallery') {
                $images = is_array($val) ? $val : (is_array($block['images'] ?? null) ? $block['images'] : []);
                $imgTags = array_map(fn($u) => '<img src="' . e($u) . '" alt="" />', $images);
                $parts[] = '<div class="gallery-block grid grid-cols-2 gap-4">' . implode('', $imgTags) . '</div>';
            } else {
                $parts[] = str_starts_with(trim($val), '<') ? $val : '<div class="prose-block">' . nl2br(e($val)) . '</div>';
            }
        }

        return implode("\n", $parts);
    }
}

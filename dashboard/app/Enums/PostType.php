<?php

namespace App\Enums;

enum PostType: string
{
    case Blog = 'blog';
    case News = 'news';
    case Page = 'page';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Blog => 'Blog',
            self::News => 'News',
            self::Page => 'Page',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Blog => 'Bài viết (Blog)',
            self::News => 'Tin tức (News)',
            self::Page => 'Trang tĩnh (Page)',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function optionsVi(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->labelVi();
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

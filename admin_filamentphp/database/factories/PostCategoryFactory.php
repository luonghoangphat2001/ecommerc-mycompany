<?php

namespace Database\Factories;

use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostCategoryFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = PostCategory::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => [
                'vi' => 'Danh mục: ' . $name,
                'en' => 'Category: ' . $name,
            ],
            'slug' => Str::slug($name),
            'type' => 'post',
            'description' => $this->faker->sentence(),
            'is_visible' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Post::class;

    public function definition(): array
    {
        $posts = [
            'Khám phá vẻ đẹp Sa Pa',
            'Hướng dẫn du lịch Đà Nẵng',
            'Top 10 món ăn đường phố Sài Gòn',
            'Kinh nghiệm phượt Hà Giang',
            'Review khách sạn 5 sao Phú Quốc',
            'Hành trình khám phá Tây Nguyên',
            'Cẩm nang du lịch Hội An',
            'Vẻ đẹp tiềm ẩn của vịnh Hạ Long',
        ];
        $title = $this->faker->randomElement($posts);
        
        return [
            'author_id' => User::factory(),
            'title' => [
                'vi' => $title,
                'en' => 'English: ' . $title,
            ],
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'content' => [
                'vi' => $this->faker->paragraphs(3, true),
                'en' => 'English content for: ' . $title,
            ],
            'post_type' => $this->faker->randomElement(['blog', 'news', 'page']),
            'image' => rand(1, 40),
            'published_at' => $this->faker->dateTimeBetween('-6 month', '+1 month'),
            'is_visible' => true,
            'seo_title' => Str::limit($title, 150),
            'seo_description' => $this->faker->sentence(20),
        ];
    }
}

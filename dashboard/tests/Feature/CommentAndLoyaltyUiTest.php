<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Settings\LoyaltySettings;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommentAndLoyaltyUiTest extends TestCase
{
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');
    }

    public function test_comment_edit_screen_renders_visible_dropdown_with_labels(): void
    {
        $product = Product::factory()->create();

        $comment = Comment::create([
            'user_id' => $this->superAdmin->id,
            'commentable_type' => Product::class,
            'commentable_id' => $product->id,
            'title' => 'Test comment',
            'content' => 'Test content',
            'is_visible' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'vi'])
            ->get(route('admin.comments.edit', $comment->id));

        $response->assertOk();
        $response->assertSee('<select id="is_visible" name="is_visible"', false);
        $response->assertSee('Có (Hiển thị)');
        $response->assertSee('Không (Tạm ẩn)');
    }

    public function test_loyalty_settings_renders_multi_dropdown_and_saves_selected_items(): void
    {
        $category = ProductCategory::factory()->create(['name' => 'Thời trang']);
        $product = Product::factory()->create(['name' => 'Áo polo', 'is_visible' => true]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.settings.index', ['tab' => 'loyalty']));

        $response->assertOk();
        $response->assertSee('multi-select-dropdown-wrapper');
        $response->assertSee('excluded_category_ids');
        $response->assertSee('excluded_product_ids');

        $updateResponse = $this->actingAs($this->superAdmin)->post(route('admin.settings.update-group'), [
            'group' => 'loyalty',
            'settings' => [
                'enabled' => '1',
                'spending_amount_per_point' => '10000',
                'point_conversion_rate' => '1000',
                'minimum_points_to_redeem' => '10',
                'max_redeem_percentage' => '50',
                'exclude_sale_items' => '0',
                'excluded_category_ids' => [(string) $category->id],
                'excluded_product_ids' => [(string) $product->id],
            ],
        ]);

        $updateResponse->assertRedirect();
        $settings = app(LoyaltySettings::class);
        $this->assertEquals([(string) $category->id], $settings->excluded_category_ids);
        $this->assertEquals([(string) $product->id], $settings->excluded_product_ids);
    }
}

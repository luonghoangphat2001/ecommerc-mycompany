<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');
    }

    public function test_admin_can_access_pages_index_without_array_to_string_conversion_error(): void
    {
        Page::create([
            'title' => ['en' => 'Test Page for Admin', 'vi' => 'Test Page for Admin'],
            'slug' => 'test-page-admin-' . uniqid(),
            'layout' => 'default',
            'blocks' => [
                ['type' => 'heading', 'value' => 'Hello Title', 'level' => 2],
                ['type' => 'editor', 'value' => '<p>Some content</p>', 'caption' => ''],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.pages.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Page for Admin');
    }

    public function test_admin_can_view_page_detail_with_blocks(): void
    {
        $page = Page::create([
            'title' => 'Detailed Page',
            'slug' => 'detailed-page-' . uniqid(),
            'layout' => 'default',
            'blocks' => [
                ['type' => 'heading', 'value' => 'Block Title', 'level' => 1],
                ['type' => 'editor', 'value' => '<p>Detailed paragraph</p>', 'caption' => ''],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.pages.show', $page->id));

        $response->assertStatus(200);
        $response->assertSee('Block Title');
    }

    public function test_show_field_handles_nested_arrays_and_blocks_safely(): void
    {
        $renderedCompact = view('admin.crud.show_field', [
            'type' => 'blocks',
            'value' => [
                ['type' => 'heading', 'value' => 'Compact Heading'],
            ],
            'compact' => true,
        ])->render();

        $this->assertStringContainsString('badge', $renderedCompact);

        $renderedNestedArray = view('admin.crud.show_field', [
            'type' => 'text',
            'value' => [
                ['key' => 'nested_val_1'],
                ['key' => 'nested_val_2'],
            ],
            'compact' => true,
        ])->render();

        $this->assertStringContainsString('badge', $renderedNestedArray);
    }
}

<?php

namespace Tests\Feature;

use App\Ecommerce\Settings\Contracts\StorefrontSettingsServiceInterface;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use App\Settings\FooterSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FooterSettingsSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Menu $testFooterMenu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin-footer-test@example.com'],
            ['name' => 'Admin Footer Tester', 'password' => bcrypt('password123')]
        );

        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->admin->assignRole($role);

        $this->testFooterMenu = Menu::firstOrCreate(
            ['handle' => 'footer-menu'],
            ['name' => 'Footer Navigation Menu']
        );

        // Clear existing items in this menu for clean test state
        MenuItem::where('menu_id', $this->testFooterMenu->id)->delete();

        MenuItem::create([
            'menu_id' => $this->testFooterMenu->id,
            'title' => ['vi' => 'Chính sách bảo mật', 'en' => 'Privacy Policy'],
            'url' => '/privacy',
            'order' => 1,
            'type' => 'custom',
            'target' => '_self',
        ]);

        MenuItem::create([
            'menu_id' => $this->testFooterMenu->id,
            'title' => ['vi' => 'Điều khoản dịch vụ', 'en' => 'Terms of Service'],
            'url' => '/terms',
            'order' => 2,
            'type' => 'custom',
            'target' => '_self',
        ]);
    }

    public function test_admin_settings_updates_footer_and_syncs_menu_links(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update-group'), [
            'group' => 'footer',
            'settings' => [
                'copyright' => '© 2026 Test Store. All rights reserved.',
                'menu_handle' => 'footer-menu',
            ],
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'footer']));

        $footerSettings = app(FooterSettings::class);
        $this->assertSame('© 2026 Test Store. All rights reserved.', $footerSettings->copyright);
        $this->assertSame('footer-menu', $footerSettings->menu_handle);
        $this->assertIsArray($footerSettings->links);
        $this->assertCount(2, $footerSettings->links);
        $this->assertSame('/privacy', $footerSettings->links[0]['url']);
        $this->assertSame('/terms', $footerSettings->links[1]['url']);
    }

    public function test_storefront_settings_service_returns_synchronized_footer(): void
    {
        $footerSettings = app(FooterSettings::class);
        $footerSettings->menu_handle = 'footer-menu';
        $footerSettings->copyright = '© 2026 Test Store';
        $footerSettings->save();

        /** @var StorefrontSettingsServiceInterface $service */
        $service = app(StorefrontSettingsServiceInterface::class);
        $settings = $service->getStorefrontSettings();

        $this->assertArrayHasKey('footer', $settings);
        $this->assertSame('footer-menu', $settings['footer']['menu_handle']);
        $this->assertSame('© 2026 Test Store', $settings['footer']['copyright']);
        $this->assertCount(2, $settings['footer']['links']);
    }

    public function test_menu_item_changes_automatically_sync_to_footer_settings(): void
    {
        $footerSettings = app(FooterSettings::class);
        $footerSettings->menu_handle = 'footer-menu';
        $footerSettings->save();

        $response = $this->actingAs($this->admin)->post(route('admin.menu-items.store'), [
            'menu_id' => $this->testFooterMenu->id,
            'title' => 'Chính sách hoàn tiền',
            'url' => '/refund',
            'type' => 'custom',
            'order' => 3,
            'target' => '_self',
        ]);

        $response->assertRedirect();

        $footerSettings = app(FooterSettings::class);
        $this->assertCount(3, $footerSettings->links);
        $urls = array_column($footerSettings->links, 'url');
        $this->assertContains('/refund', $urls);
    }
}

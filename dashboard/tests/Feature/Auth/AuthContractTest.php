<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Ecommerce\Core\Services\AuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthContractTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_returns_the_current_passport_contract(): void
    {
        $user = User::factory()->create([
            'email' => 'contract-login@example.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'contract-test',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => ['id', 'email'],
                ],
                'meta',
            ])
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', $user->email);

        $response->assertCookie('storefront_refresh');
        $this->assertArrayNotHasKey('refresh_token', $response->json('data'));

        $authData = app(AuthService::class)->login($user->email, 'password', 'contract-test-refresh');
        $rotated = app(AuthService::class)->refreshToken($authData['refresh_token']);
        $this->assertNotNull($rotated);
        $this->assertSame(900, $rotated['expires_in']);
        $this->assertNull(app(AuthService::class)->refreshToken($authData['refresh_token']));
    }

    public function test_protected_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/profile')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_refresh_token_cannot_access_protected_routes(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $authData = app(AuthService::class)->login($user->email, 'password', 'refresh-test');

        $response = $this->withToken($authData['refresh_token'])
            ->getJson('/api/v1/auth/profile');

        $response->assertUnauthorized();
    }

    public function test_password_reset_requires_a_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'contract-reset@example.test',
        ]);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(422);

        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }
}

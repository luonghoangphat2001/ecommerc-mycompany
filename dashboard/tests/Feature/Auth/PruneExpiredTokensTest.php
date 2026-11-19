<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Token;
use Tests\TestCase;

class PruneExpiredTokensTest extends TestCase
{
    use DatabaseTransactions;

    public function test_prune_command_removes_old_passport_tokens(): void
    {
        $user = User::factory()->create();
        $old = $user->createToken('old', ['access'])->token;
        $recent = $user->createToken('recent', ['access'])->token;
        $active = $user->createToken('active', ['access'])->token;

        Token::query()->whereKey($old->id)->update(['expires_at' => now()->subDays(8)]);
        Token::query()->whereKey($recent->id)->update(['expires_at' => now()->subDay()]);
        Token::query()->whereKey($active->id)->update(['expires_at' => now()->addDay()]);

        $this->artisan('auth:prune-tokens')->assertSuccessful();

        $this->assertDatabaseMissing('oauth_access_tokens', ['id' => $old->id]);
        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $recent->id]);
        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $active->id]);
    }
}

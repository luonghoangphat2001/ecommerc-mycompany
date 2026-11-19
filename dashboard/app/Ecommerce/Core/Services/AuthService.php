<?php

namespace App\Ecommerce\Core\Services;

use App\Ecommerce\User\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthService
{
    private const ACCESS_TOKEN_TTL_SECONDS = 900;

    private const REFRESH_TOKEN_TTL_SECONDS = 604800;

    public function __construct(protected UserRepositoryInterface $userRepository) {}

    /** @throws ValidationException */
    public function login(string $email, string $password, string $deviceName): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->issueTokenPair($user, $deviceName);
    }

    public function register(array $data): array
    {
        /** @var User $user */
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);

        try {
            if (method_exists($user, 'assignRole') && Role::where('name', 'customer')->exists()) {
                $user->assignRole('customer');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->issueTokenPair($user, $data['device_name'] ?? 'storefront_web');
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function updatePassword(User $user, string $password): User
    {
        $user->update(['password' => $password]);

        return $user->fresh();
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh(['defaultShippingAddress', 'defaultBillingAddress']);
    }

    public function refreshToken(string $plainTextToken): ?array
    {
        return DB::transaction(function () use ($plainTextToken): ?array {
            $refreshToken = DB::table('auth_refresh_tokens')
                ->lockForUpdate()
                ->where('token_hash', hash('sha256', $plainTextToken))
                ->whereNull('revoked_at')
                ->first();

            if (! $refreshToken || now()->greaterThan($refreshToken->expires_at)) {
                if ($refreshToken) {
                    DB::table('auth_refresh_tokens')->where('id', $refreshToken->id)->update(['revoked_at' => now()]);
                }

                return null;
            }

            $user = User::query()->find($refreshToken->user_id);
            DB::table('auth_refresh_tokens')->where('id', $refreshToken->id)->update(['revoked_at' => now()]);

            if (! $user) {
                return null;
            }

            return $this->issueTokenPair($user, $refreshToken->device_name, false);
        });
    }

    /** @return array<string, mixed> */
    private function issueTokenPair(User $user, string $deviceName, bool $revokeDevice = true): array
    {
        $deviceName = trim($deviceName) ?: 'storefront_web';

        if ($revokeDevice) {
            $user->tokens()
                ->where('name', "access:{$deviceName}")
                ->update(['revoked' => true]);

            DB::table('auth_refresh_tokens')
                ->where('user_id', $user->id)
                ->where('device_name', $deviceName)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }

        $accessToken = $user->createToken(
            "access:{$deviceName}",
            ['access']
        );
        $plainRefreshToken = Str::random(80);

        DB::table('auth_refresh_tokens')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainRefreshToken),
            'device_name' => $deviceName,
            'expires_at' => now()->addSeconds(self::REFRESH_TOKEN_TTL_SECONDS),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'access_token' => $accessToken->accessToken,
            'refresh_token' => $plainRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TOKEN_TTL_SECONDS,
            'refresh_expires_in' => self::REFRESH_TOKEN_TTL_SECONDS,
            'user' => $user->load(['defaultShippingAddress', 'defaultBillingAddress']),
        ];
    }

    public function logout(User $user): void
    {
        $currentToken = $user->currentAccessToken();
        $deviceName = preg_replace('/^access:/', '', (string) $currentToken?->name) ?: null;

        $currentToken?->revoke();

        if ($deviceName) {
            DB::table('auth_refresh_tokens')
                ->where('user_id', $user->id)
                ->where('device_name', $deviceName)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }
    }

    public function revokeAll(User $user): void
    {
        $user->tokens()->update(['revoked' => true]);
        DB::table('auth_refresh_tokens')
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }
}

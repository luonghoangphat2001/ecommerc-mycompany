<?php

namespace App\Ecommerce\Core\Services;

use App\Ecommerce\User\Contracts\UserRepositoryInterface;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const ACCESS_TOKEN_TTL_SECONDS = 900;
    private const REFRESH_TOKEN_TTL_SECONDS = 604800;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * AuthService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Authenticate user and return token.
     *
     * @param string $email
     * @param string $password
     * @param string $deviceName
     * @return array
     * @throws ValidationException
     */
    public function login(string $email, string $password, string $deviceName): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->delete();
        $expiresIn = self::ACCESS_TOKEN_TTL_SECONDS;
        $token = $user->createToken($deviceName, ['*'], now()->addSeconds($expiresIn));

        return $this->buildTokenResponse(
            $token->plainTextToken,
            $user->load(['defaultShippingAddress', 'defaultBillingAddress']),
            $expiresIn
        );
    }

    /**
     * Refresh the current access token using the bearer token provided by the client.
     *
     * @param string $plainTextToken
     * @return array|null
     */
    public function refreshToken(string $plainTextToken): ?array
    {
        $accessToken = PersonalAccessToken::findToken($plainTextToken);

        if (!$accessToken || !$accessToken->tokenable instanceof User) {
            return null;
        }

        $tokenCreatedAt = Carbon::parse($accessToken->created_at);
        $tokenAgeInSeconds = $tokenCreatedAt->diffInSeconds(now());

        if ($tokenAgeInSeconds > self::REFRESH_TOKEN_TTL_SECONDS) {
            $accessToken->delete();

            return null;
        }

        $user = $accessToken->tokenable->load(['defaultShippingAddress', 'defaultBillingAddress']);
        $deviceName = $accessToken->name ?: 'storefront_web';

        $accessToken->delete();

        $newToken = $user->createToken(
            $deviceName,
            $accessToken->abilities ? json_decode($accessToken->abilities, true) : ['*'],
            now()->addSeconds(self::ACCESS_TOKEN_TTL_SECONDS)
        );

        return $this->buildTokenResponse(
            $newToken->plainTextToken,
            $user,
            self::ACCESS_TOKEN_TTL_SECONDS
        );
    }

    /**
     * Normalize token payload for frontend compatibility.
     *
     * @param string $plainTextToken
     * @param User $user
     * @param int $expiresIn
     * @return array
     */
    private function buildTokenResponse(string $plainTextToken, User $user, int $expiresIn): array
    {
        return [
            'access_token' => $plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn,
            'user' => $user,
        ];
    }

    /**
     * Log out the current user.
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}

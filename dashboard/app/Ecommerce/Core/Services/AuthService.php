<?php

namespace App\Ecommerce\Core\Services;

use App\Ecommerce\User\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
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
        $expiresIn = 3600;
        $token = $user->createToken($deviceName, ['*'], now()->addSeconds($expiresIn));

        return [
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn,
            'user' => $user->load(['defaultShippingAddress', 'defaultBillingAddress'])
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

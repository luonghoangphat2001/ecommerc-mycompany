<?php

namespace App\Http\Controllers\Api;

use App\Ecommerce\Core\Services\AuthService;
use App\Http\Requests\API\Auth\ChangePasswordRequest;
use App\Http\Requests\API\Auth\ForgotPasswordRequest;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use App\Http\Requests\API\Auth\UpdateProfileRequest;
use App\Http\Resources\Api\UserResource;
use App\Services\Logging\ModuleLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use OpenApi\Attributes as OAT;

class AuthController extends BaseApiController
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[ApiPost(
        path: '/auth/login',
        summary: 'Storefront Login',
        tags: 'Authentication',
        requiresAuth: false,
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['email', 'password', 'device_name'],
                properties: [
                    new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'customer@example.com'),
                    new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OAT\Property(property: 'device_name', type: 'string', example: 'web')
                ]
            )
        ),
        responseData: [
            new OAT\Property(property: 'access_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxIn0.signature'),
            new OAT\Property(property: 'token_type', type: 'string', example: 'Bearer'),
            new OAT\Property(property: 'expires_in', type: 'integer', example: 900),
            new OAT\Property(property: 'user', ref: '#/components/schemas/UserResource')
        ]
    )]


    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $authData = $this->authService->login(
            $validated['email'],
            $validated['password'],
            $validated['device_name']
        );

        ModuleLogger::auth()->info('login_success', 'User logged in successfully', [
            'user_id' => $authData['user']['id'],
            'email' => $validated['email'],
            'device_name' => $validated['device_name'],
        ]);

        return $this->ok(
            $this->tokenResponseData($authData),
            __('messages.login_success') ?? 'Login successful.'
        )
            ->withCookie($this->refreshCookie($authData['refresh_token']));
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $authData = $this->authService->register($validated);

        ModuleLogger::auth()->info('register_success', 'User registered successfully', [
            'user_id' => $authData['user']->id,
            'email' => $validated['email'],
        ]);

        return $this->created(
            $this->tokenResponseData($authData),
            __('storefront.auth.register_success')
        )
            ->withCookie($this->refreshCookie($authData['refresh_token']));
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $validated = $request->validated();

        $user = $this->authService->findByEmail($validated['email']);
        $token = null;

        if ($user) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );
        }

        return $this->ok([
            'reset_sent' => true,
            'email' => $validated['email'],
        ], __('storefront.auth.reset_email_sent'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        $user = $this->authService->findByEmail($validated['email']);
        if (! $user) {
            return $this->validationError(['email' => [__('passwords.user')]]);
        }

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        $expiresAt = now()->subMinutes((int) config('auth.passwords.users.expire', 60));
        if (! $resetRecord
            || ! $resetRecord->created_at
            || $resetRecord->created_at < $expiresAt
            || ! Hash::check($validated['token'], $resetRecord->token)) {
            return $this->validationError(['token' => [__('passwords.token')]]);
        }

        $this->authService->updatePassword($user, $validated['password']);

        $this->authService->revokeAll($user);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        return $this->ok([
            'reset' => true,
        ], __('storefront.auth.reset_success'));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized(__('messages.unauthorized') ?? 'Unauthorized');
        }

        $validated = $request->validated();

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('storefront.account.password_update_failed')],
            ]);
        }

        $this->authService->updatePassword($user, $validated['password']);

        $this->authService->revokeAll($user);

        return $this->ok(
            new UserResource($user->fresh(['defaultShippingAddress', 'defaultBillingAddress'])),
            __('storefront.account.password_updated')
        );
    }

    #[ApiPost(
        path: '/auth/refresh-token',
        summary: 'Refresh Access Token',
        tags: 'Authentication',
        requiresAuth: false,
        requestBody: new OAT\RequestBody(
            required: false
        ),
        responseData: [
            new OAT\Property(property: 'access_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxIn0.signature'),
            new OAT\Property(property: 'token_type', type: 'string', example: 'Bearer'),
            new OAT\Property(property: 'expires_in', type: 'integer', example: 900),
            new OAT\Property(property: 'user', type: 'object')
        ]
    )]


    public function refreshToken(Request $request)
    {
        $refreshToken = $request->cookie('storefront_refresh');

        if (! $refreshToken) {
            return $this->unauthorized('Token not found');
        }

        $authData = $this->authService->refreshToken($refreshToken);

        if (! $authData) {
            return $this->unauthorized('Token expired');
        }

        return $this->ok($this->tokenResponseData($authData))
            ->withCookie($this->refreshCookie($authData['refresh_token']));
    }

    #[ApiPost(
        path: '/auth/logout',
        summary: 'Logout',
        tags: 'Authentication',
        requiresAuth: true
    )]


    public function logout(Request $request)
    {
        if (! $request->user()) {
            return $this->notFound('User not found');
        }

        $user = $request->user();
        $this->authService->logout($user);

        ModuleLogger::auth()->info('logout_success', 'User logged out successfully', [
            'user_id' => $user->id,
        ]);

        return $this->ok(null, 'Logged out successfully')
            ->withCookie(Cookie::forget('storefront_refresh', '/api/v1/auth', config('session.domain')));
    }

    #[ApiGet(
        path: '/auth/profile',
        summary: 'Get Profile',
        tags: 'Authentication',
        requiresAuth: true,
        responseData: '#/components/schemas/UserResource'
    )]


    public function profile(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized(__('messages.unauthorized') ?? 'Unauthorized');
        }

        return $this->ok(new UserResource($user->load(['defaultShippingAddress', 'defaultBillingAddress'])));
    }

    #[ApiUpdate(
        path: '/profile',
        summary: 'Update Profile',
        tags: 'Authentication',
        requiresAuth: true,
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'name', type: 'string', example: 'Nguyen Van A'),
                    new OAT\Property(property: 'phone', type: 'string', example: '0123456789'),
                    new OAT\Property(property: 'default_shipping_address_id', type: 'integer', nullable: true, example: 1),
                    new OAT\Property(property: 'default_billing_address_id', type: 'integer', nullable: true, example: 2)
                ]
            )
        ),
        responseData: '#/components/schemas/UserResource'
    )]


    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        if (! $user) {
            return $this->notFound('User not found');
        }

        $validated = $request->validated();

        if (! empty($validated['password'])) {
            if (empty($validated['current_password']) || ! Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => [__('storefront.account.password_update_failed')],
                ]);
            }
            if ($request->filled('password_confirmation') && $validated['password'] !== $request->input('password_confirmation')) {
                throw ValidationException::withMessages([
                    'password_confirmation' => [__('storefront.account.password_mismatch')],
                ]);
            }
        } else {
            unset($validated['password']);
        }

        unset($validated['current_password'], $validated['password_confirmation']);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['photo'] = $path;
        }
        unset($validated['avatar']);

        $oldData = $user->only(['name', 'phone', 'default_shipping_address_id', 'default_billing_address_id']);
        $user = $this->authService->updateProfile($user, $validated);

        ModuleLogger::user()->info('profile_updated', 'User profile updated successfully', [
            'user_id' => $user->id,
            'changed_fields' => array_values(array_unique(array_merge(array_keys($oldData), array_keys($validated)))),
        ]);

        return $this->ok(
            new UserResource($user),
            __('storefront.account.profile_updated')
        );
    }

    private function refreshCookie(string $plainTextToken): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            'storefront_refresh',
            $plainTextToken,
            7 * 24 * 60,
            '/api/v1/auth',
            config('session.domain'),
            (bool) config('session.secure', app()->isProduction()),
            true,
            false,
            config('session.same_site', 'lax')
        );
    }

    private function tokenResponseData(array $authData): array
    {
        return [
            'access_token' => $authData['access_token'],
            'token_type' => $authData['token_type'],
            'expires_in' => $authData['expires_in'],
            'user' => new UserResource($authData['user']),
        ];
    }
}

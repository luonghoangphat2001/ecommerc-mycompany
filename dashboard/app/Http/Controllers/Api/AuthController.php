<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Ecommerce\Core\Services\AuthService;

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
        tags: 'Storefront - Authentication',
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
            new OAT\Property(property: 'access_token', type: 'string', example: '1|xyz...'),
            new OAT\Property(property: 'token_type', type: 'string', example: 'Bearer'),
            new OAT\Property(property: 'expires_in', type: 'integer', example: 3600),
            new OAT\Property(property: 'user', type: 'object', properties: [
                new OAT\Property(property: 'id', type: 'integer', example: 1),
                new OAT\Property(property: 'name', type: 'string', example: 'John Doe'),
                new OAT\Property(property: 'email', type: 'string', example: 'customer@example.com'),
            ])
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $authData = $this->authService->login(
            $request->email,
            $request->password,
            $request->device_name
        );

        \App\Services\Logging\ModuleLogger::auth()->info('login_success', 'User logged in successfully', [
            'user_id' => $authData['user']['id'],
            'email' => $request->email,
            'device_name' => $request->device_name,
        ]);

        return $this->ok([
            'access_token' => $authData['access_token'],
            'token_type' => $authData['token_type'],
            'expires_in' => $authData['expires_in'],
            'user' => $authData['user']
        ], 'Login successful.');
    }

    #[ApiPost(
        path: '/auth/refresh-token',
        summary: 'Refresh Access Token',
        tags: 'Storefront - Authentication',
        requiresAuth: false,
        requestBody: new OAT\RequestBody(
            required: false
        ),
        responseData: [
            new OAT\Property(property: 'access_token', type: 'string', example: '1|xyz...'),
            new OAT\Property(property: 'token_type', type: 'string', example: 'Bearer'),
            new OAT\Property(property: 'expires_in', type: 'integer', example: 900),
            new OAT\Property(property: 'user', type: 'object')
        ]
    )]
    public function refreshToken(Request $request)
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return $this->unauthorized('Token not found');
        }

        $authData = $this->authService->refreshToken($bearerToken);

        if (!$authData) {
            return $this->unauthorized('Token expired');
        }

        return response()->json($authData, 200);
    }

    #[ApiPost(
        path: '/auth/logout',
        summary: 'Logout',
        tags: 'Storefront - Authentication',
        requiresAuth: true
    )]
    public function logout(Request $request)
    {
        if (!$request->user()) {
            return $this->notFound('User not found');
        }

        $user = $request->user();
        $this->authService->logout($user);

        \App\Services\Logging\ModuleLogger::auth()->info('logout_success', 'User logged out successfully', [
            'user_id' => $user->id,
        ]);

        return $this->ok(null, 'Logged out successfully');
    }

    #[ApiUpdate(
        path: '/profile',
        summary: 'Update Profile',
        tags: 'Storefront - Authentication',
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
        responseData: [
            new OAT\Property(property: 'id', type: 'integer', example: 1),
            new OAT\Property(property: 'name', type: 'string', example: 'John Doe'),
            new OAT\Property(property: 'email', type: 'string', example: 'customer@example.com'),
            new OAT\Property(property: 'phone', type: 'string', nullable: true, example: '0123456789'),
            new OAT\Property(property: 'default_shipping_address_id', type: 'integer', nullable: true, example: 1),
            new OAT\Property(property: 'default_billing_address_id', type: 'integer', nullable: true, example: 2),
        ]
    )]
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->notFound('User not found');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'default_shipping_address_id' => 'sometimes|nullable|exists:addresses,id',
            'default_billing_address_id' => 'sometimes|nullable|exists:addresses,id',
        ]);

        $oldData = $user->only(['name', 'phone', 'default_shipping_address_id', 'default_billing_address_id']);
        $user->update($validated);

        \App\Services\Logging\ModuleLogger::user()->info('profile_updated', 'User profile updated successfully', [
            'user_id' => $user->id,
            'old_data' => $oldData,
            'new_data' => $validated,
        ]);

        return $this->ok($user->fresh(['defaultShippingAddress', 'defaultBillingAddress']), 'Profile updated successfully');
    }
}

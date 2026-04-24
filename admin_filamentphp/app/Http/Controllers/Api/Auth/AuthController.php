<?php

namespace App\Http\Controllers\Api\Auth;
 
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
 
class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;
 
    public function __construct(\App\Services\AuthService $authService)
    {
        $this->authService = $authService;
    }
    /**
     * User Login
     * 
     * Authenticate a user with email and password to receive a Bearer token.
     * 
     * @unauthenticated
     */
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
 
        return $this->ok([
            'token' => $authData['token'],
            'expires_at' => Carbon::parse($authData['expires_at'])->toDateTimeString(),
            'user' => $authData['user']
        ]);
    }
 
 
 
    /**
     * User Logout
     * 
     * Revoke the current access token.
     */
    public function logout(Request $request)
    {
        if (!$request->user()) {
            return $this->notFound('User not found');
        }
 
        $this->authService->logout($request->user());
 
        return $this->ok(null, 'Logged out successfully');
    }
}

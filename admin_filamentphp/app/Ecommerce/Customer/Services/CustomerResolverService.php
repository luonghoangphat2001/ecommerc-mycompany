<?php

namespace App\Ecommerce\Customer\Services;

use App\Ecommerce\Customer\Contracts\CustomerResolverServiceInterface;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerResolverService implements CustomerResolverServiceInterface
{
    /**
     * @inheritDoc
     */
    public function resolveCustomerId(Request $request, ?string $email = null): ?int
    {
        // 1. Check via request user (Sanctum/Bearer token)
        if ($request->user()) {
            return $request->user()->id;
        }

        // 2. Check via web session authentication
        if (auth()->check()) {
            return auth()->id();
        }

        // 3. Fallback: lookup via email provided during guest checkout
        if ($email) {
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                return $existingUser->id;
            }
        }

        return null;
    }
}

<?php

namespace App\Ecommerce\Customer\Contracts;

use Illuminate\Http\Request;

interface CustomerResolverServiceInterface
{
    /**
     * Resolve the customer ID based on authentication or request context.
     *
     * @param Request $request
     * @param string|null $email Fallback lookup by email
     * @return int|null
     */
    public function resolveCustomerId(Request $request, ?string $email = null): ?int;
}

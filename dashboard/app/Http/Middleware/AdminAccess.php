<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if ($user->hasRole('super_admin') || $user->can('access-admin-panel')) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access admin area.');
    }
}

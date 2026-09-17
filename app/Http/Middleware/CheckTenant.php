<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return redirect()->route('login');
        }

        if (!$user->tenant_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'User is not associated with a tenant.',
                ], 403);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Your account is not associated with a tenant.');
        }

        $tenant = Tenant::find($user->tenant_id);
        if (is_null($tenant)) {
            return response()->json([
                'message' => 'User is not associated with a tenant.'
            ], 403);
        }

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
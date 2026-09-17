<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->tenant) {

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tenant not found.',
                ], 403);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Tenant not found.');
        }

        if ($user->tenant->subscription_status !== 'active') {

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your tenant subscription is not active.',
                ], 403);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Your tenant subscription is not active.');
        }

        return $next($request);
    }
}
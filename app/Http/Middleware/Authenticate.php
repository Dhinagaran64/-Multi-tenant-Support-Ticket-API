<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        $host = $request->getHost();
        if (!$request->expectsJson()) {
            return route('login');
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string[] ...$guards
     * @return mixed
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        // check session expire
        if (!$request->is('api/*') && !Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            session(['url.intended' => $request->fullUrl()]);

            $host = $request->getHost();
            // For web routes, redirect to login
            return redirect()->route('login')->with('message', 'Your session has expired, please log in again.');
        }

        try {
            $this->authenticate($request, $guards);
        } catch (AuthenticationException $e) {
            if ($request->is('api/*')) {
                return resp(401, false, "Unauthorized");
            }

            $host = $request->getHost();

            // For web routes, redirect to login 
            return redirect()->route('login')->with('message', 'Please log in to access this page.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            $token = $request->cookie('access_token');

            if (!is_null($token)) {
                if (str_contains($token, '|')) {
                    [$id, $plainTextToken] = explode('|', $token, 2);
                    $accessToken = PersonalAccessToken::find($id);

                    if ($accessToken) {
                        $hashedToken = hash('sha256', $plainTextToken);
                        if (hash_equals($accessToken->token, $hashedToken)) {
                            $accessToken->delete();
                        }
                    }
                }
            }

            setcookie('access_token', '', time() - 3600, '/');
        } else {

            $token = $request->cookie('access_token');
            
            if (is_null($token) && isset($_COOKIE['access_token'])) {
                $token = $_COOKIE['access_token'];
            }

            if (is_null($token)) {
                $user = $request->user();
                $token = $user->createToken('access_token')->plainTextToken;
                
                $currentPath = $request->getPathInfo();
                if ($currentPath !== '/' && $currentPath !== '') {
                    setcookie('access_token', '', time() - 3600, $currentPath);
                }
                
                setcookie('access_token', $token, 0, '/');
            } else {
                $currentPath = $request->getPathInfo();
                if ($currentPath !== '/' && $currentPath !== '') {
                    setcookie('access_token', '', time() - 3600, $currentPath);
                }
                
                setcookie('access_token', $token, 0, '/');
            }

            $request->attributes->set('access_token', $token);
        }

        return $next($request);
    }
}

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::view('/login', 'auth.login')->name('login');

Route::middleware(['auth', 'check.tenant', 'check.subscription'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::view('/tickets', 'tickets.index')->name('tickets.index');

    Route::post('/logout', function (Request $request) {
        $user = $request->user();
        if ($user) {
            if (method_exists($user, 'tokens')) {
                $user->tokens->each(function ($token) {
                    $token->delete();
                });
            }
            
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Logged out successfully.');

    })->name('logout');
});

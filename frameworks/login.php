<?php 
app/Http/Controllers/Auth/AuthenticatedSessionController.php


$request->authenticate();
$request->session()->regenerate();

return redirect()->intended(RouteServiceProvider::HOME);


$request->validate([
    'email' => ['required', 'email'],
    'password' => ['required'],
]);


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');php artisan install:api
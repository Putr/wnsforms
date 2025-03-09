<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Form submission routes are defined in routes/api.php to avoid CSRF protection
// See: https://laravel.com/docs/12.x/csrf#csrf-excluding-uris

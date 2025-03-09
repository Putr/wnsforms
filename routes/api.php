<?php

use App\Http\Controllers\FormSubmissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Form submission endpoint - placed here to avoid the web middleware group
// This allows cross-domain submissions without CSRF tokens
// See: https://laravel.com/docs/12.x/csrf#csrf-excluding-uris
Route::post('/post/{hash}', [FormSubmissionController::class, 'submit']);

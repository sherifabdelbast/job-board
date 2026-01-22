<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'message' => 'Welcome to the Job Board API'
    ]);
});

require __DIR__ . '/auth.php';

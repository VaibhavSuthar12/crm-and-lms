<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app'     => 'CRM Backend API',
        'version' => '1.0.0',
        'docs'    => '/api/dashboard',
    ]);
});

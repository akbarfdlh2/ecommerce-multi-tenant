<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app'     => config('app.name'),
        'version' => '1.0.0',
        'status'  => 'running',
    ]);
});

Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
});

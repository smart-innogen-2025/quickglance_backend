<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Shortcut,
    App\Http\Controllers\ShortcutController;


Route::prefix('shortcut')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ShortcutController::class, 'publicShortcuts']);
    Route::get('/personal', [ShortcutController::class, 'personalShortcuts']);
    Route::post('/', [ShortcutController::class, 'store']);
    Route::delete('/{id}', [ShortcutController::class, 'destroy']);
});
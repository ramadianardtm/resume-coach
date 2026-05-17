<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — all protected by Sanctum session auth
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'throttle:60,1'])->group(function () {

    // Chat — send a message and get AI reply
    Route::post('/chat', [ChatController::class, 'send'])->name('api.chat.send');

    // Chat — get full session history (for reload recovery)
    Route::get('/chat/session/{sessionId}', [ChatController::class, 'history'])->name('api.chat.history');

});
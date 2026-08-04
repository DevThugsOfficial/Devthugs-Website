<?php

use App\Http\Controllers\Api\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::prefix('chatbot')->group(function () {
    Route::post('/message', [ChatbotController::class, 'sendMessage']);
    Route::get('/session/{token}/messages', [ChatbotController::class, 'getMessages']);
});

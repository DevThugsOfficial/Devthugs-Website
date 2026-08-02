<?php

use App\Http\Controllers\Api\ChatbotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Chatbot routes
Route::prefix('chatbot')->group(function () {
    Route::post('/message', [ChatbotController::class, 'sendMessage']);
    Route::get('/session/{token}/messages', [ChatbotController::class, 'getMessages']);
});

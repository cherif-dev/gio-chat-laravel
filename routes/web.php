<?php

use GenitIo\Chat\Http\Controllers\ContactController;
use GenitIo\Chat\Http\Controllers\ConversationApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('chat')->name('chat.')->group(function () {
    Route::post('/contact', [ContactController::class, 'create'])->name('contact.create');
    Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
    Route::put('/contact', [ContactController::class, 'update'])->name('contact.update');
    Route::post('/contact/sync', [ContactController::class, 'sync'])->name('contact.sync');
    Route::delete('/contact', [ContactController::class, 'destroy'])->name('contact.destroy');

    // Conversation routes
    Route::prefix('{contact:contact_id}/{participant}')->group(function () {
        Route::get('/', [ConversationApiController::class, 'getConversation'])->name('conversation.get');
    });
});
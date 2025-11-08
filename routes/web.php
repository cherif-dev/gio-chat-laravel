<?php

use GenitIo\Chat\Http\Controllers\ChatController;
use GenitIo\Chat\Http\Controllers\ContactController;
use GenitIo\Chat\Http\Controllers\ConversationController;
use GenitIo\Chat\Http\Controllers\GenitIoController;
use GenitIo\Chat\Http\Controllers\MassageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('genit-io-chat/{project}')->name('genit-io-chat.')->group(function () {
    Route::get('/config', [GenitIoController::class, 'config'])->name('config.get');


    Route::post('/contact', [ContactController::class, 'create'])->name('contact.create');
    Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
    Route::put('/contact', [ContactController::class, 'update'])->name('contact.update');
    Route::post('/contact/sync', [ContactController::class, 'sync'])->name('contact.sync');
    Route::delete('/contact', [ContactController::class, 'destroy'])->name('contact.destroy');



    Route::prefix('contacts')->group(function () {
        Route::post('/', [ContactController::class, 'store']);
        Route::get('/{contact:contact_id}', [ContactController::class, 'show']);
        Route::put('/{contact:contact_id}', [ContactController::class, 'update']);
        Route::delete('/{contact:contact_id}', [ContactController::class, 'destroy']);
        Route::post('/{contact:contact_id}/approve', [ContactController::class, 'approve']);
        Route::post('/{contact:contact_id}/reject', [ContactController::class, 'reject']);
        Route::post('/{contact:contact_id}/online', [ContactController::class, 'updateOnlineStatus']);
        // Conversations
        Route::prefix('/{contact:contact_id}/conversations')->group(function () {
            Route::get('/', [ConversationController::class, 'index']);
            Route::post('/', [ConversationController::class, 'store']);
            Route::get('/{conversation}', [ConversationController::class, 'show']);
            Route::put('/{conversation}', [ConversationController::class, 'update']);
            Route::delete('/{conversation}', [ConversationController::class, 'destroy']);
            Route::post('/{conversation}/assign', [ConversationController::class, 'assign']);
            Route::post('/{conversation}/unassign', [ConversationController::class, 'unassign']);
            Route::post('/{conversation}/status', [ConversationController::class, 'updateStatus']);
            Route::post('/{conversation}/pin', [ConversationController::class, 'pin']);
            Route::post('/{conversation}/unpin', [ConversationController::class, 'unpin']);
            Route::post('/{conversation}/star', [ConversationController::class, 'star']);
            Route::post('/{conversation}/unstar', [ConversationController::class, 'unstar']);
            Route::post('/{conversation}/archive', [ConversationController::class, 'archive']);
            Route::post('/{conversation}/unarchive', [ConversationController::class, 'unarchive']);
        });
    });
    // Chat routes
    Route::prefix('chat')->group(function () {
        // Messages
        Route::prefix('/{conversation:uuid}/messages')->group(function () {
            Route::get('/', [MassageController::class, 'index']);
            Route::post('/', [MassageController::class, 'store']);
            Route::get('/{message:uuid}', [MassageController::class, 'show']);
            Route::put('/{message:uuid}', [MassageController::class, 'update']);
            Route::delete('/{message:uuid}', [MassageController::class, 'destroy']);
            Route::post('/{message:uuid}/read', [MassageController::class, 'markAsRead']);
            Route::post('/{message:uuid}/delivered', [MassageController::class, 'markAsDelivered']);
        });
        Route::get('/{contact:uuid}/{participant:uuid}', [ChatController::class, 'get_or_create_conversation']);
    });
    // Conversation routes
    Route::prefix('{contact:contact_id}/{participant}')->group(function () {
        Route::get('/', [ConversationController::class, 'getConversation'])->name('conversation.get');
    });
});

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Api\V1\Controllers\ChatUsers\ChatUserListController;
use App\Http\Api\V1\Controllers\ChatUsers\ChatUserCreateController;
use App\Http\Api\V1\Controllers\ChatUsers\ChatUserShowController;
use App\Http\Api\V1\Controllers\ChatUsers\ChatUserUpdateController;
use App\Http\Api\V1\Controllers\ChatUsers\ChatUserDeleteController;

use App\Http\Api\V1\Controllers\ChatMessages\ChatMessageListController;
use App\Http\Api\V1\Controllers\ChatMessages\ChatMessageCreateController;
use App\Http\Api\V1\Controllers\ChatMessages\ChatMessageShowController;

use App\Http\Api\V1\Controllers\Subscriptions\SubscriptionListController;
use App\Http\Api\V1\Controllers\Subscriptions\SubscriptionCreateController;
use App\Http\Api\V1\Controllers\Subscriptions\SubscriptionShowController;
use App\Http\Api\V1\Controllers\Subscriptions\SubscriptionUpdateController;
use App\Http\Api\V1\Controllers\Subscriptions\SubscriptionDeleteController;

use App\Http\Api\V1\Controllers\Webhook\WebhookTelegram;
use App\Http\Api\V1\Controllers\Webhook\WebhookWhatsapp;
use App\Http\Api\V1\Controllers\Webhook\WebhookPaymentNotification;

// Chat Users routes
Route::prefix('chat-users')->group(function () {
    Route::get('/', ChatUserListController::class);
    Route::post('/', ChatUserCreateController::class);
    Route::get('/{chatUser}', ChatUserShowController::class);
    Route::put('/{chatUser}', ChatUserUpdateController::class);
    Route::delete('/{chatUser}', ChatUserDeleteController::class);
});

// Chat Messages routes (only index, store, show)
Route::prefix('chat-messages')->group(function () {
    Route::get('/', ChatMessageListController::class);
    Route::post('/', ChatMessageCreateController::class);
    Route::get('/{chatMessage}', ChatMessageShowController::class);
});

// Subscriptions routes
Route::prefix('subscriptions')->group(function () {
    Route::get('/', SubscriptionListController::class);
    Route::post('/', SubscriptionCreateController::class);
    Route::get('/{subscription}', SubscriptionShowController::class);
    Route::put('/{subscription}', SubscriptionUpdateController::class);
    Route::delete('/{subscription}', SubscriptionDeleteController::class);

    // Webhook endpoint to receive payment notifications
});

Route::post('/webhook/messages/telegram', WebhookTelegram::class);
Route::post('/webhook/messages/whatsapp', WebhookWhatsapp::class);
Route::post('/subscriptions/webhook/payment', WebhookPaymentNotification::class);

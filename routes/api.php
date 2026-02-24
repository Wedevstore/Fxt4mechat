<?php

use App\Addons\EmiroSync\Controllers\Client\ProductTemplateController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Api\Client\AuthController;
use App\Http\Controllers\Api\Client\ContactNoteController;
use App\Http\Controllers\Api\Client\ContactTagController;
use App\Http\Controllers\Api\Client\MessageController;
use App\Http\Controllers\Api\Client\TeamController;
use App\Http\Controllers\Api\Client\Telegram\CampaignController as TelegramCampaignController;
use App\Http\Controllers\Api\Client\Telegram\ContactController as TelegramContactController;
use App\Http\Controllers\Api\Client\Telegram\GroupController;
use App\Http\Controllers\Api\Client\TicketController;
use App\Http\Controllers\Api\Client\Whatsapp\BotRepliesController;
use App\Http\Controllers\Api\Client\Whatsapp\CampaignController as WhatsappCampaignController;
use App\Http\Controllers\Api\Client\Whatsapp\ContactController;
use App\Http\Controllers\Api\Client\Whatsapp\ContactListController;
use App\Http\Controllers\Api\Client\Whatsapp\SegmentController;
use App\Http\Controllers\Api\Client\Whatsapp\TemplateController;
use App\Http\Controllers\Api\Setting\CountryController;
use App\Http\Controllers\Api\Setting\CurrencyController;
use App\Http\Controllers\Api\Setting\DepartmentController;
use App\Http\Controllers\Api\Setting\TimezoneController;
use App\Http\Controllers\Client\UserController;
use App\Addons\EmiroSync\Controllers\Shopify\ShopifyProductController;
use App\Addons\EmiroSync\Controllers\BigCommerce\BigCommerceProductController;
use App\Addons\EmiroSync\Controllers\Client\OrderController;
use App\Addons\EmiroSync\Controllers\Client\StripePaymentController;
use Illuminate\Support\Facades\Route;
use Pusher\Pusher;

Route::group(['prefix' => localeRoutePrefix().'/api'], function () {
    Route::middleware(['CheckApiKey'])->group(function () {

        Route::get('country/list', [CountryController::class, 'index']);
        Route::get('currency/list', [CurrencyController::class, 'index']);
        Route::get('department/list', [DepartmentController::class, 'index']);
        Route::get('timezone/list', [TimezoneController::class, 'index']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('jwt.verify')->group(function () {

            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('profile', [AuthController::class, 'profile']);
            Route::post('profile-update', [AuthController::class, 'profileUpdate']);
            //------------Whatsapp start----------//
            //Contact api
            Route::get('contact/details', [ContactController::class, 'contactDetails']);
            Route::get('whatsapp-contact', [ContactController::class, 'index']);
            Route::post('whatsapp-contact-store', [ContactController::class, 'store']);
            Route::post('whatsapp-contact-update/{id}', [ContactController::class, 'store']);
            //Contact-list api
            Route::get('whatsapp-contact-list', [ContactListController::class, 'index']);
            Route::post('whatsapp-contact-list-store', [ContactListController::class, 'store']);
            Route::post('whatsapp-contact-list-update/{id}', [ContactListController::class, 'store']);
            //Segment api
            Route::get('whatsapp-segment', [SegmentController::class, 'index']);
            Route::post('whatsapp-segment-store', [SegmentController::class, 'store']);
            Route::post('whatsapp-segment-update/{id}', [SegmentController::class, 'store']);
            //Bot Replies api
            Route::get('whatsapp-bot-replies', [BotRepliesController::class, 'index']);
            Route::post('whatsapp-bot-replies-store', [BotRepliesController::class, 'store']);
            Route::post('whatsapp-bot-replies-update/{id}', [BotRepliesController::class, 'store']);

            //Campaign api
            Route::get('whatsapp-campaign', [WhatsappCampaignController::class, 'index']);
            Route::post('whatsapp-campaign-store', [WhatsappCampaignController::class, 'store']);
            Route::post('whatsapp-campaign-update/{id}', [WhatsappCampaignController::class, 'submitCampaign']);

            Route::get('whatsapp-template', [TemplateController::class, 'index']);

            //Group api
            Route::get('telegram-group', [GroupController::class, 'index']);
            //Contact api
            Route::get('telegram-contact', [TelegramContactController::class, 'allContact']);
            //Campaign api
            Route::get('telegram-campaign', [TelegramCampaignController::class, 'allCampaign']);
            Route::post('telegram-campaign-store', [TelegramCampaignController::class, 'submitCampaign']);
            Route::post('telegram-campaign-update/{id}', [TelegramCampaignController::class, 'submitCampaign']);
            //------------Telegram end----------//
            //Ticket api
            Route::get('ticket', [TicketController::class, 'index']);
            Route::post('ticket-store', [TicketController::class, 'createTicket']);
            Route::post('ticket-reply', [TicketController::class, 'replyTicket']);
            Route::get('ticket-reply-edit/{id}', [TicketController::class, 'replyEdit']);
            Route::post('ticket-reply-update/{id}', [TicketController::class, 'replyUpdateTicket']);
            //Team api
            Route::get('team', [TeamController::class, 'index']);
            Route::post('team-store', [TeamController::class, 'store']);
            Route::post('team-update/{id}', [TeamController::class, 'store']);


            
            Route::post('send-message', [MessageController::class, 'sendMessage']);

            Route::get('whatsapp/get-template', [MessageController::class, 'getTemplate']);

            //Chat
            Route::get('chat/clear/{contact_id}', [MessageController::class, 'clearChat']);
            Route::get('contact/add-blacklist/{id}', [ContactController::class, 'block']);
            Route::delete('chat/delete/{message_id}', [MessageController::class, 'deleteMessage']);
            Route::get('chat-rooms', [MessageController::class, 'chatRooms']);
            Route::get('message/{chat_room_id}', [MessageController::class, 'chatroomMessages']);

            Route::get('contacts-by-client', [MessageController::class, 'contactByClient']);
            
            Route::get('staffs-by-client', [MessageController::class, 'staffsByClient']);
            Route::post('message/generate-ai-reply', [MessageController::class, 'generateAIReply']);
            Route::post('message/generate-ai-rewrite-reply', [MessageController::class, 'generateAIRewriteReply']);
            Route::get('contact-messages/{contactId}', [MessageController::class, 'getContactMessages']);
            Route::post('send-forward-message', [MessageController::class, 'sendForwardMessage']);
            Route::get('canned-responses', [BotRepliesController::class, 'cannedResponses']);
            Route::post('assign-staff', [MessageController::class, 'assignStaff']);
            Route::get('contacts-details/{id}', [MessageController::class, 'contactDetails']);
            Route::resource('notes', ContactNoteController::class)->only(['index','store','update','destroy']);
            Route::get('tags', [ContactTagController::class, 'index']);
            Route::post('tags', [ContactTagController::class, 'store']);
            Route::get('tags/contact-tags', [ContactTagController::class, 'getContactTags']);
            Route::post('tags/store/contact-tag', [ContactTagController::class, 'storeContactTag']);
            // Route::post('tags/change-status', [ContactTagController::class, 'changeStatus'])->name('tags.change.status');
            Route::get('shared-files/{id}', [MessageController::class, 'sharedFiles']);
            Route::delete('delete-file/{id}', [MessageController::class, 'deleteFile']);
            //Template api
            Route::get('whatsapp-templates', [TemplateController::class, 'allTemplate']);
            Route::post('whatsapp/send-template', [MessageController::class, 'sendTemplate']);

            Route::get('message/clear-chat', [MessageController::class, 'clearChat']);
            Route::get('message/delete', [MessageController::class, 'deleteMessage']);
            Route::get('message/forward', [MessageController::class, 'sendForwardMessage']);

            Route::get('message/list', [MessageController::class, 'contactMessages']);
            //Route::get('send-template', [WhatsappCampaignController::class, 'sendTemplate']);

            Route::post('onesignal-subscription', [UserController::class, 'oneSignalSubscription']);

            Route::post('/generate-stripe-link', [StripePaymentController::class, 'generateLink']);

            Route::get('shopify/products', [ShopifyProductController::class, 'getProducts']);
            Route::get('bigcommerce/products', [BigCommerceProductController::class, 'index']);

            Route::post('/shopify/product/send-template', [ProductTemplateController::class, 'storeAndSend']);
            Route::post('/big-commerce/product/send-template', [ProductTemplateController::class, 'bigCommerceStoreAndSend']);

            Route::get('order', [OrderController::class, 'orderApiData']);

        });
    });

});

Route::get('check-pusher', function () {
    $client_id  = 15;
    $app_key    = setting('pusher_app_key');
    $app_secret = setting('pusher_app_secret');
    $app_id     = setting('pusher_app_id');
    $cluster    = setting('pusher_app_cluster');
    $pusher     = new Pusher($app_key, $app_secret, $app_id, ['cluster' => $cluster]);

    $pusher->trigger("message-received-$client_id", 'App\Events\ReceiveUpcomingMessage', []);
});

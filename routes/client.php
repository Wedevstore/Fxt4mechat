<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Client\AiWriterController;
use App\Http\Controllers\Client\BotReplyController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientRapiwaSettingController;
use App\Http\Controllers\Client\ClientSettingController;
use App\Http\Controllers\Client\ContactAttributeController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\ContactNoteController;
use App\Http\Controllers\Client\ContactsListController;
use App\Http\Controllers\Client\ContactTagController;
use App\Http\Controllers\Client\FlowBuilderController;
use App\Http\Controllers\Client\MessageController;
use App\Http\Controllers\Client\Web\DeviceController;
use App\Http\Controllers\Client\Web\SettingController;
use App\Http\Controllers\Client\SegmentController;
use App\Http\Controllers\Client\SubscriptionController;
use App\Http\Controllers\Client\TeamController;
use App\Http\Controllers\Client\TelegramCampaignController;
use App\Http\Controllers\Client\TelegramSubscriberController;
use App\Http\Controllers\Client\TemplateController;
use App\Http\Controllers\Client\TicketController;
use App\Http\Controllers\Client\UserController;
use App\Http\Controllers\Client\Web\ClientDashboardController as WebClientDashboardController;
use App\Http\Controllers\Client\Web\MessageController as WebMessageController;
use App\Http\Controllers\Client\Web\TemplateController as WebTemplateController;
use App\Http\Controllers\Client\Web\VerifyNumberController;
use App\Http\Controllers\Client\Web\QuickReplyController;
use App\Http\Controllers\Client\Web\WarmUpController;
use App\Http\Controllers\Client\Web\WhatsappCampaignController as WebWhatsappCampaignController;
use App\Http\Controllers\Client\WhatsappCampaignController;
use Illuminate\Support\Facades\Route;

Route::get('available-plans', [SubscriptionController::class, 'availablePlans'])->name('available.plans');
Route::get('pending-subscription', [SubscriptionController::class, 'pendingSubscription'])->name('pending.subscription');
Route::get('upgrade-plan/{id}', [SubscriptionController::class, 'upgradePlan'])->name('upgrade.plan');
Route::post('offline-claim', [SubscriptionController::class, 'offlineClaim'])->name('offline.claim');
Route::post('upgrade-plan/free', [SubscriptionController::class, 'upgradeFreePlan'])->name('upgrade-plan.free');
Route::post('stripe-redirect', [SubscriptionController::class, 'stripeRedirect'])->name('stripe.redirect');
Route::get('stripe-success', [SubscriptionController::class, 'stripeSuccess'])->name('stripe.payment.success');
Route::post('paypal-redirect', [SubscriptionController::class, 'paypalRedirect'])->name('paypal.redirect');
Route::get('paypal-success', [SubscriptionController::class, 'paypalSuccess'])->name('paypal.payment.success');
Route::post('paddle-redirect', [SubscriptionController::class, 'paddleRedirect'])->name('paddle.redirect');
Route::match(['get', 'post'], 'paddle-success', [SubscriptionController::class, 'paddleSuccess'])->name('paddle.payment.success');
Route::post('razor_pay-redirect', [SubscriptionController::class, 'razorPayRedirect'])->name('razor.pay.redirect');
Route::match(['post', 'get'], 'client/razor_pay-success', [SubscriptionController::class, 'razorPaySuccess'])->name('razor.pay.payment.success');
Route::post('mercadopago-redirect', [SubscriptionController::class, 'mercadopagoRedirect'])->name('mercadopago.redirect');
Route::match(['post', 'get'], 'mercadopago-success', [SubscriptionController::class, 'mercadopagoSuccess']);
Route::get('back-to-admin', [AuthenticatedSessionController::class, 'back_to_admin'])->name('back.to.admin');

Route::group(['prefix' => localeRoutePrefix().'/client', 'middleware' => 'subscriptionCheck'], function () {
    // Subscription Routes
    Route::get('my-subscription', [SubscriptionController::class, 'mySubscription'])->name('my.subscription');
    // Dashboard Routes
    Route::get('dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    // WhatsApp Settings Routes
    Route::get('whatsapp-settings', [ClientSettingController::class, 'whatsAppSettings'])->name('whatsapp.settings');
    Route::post('whatsApp/settings/update', [ClientSettingController::class, 'whatsAppSettingUpdate'])->name('whatsapp.settings.update');
    Route::post('whatsapp-settings/sync/{id}', [ClientSettingController::class, 'whatsAppsync'])->name('whatsapp-settings.sync');
    Route::post('whatsApp/settings/remove-token/{id}', [ClientSettingController::class, 'removeWhatsAppToken'])->name('whatsAppSettings.remove-token');
    // Rapiwa Whatsapp
    Route::get('rapiwa-settings', [ClientRapiwaSettingController::class, 'rapiwaSettings'])->name('rapiwa.settings');
    Route::post('rapiwa/settings/update', [ClientRapiwaSettingController::class, 'rapiwaSettingUpdate'])->name('rapiwa.settings.update');
    Route::post('rapiwa/settings/remove-token/{id}', [ClientRapiwaSettingController::class, 'removeRapiwaToken'])->name('rapiwaSettings.remove-token');
    // Telegram Settings Routes
    Route::get('telegram-settings', [ClientSettingController::class, 'telegramSettings'])->name('telegram.settings');
    Route::post('telegram/settings', [ClientSettingController::class, 'telegramUpdate'])->name('settings.telegram.update');
    Route::post('telegram/settings/remove-token/{id}', [ClientSettingController::class, 'removeTelegramToken'])->name('settings.remove-token');
    Route::post('telegram/settings/sync/{id}', [ClientSettingController::class, 'telegramSettingsync'])->name('telegram.settings.sync');
    // General Settings Routes
    Route::get('general-settings', [ClientSettingController::class, 'generalSettings'])->name('general.settings');
    Route::post('general-settings/{id}', [ClientSettingController::class, 'updateGeneralSettings'])->name('general.settings.update');
    Route::get('api', [ClientSettingController::class, 'api'])->name('settings.api');
    Route::post('api', [ClientSettingController::class, 'update_api'])->name('settings.api.update');
    Route::post('ai_reply/status-update', [ClientSettingController::class, 'AIReplyStatus'])->name('setting.ai-reply.status-update');
    // Billing Routes
    Route::get('billing/details', [ClientSettingController::class, 'billingDetails'])->name('billing.details');
    Route::post('billing/details/store/{id}', [ClientSettingController::class, 'storeBillingDetails'])->name('billing.details.store');
    // Chat Routes
    Route::get('chat', [MessageController::class, 'index'])->name('chat.index');
    Route::get('chat/clear/{contact_id}', [MessageController::class, 'clearChat'])->name('chat.clear');
    Route::delete('chat/delete/{message_id}', [MessageController::class, 'deleteMessage'])->name('chat.delete');
    // Profile Routes
    Route::get('profile', [ClientDashboardController::class, 'profile'])->name('profile.index');
    Route::patch('update', [ClientDashboardController::class, 'profileUpdate'])->name('profile.update');
    Route::get('password-change', [ClientDashboardController::class, 'passwordChange'])->name('profile.password-change');
    Route::post('password-update', [ClientDashboardController::class, 'passwordUpdate'])->name('profile.password-update');

    Route::get('whatsapp/overview', [WhatsappCampaignController::class, 'overview'])->name('whatsapp.overview');
    Route::get('whatsapp/campaigns', [WhatsappCampaignController::class, 'index'])->name('whatsapp.campaigns.index');

    Route::middleware(['whatsapp.connected'])->group(function () {
        //templates route
        Route::get('telegram/templates', [TemplateController::class, 'getTelegramTemplate'])->name('telegram.templates.index');
        Route::get('templates', [TemplateController::class, 'index'])->name('templates.index');
        Route::get('template/load-templete', [TemplateController::class, 'loadTemplate'])->name('templates.load-templete');
        Route::get('template/create', [TemplateController::class, 'create'])->name('template.create');
        Route::post('template/store', [TemplateController::class, 'store'])->name('template.store');
        Route::get('template/get/{id}', [TemplateController::class, 'getTemplateByID'])->name('template.get');
        Route::get('template/sync/{id}', [TemplateController::class, 'syncTemplateByID'])->name('template.sync');
        Route::post('template/delete/{id}', [TemplateController::class, 'delete'])->name('template.delete');
        Route::get('template/edit/{id}', [TemplateController::class, 'edit'])->name('template.edit');
        Route::post('template/update/{id}', [TemplateController::class, 'update'])->name('template.update');
        //campaigns route
        Route::get('whatsapp/campaign/create', [WhatsappCampaignController::class, 'create'])->name('whatsapp.campaign.create');
        Route::post('whatsapp/campaign/store', [WhatsappCampaignController::class, 'store'])->name('whatsapp.campaign.store');
        Route::post('whatsapp/contact-template/store', [WhatsappCampaignController::class, 'storeContactTemplate'])->name('whatsapp.contact.template.store');
        Route::get('whatsapp/campaigns/{id}/view', [WhatsappCampaignController::class, 'view'])->name('whatsapp.campaigns.view');
        Route::post('whatsapp/campaigns/status/update/{id}', [WhatsappCampaignController::class, 'statusUpdate'])->name('whatsapp.campaigns.status.update');
        Route::post('whatsapp/campaigns/filter', [WhatsappCampaignController::class, 'filterData'])->name('whatsapp.campaigns.filter');
        Route::post('whatsapp/campaign/resend', [WhatsappCampaignController::class, 'resend'])->name('whatsapp.campaign.resend');
        Route::get('send-template', [WhatsappCampaignController::class, 'sendTemplate'])->name('send.template');
        Route::get('whatsapp/campaign/count-contact', [WhatsappCampaignController::class, 'campaignCountContact'])->name('whatsapp.campaign.count-contact');
    });

    Route::get('telegram/campaigns', [TelegramCampaignController::class, 'index'])->name('telegram.campaigns.index');
    Route::get('telegram/groups', [TelegramCampaignController::class, 'groups'])->name('groups.index');
    Route::get('telegram/overview', [TelegramCampaignController::class, 'overview'])->name('telegram.overview');

    Route::middleware(['telegram.connected'])->group(function () {
        Route::get('telegram/campaign/create', [TelegramCampaignController::class, 'create'])->name('telegram.campaign.create');
        Route::post('telegram/campaign/store', [TelegramCampaignController::class, 'store'])->name('telegram.campaign.store');
        Route::get('telegram/campaigns/{id}/view', [TelegramCampaignController::class, 'view'])->name('telegram.campaigns.view');
        Route::post('telegram/campaigns/status/update/{id}', [TelegramCampaignController::class, 'statusUpdate'])->name('telegram.campaigns.status.update');
    });
    //segments route
    Route::get('segments', [SegmentController::class, 'index'])->name('segments.index');
    Route::post('segment/store', [SegmentController::class, 'store'])->name('segment.store');
    Route::get('segment/edit/{id}', [SegmentController::class, 'edit'])->name('segment.edit');
    Route::post('segment/update/{id}', [SegmentController::class, 'update'])->name('segment.update');
    Route::delete('segment/delete/{id}', [SegmentController::class, 'delete'])->name('segment.delete');
    Route::get('segments-list', [ContactController::class, 'segments'])->name('segment.list');

    Route::get('contact-attributes/list', [ContactAttributeController::class, 'index'])->name('contact-attributes.index');
    Route::get('contact-attribute/create', [ContactAttributeController::class, 'create'])->name('contact-attributes.create');
    Route::post('contact-attribute/store', [ContactAttributeController::class, 'store'])->name('contact-attributes.store');
    Route::get('contact-attribute/edit/{id}', [ContactAttributeController::class, 'edit'])->name('contact-attributes.edit');
    Route::post('contact-attribute/update/{id}', [ContactAttributeController::class, 'update'])->name('contact-attributes.update');
    Route::delete('contact-attribute/delete/{id}', [ContactAttributeController::class, 'delete'])->name('contact-attributes.delete');

    //contacts route
    Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('contact/create', [ContactController::class, 'create'])->name('contact.create');
    Route::post('contact/store', [ContactController::class, 'store'])->name('contact.store');
    Route::get('contact/edit/{id}', [ContactController::class, 'edit'])->name('contact.edit');
    Route::post('contact/update/{id}', [ContactController::class, 'update'])->name('contact.update');
    Route::delete('contact/delete/{id}', [ContactController::class, 'delete'])->name('contact.delete');
    Route::post('contact/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contact.bulk-delete');
    Route::get('contact/view/{id}', [ContactController::class, 'view'])->name('contact.view');
    Route::post('contact/update-details/{id}', [ContactController::class, 'updateDetails'])->name('contact.update-details');

    Route::post('contact/download', [ContactController::class, 'getContactDownload'])->name('contact.download');

    Route::get('telegram/subscribers/list', [TelegramSubscriberController::class, 'index'])->name('telegram.subscribers.index');
    Route::post('telegram/subscriber/sync/{id}', [TelegramSubscriberController::class, 'telegramSubscribersync'])->name('telegram.subscriber.sync');
    Route::post('telegram/subscriber/add-blacklist/{id}', [TelegramSubscriberController::class, 'addBlacklist'])->name('telegram.subscriber.add-blacklist');
    Route::post('telegram/subscriber/remove-blacklist/{id}', [TelegramSubscriberController::class, 'removeBlacklist'])->name('telegram.subscriber.remove-blacklist');
    Route::post('telegram/subscriber/delete/{id}', [TelegramSubscriberController::class, 'delete'])->name('telegram.subscriber.delete');

    Route::post('/contact/blacklist', [ContactController::class, 'addBlacklist'])->name('contact.blacklist');
    Route::post('/remove-blacklist', [ContactController::class, 'removeBlacklist'])->name('remove.blacklist');
    Route::get('/contact/add-blacklist/{id}', [ContactController::class, 'block'])->name('contact.add_blacklist');
    Route::get('/contact/remove-blacklist/{id}', [ContactController::class, 'unblock'])->name('contact.remove_blacklist');

    Route::post('/add-list', [ContactController::class, 'addList'])->name('add.list');
    Route::post('/remove-list', [ContactController::class, 'removeList'])->name('remove.list');
    Route::post('/add-segment', [ContactController::class, 'addSegment'])->name('add.segment');
    Route::post('/remove-segment', [ContactController::class, 'removeSegment'])->name('remove.segment');
    Route::post('contact/parse-csv', [ContactController::class, 'parseCSV'])->name('contact.parse.csv');
    Route::post('contact/confirm-upload', [ContactController::class, 'confirmUpload'])->name('contact.confirm-upload');
    Route::get('contact/import', [ContactController::class, 'createImport'])->name('contact.import');

    //contacts list route
    Route::get('contacts-list', [ContactsListController::class, 'index'])->name('contacts_list.index');
    Route::post('contacts-store', [ContactsListController::class, 'store'])->name('contacts_list.store');
    Route::get('contacts-list/edit/{id}', [ContactsListController::class, 'edit'])->name('list.edit');
    Route::post('contact-list/update/{id}', [ContactsListController::class, 'update'])->name('list.update');
    Route::get('contacts-export', [ContactsListController::class, 'downloadSample'])->name('contacts.export');
    Route::post('contacts-Imports-store', [ContactsListController::class, 'importStore'])->name('store.Imports');
    Route::delete('contacts-list/delete/{id}', [ContactsListController::class, 'delete'])->name('list.delete');
    Route::get('contacts-lists', [ContactsListController::class, 'contactList'])->name('contacts-lists');
    //tickets route
    Route::resource('tickets', TicketController::class)->except(['edit', 'destroy']);
    Route::get('ticket/update/{id}', [TicketController::class, 'update'])->name('ticket.update');
    Route::post('ticket-reply', [TicketController::class, 'reply'])->name('ticket.reply');
    Route::get('ticket-reply-edit/{id}', [TicketController::class, 'replyEdit'])->name('ticket.reply.edit');
    Route::post('ticket-reply-update/{id}', [TicketController::class, 'replyUpdate'])->name('ticket.reply.update');
    Route::delete('ticket-reply-delete/{id}', [TicketController::class, 'replyDelete'])->name('ticket.reply.delete');
    //Bot Reply
    Route::resource('bot-reply', BotReplyController::class);
    //team route
    Route::get('team-list', [TeamController::class, 'index'])->name('team.index');
    Route::get('team/create', [TeamController::class, 'create'])->name('team.create');
    Route::post('team/store', [TeamController::class, 'store'])->name('team.store');
    Route::get('team/edit/{id}', [TeamController::class, 'edit'])->name('team.edit');
    Route::put('team/update/{id}', [TeamController::class, 'update'])->name('team.update');

    //AI writer
    Route::get('ai-writer', [AiWriterController::class, 'index'])->name('ai.writer');
    Route::post('ai-writer', [AiWriterController::class, 'saveAiSetting'])->name('ai.writer');
    Route::get('ai-writer-setting', [ClientSettingController::class, 'aiWriterSetting'])->name('ai_writer.setting');
    Route::post('generated-ai-content', [AiWriterController::class, 'generateContent'])->name('ai.content');

    //user route
    Route::get('users/verified/{verify}', [UserController::class, 'instructorVerified'])->name('users.verified');
    Route::get('users/ban/{id}', [UserController::class, 'instructorBan'])->name('users.ban');
    Route::post('user-status', [UserController::class, 'statusChange'])->name('users.status');
    Route::delete('users/delete/{id}', [UserController::class, 'instructorDelete'])->name('users.delete');

    Route::post('onesignal-subscription', [UserController::class, 'oneSignalSubscription'])->name('onesignal');
    Route::get('onesignal-notification', [UserController::class, 'oneSignalNotification'])->name('onesignal.notification');
    Route::delete('stop-recurring/{id}', [SubscriptionController::class, 'stopRecurring'])->name('stop.recurring');
    Route::delete('enable-recurring/{id}', [SubscriptionController::class, 'enableRecurring'])->name('enable.recurring');
    Route::delete('cancel-subscription/{id}', [SubscriptionController::class, 'cancelSubscription'])->name('cancel.subscription');

    Route::resource('flow-builders', FlowBuilderController::class)->except(['update']);
    Route::post('flow-builders/{id}', [FlowBuilderController::class, 'update'])->name('flow-builders.update');
    Route::post('flow-builders/{id}', [FlowBuilderController::class, 'update'])->name('flow-builders.update');
    Route::post('upload-files', [FlowBuilderController::class, 'uploadFile'])->name('upload.file');
    Route::get('flow-builder/list', [FlowBuilderController::class, 'getFlowBuilderList'])->name('flow-builder.list');
});
Route::group(['prefix' => 'client', 'middleware' => 'subscriptionCheck'], function () {
    Route::get('contacts-by-client', [ContactController::class, 'contactByClient'])->name('contacts.by.client');
    Route::get('staffs-by-client', [MessageController::class, 'staffsByClient'])->name('staffs-by-client');
    Route::get('chat-rooms', [MessageController::class, 'chatRooms'])->name('chat.rooms');
    Route::get('message/{chat_room_id}', [MessageController::class, 'chatroomMessages'])->name('chatroom.messages');
    Route::post('send-message', [MessageController::class, 'sendMessage'])->name('message.sent');
    //    Route::post('send-message', [MessageController::class, 'sendMessage'])->name('message.sent');
    Route::post('message/generate-ai-reply', [MessageController::class, 'generateAIReply'])->name('message.generate-ai-reply');
    Route::post('message/generate-ai-rewrite-reply', [MessageController::class, 'generateAIRewriteReply'])->name('message.generate-ai-rewrite-reply');
    Route::get('contact-messages/{contactId}', [MessageController::class, 'getContactMessages'])->name('contact.messages');
    Route::post('send-forward-message', [MessageController::class, 'sendForwardMessage'])->name('forward.message');
    Route::get('canned-responses', [BotReplyController::class, 'cannedResponses'])->name('canned.responses');
    Route::post('assign-staff', [MessageController::class, 'assignStaff'])->name('assign.staff');
    Route::get('contacts-details/{id}', [MessageController::class, 'contactDetails'])->name('contacts.details');
    Route::resource('notes', ContactNoteController::class)->only(['store', 'destroy']);
    Route::get('tags', [ContactTagController::class, 'index'])->name('tags');
    Route::post('tags', [ContactTagController::class, 'store'])->name('tags.store');
    Route::get('tags/contact-tags', [ContactTagController::class, 'getContactTags'])->name('contact-tags');
    Route::post('tags/store/contact-tag', [ContactTagController::class, 'storeContactTag'])->name('tags.store.contact-tag');
    // Route::post('tags/change-status', [ContactTagController::class, 'changeStatus'])->name('tags.change.status');
    Route::get('shared-files/{id}', [MessageController::class, 'sharedFiles'])->name('shared.files');
    Route::delete('delete-file/{id}', [MessageController::class, 'deleteFile'])->name('delete.file');
    Route::get('whatsapp-templates', [TemplateController::class, 'whatsappTemplates'])->name('whatsapp.templates');
    Route::get('send-template', [WhatsappCampaignController::class, 'sendTemplate'])->name('send.template');

    // start Web
    Route::get('web/overview', [WebClientDashboardController::class, 'index'])->name('web.overview');

    Route::get('web/devices', [DeviceController::class, 'index'])->name('web.devices');
    Route::get('web/devices/{id}/setting', [DeviceController::class, 'deviceSetting'])->name('web.devices.setting');
    Route::get('web/devices/{id}/chat', [DeviceController::class, 'deviceChat'])->name('web.devices.chat.setting');
    Route::get('web/setting', [SettingController::class, 'setting'])->name('web.setting');
    Route::post('web/setting', [SettingController::class, 'whatsAppWebUpdate'])->name('web.setting.update');

    Route::post('web/settings/sync/{id}', [SettingController::class, 'whatsAppWebSync'])->name('web.settings.sync');
    Route::post('web/settings/remove/{id}', [SettingController::class, 'removeWhatsAppWeb'])->name('whatsappwebsettings.remove');

    Route::get('web/templates', [WebTemplateController::class, 'index'])->name('web.templates.index');
    Route::get('web/template/load-templete', [WebTemplateController::class, 'loadTemplate'])->name('web.templates.load-templete');
    Route::get('web/template/create', [WebTemplateController::class, 'create'])->name('web.template.create');
    Route::post('web/template/store', [WebTemplateController::class, 'store'])->name('web.template.store');
    Route::get('web/template/get/{id}', [WebTemplateController::class, 'getTemplateByID'])->name('web.template.get');
    Route::post('web/template/delete/{id}', [WebTemplateController::class, 'delete'])->name('web.template.delete');
    Route::get('web/template/edit/{id}', [WebTemplateController::class, 'edit'])->name('web.template.edit');
    Route::post('web/template/update/{id}', [WebTemplateController::class, 'update'])->name('web.template.update');

    //campaigns route
    Route::get('web/whatsapp/campaigns', [WebWhatsappCampaignController::class, 'index'])->name('web.whatsapp.campaigns.index');
    Route::get('web/whatsapp/campaign/create', [WebWhatsappCampaignController::class, 'create'])->name('web.whatsapp.campaign.create');
    Route::post('web/whatsapp/campaign/store', [WebWhatsappCampaignController::class, 'store'])->name('web.whatsapp.campaign.store');
    Route::get('web/whatsapp/campaign/count-contact', [WhatsappCampaignController::class, 'campaignCountContact'])->name('web.whatsapp.campaign.count-contact');
    Route::get('web/whatsapp/campaigns/{id}/view', [WebWhatsappCampaignController::class, 'view'])->name('web.whatsapp.campaigns.view');
    Route::post('web/whatsapp/campaigns/status/update/{id}', [WebWhatsappCampaignController::class, 'statusUpdate'])->name('web.whatsapp.campaigns.status.update');
    Route::post('web/whatsapp/campaign/resend', [WebWhatsappCampaignController::class, 'resend'])->name('web.whatsapp.campaign.resend');

    //verify-number
    Route::get('web/whatsapp/verify-number', [VerifyNumberController::class, 'index'])->name('web.whatsapp.verify-number.index');
    Route::get('web/whatsapp/verify-number/create', [VerifyNumberController::class, 'create'])->name('web.whatsapp.verify-number.create');
    Route::post('web/whatsapp/verify-number/store', [VerifyNumberController::class, 'store'])->name('web.whatsapp.verify-number.store');
    Route::get('web/whatsapp/verify-number/{id}/view', [VerifyNumberController::class, 'view'])->name('web.whatsapp.verify-number.view');
    Route::get('web/whatsapp/verify-number/job-run', [VerifyNumberController::class, 'verifyNumberJob'])->name('web.whatsapp.verify-number.job-run');

    // quick reply
    Route::get('web/quick-reply', [QuickReplyController::class, 'index'])->name('web.whatsapp.quick-reply.index');
    Route::get('web/quick-reply/create', [QuickReplyController::class, 'create'])->name('web.whatsapp.quick-reply.create');
    Route::post('web/quick-reply/store', [QuickReplyController::class, 'store'])->name('web.whatsapp.quick-reply.store');
    Route::get('web/quick-reply/edit/{id}', [QuickReplyController::class, 'edit'])->name('web.whatsapp.quick-reply.edit');
    Route::post('web/quick-reply/update/{id}', [QuickReplyController::class, 'update'])->name('web.whatsapp.quick-reply.update');

    // warm up
    Route::get('web/warm-up', [WarmUpController::class, 'index'])->name('web.whatsapp.warm-up.index');
    Route::get('web/warm-up/create', [WarmUpController::class, 'create'])->name('web.whatsapp.warm-up.create');
    Route::post('web/warm-up/store', [WarmUpController::class, 'store'])->name('web.whatsapp.warm-up.store');
    Route::get('web/warm-up/edit/{id}', [WarmUpController::class, 'edit'])->name('web.whatsapp.warm-up.edit');
    Route::post('web/warm-up/update/{id}', [WarmUpController::class, 'update'])->name('web.whatsapp.warm-up.update');
    Route::delete('web/warm-up/delete/{id}', [WarmUpController::class, 'destroy'])->name('web.whatsapp.warm-up.delete');
    Route::post('web/warm-up/status-change', [WarmUpController::class, 'statusChange'])->name('web.whatsapp.warm-up.status-change');
    Route::get('web/warm-up/manage/{id}', [WarmUpController::class, 'manage'])->name('web.whatsapp.warm-up.manage');

    Route::get('web/warm-up-number/create/{id}', [WarmUpController::class, 'warmUpNumberCreate'])->name('web.whatsapp.warm-up-number.create');
    Route::post('web/warm-up-number/store', [WarmUpController::class, 'warmUpNumberStore'])->name('web.whatsapp.warm-up-number.store');

    Route::get('web/warm-up-device/create/{id}', [WarmUpController::class, 'warmUpDeviceCreate'])->name('web.whatsapp.warm-up-device.create');
    Route::post('web/warm-up-device/store', [WarmUpController::class, 'warmUpDeviceStore'])->name('web.whatsapp.warm-up-device.store');

    Route::get('web/run-warmup-job', [WarmUpController::class, 'runNow'])->name('web.whatsapp.warmup.run');

    // admin handle
    Route::post('web/device/store', [DeviceController::class, 'storeDeviceToWeb'])->name('web.device.store');

    Route::post('web/settings/sync/admin-handle/{id}', [SettingController::class, 'whatsAppWebSyncAdminHandle'])->name('web.settings.sync.admin-handle');

    //vue route
    Route::get('web/whatsapp-templates', [WebTemplateController::class, 'whatsappTemplates'])->name('web.whatsapp.templates');
    Route::get('web/send-template', [WebWhatsappCampaignController::class, 'sendTemplate'])->name('web.send.template');
    Route::post('web/whatsapp/contact-template/store', [WebWhatsappCampaignController::class, 'storeContactTemplate'])->name('web.whatsapp.contact.template.store');

    Route::get('web-chat', [WebMessageController::class, 'index'])->name('web.chat.index');
    Route::post('send-web-message', [WebMessageController::class, 'sendMessage'])->name('message.sent');
    Route::get('all/devices', [DeviceController::class, 'allDevices']);
    Route::post('device/active/{id}', [DeviceController::class, 'deviceActive']);

});


Route::get('/check-addons-config', function () {
    $emiroPath = base_path('app/Addons/EmiroSync/config.json');
    $salebotPath = base_path('app/Addons/SaleBotECommerce/config.json');

    return response()->json([
        'emiroSync' => file_exists($emiroPath),
        'saleBotECommerce' => file_exists($salebotPath),
    ]);
});


Route::get('chat-refresh', function () {
    \Illuminate\Support\Facades\Artisan::call('chat:refresh');

    return 'success';
})->name('chat.refresh');

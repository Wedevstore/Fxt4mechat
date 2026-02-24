<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CronController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Webhook\TelegramController;
use App\Http\Controllers\Webhook\WhatsAppRapiwaWebhookController;
use App\Http\Controllers\Webhook\WhatsAppWebhookController;

Route::get('cron-run', [CronController::class, 'manual_run'])->name('cron.run.manually');
Route::get('cron/{key}', [CronController::class, 'index']);
Route::get('whatsapp/webhook/{token}', [WhatsAppWebhookController::class, 'verifyToken'])->name('whatsapp.verify.token');
Route::post('whatsapp/webhook/{token}', [WhatsAppWebhookController::class, 'receiveResponse'])->name('whatsapp.webhook');
// rapiwa webhook
Route::post('whatsapp/web/webhook/{token}', [WhatsAppRapiwaWebhookController::class, 'receiveResponse'])->name('whatsapp.web.webhook');
// APP Webhook For Embadded Signup
Route::any('webhook/whatsapp-webhook', [WhatsAppWebhookController::class, 'verifyWABAToken'])->name('webhook.whatsapp-webhook');
Route::get('message/schedule/send', [WhatsAppWebhookController::class, 'sendScheduleMessage'])->name('message.schedule.send');
Route::get('/logs', [LogController::class, 'showLog'])->name('logs.show')->middleware(['auth', 'verified']);
//Telegram
Route::any('telegram/webhook/{token}', [TelegramController::class, 'receiveResponse'])->name('telegram.webhook');
Route::any('env-editor', function () {
    return redirect('/');
})->where('anything', '.*');
Route::any('env-editor/key', function () {
    return redirect('/');
})->where('anything', '.*');
Route::post('subscribe/store', [HomeController::class, 'subscribeStore'])->name('subscribe.store');
Route::group(['prefix' => localeRoutePrefix(), 'middleware' => 'isInstalled'], function () {
    Route::get('/health',function(){
        return response()->json('OK');
    });
    Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('check.landing.page');
    Route::get('language/{lang}', [HomeController::class, 'changeLanguage'])->name('lang');
    Route::get('cache-clear', [HomeController::class, 'cacheClear'])->name('cache.clear');
    Route::get('page/{link}', [HomeController::class, 'page']);
});
require __DIR__.'/auth.php'; 

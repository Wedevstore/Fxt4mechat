<?php

namespace App\Models;

use App\Addons\EmiroSync\Enums\TypeEnum as EnumsTypeEnum;
use App\Addons\SaleBotECommerce\Enums\TypeEnum as EcommerceEnumsTypeEnum;
use App\Addons\EmiroSync\Models\ClientGatewaySetting;
use App\Addons\SaleBotECommerce\Models\ClientGatewaySetting as EcommerceClientGatewaySetting;
use App\Enums\TypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_secret_key', 'company_name', 'logo', 'timezone', 'slug', 'country_id', 'address', 'status', 'webhook_verify_token', 'api_key', 'stripe_customer_id', 'paddle_customer_id', 'big_commerce_status',
    ];

    protected $casts    = [
        'logo' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function whatsappSetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class, 'client_id')->where('status',1)->latest()->where('type', TypeEnum::WHATSAPP->value);
    }

    public function webSetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class, 'client_id')->where('status',1)->latest()->where('type', TypeEnum::RAPIWA->value);
    }

    public function rapiwaSetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class, 'client_id')->latest()->where('type', TypeEnum::RAPIWA->value);
    }


    public function messengerSetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class,'client_id')->where('status',1)->latest()->where('type',TypeEnum::MESSENGER->value);
    }


    public function bigcommerceSetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class,'client_id')->latest()->where('type',TypeEnum::BIGCOMMERCE->value);
    }


    public function shopifySetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class,'client_id')->latest()->where('type',TypeEnum::SHOPIFY->value);
    }

    public function woocommerceSetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class,'client_id')->latest()->where('type',TypeEnum::WOOCOMMERCE->value);
    }

    public function preference(): HasMany
    {
        return $this->hasMany(ClientSetting::class,'client_id');
    }


    public function stripeSetting(): HasOne
    {
        return $this->hasOne(ClientGatewaySetting::class,'client_id')->where('status',1)->latest()->where('payment_provider',EnumsTypeEnum::STRIPE->value);
    }

    public function stripeEcommerceSetting(): HasOne
    {
        return $this->hasOne(EcommerceClientGatewaySetting::class,'client_id')->latest()->where('payment_provider',EcommerceEnumsTypeEnum::STRIPE->value);
    }


    public function telegramSetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class, 'client_id')->where('status',1)->latest()->where('type', TypeEnum::TELEGRAM->value);
    }


    public function instagramSetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class, 'client_id')->where('status',1)->latest()->where('type', TypeEnum::INSTAGRAM->value);
    }

    public function staff()
    {
        return $this->hasMany(ClientStaff::class, 'client_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function whatsAppContacts(): HasMany
    {
        return $this->hasMany(Contact::class)->where('type', TypeEnum::WHATSAPP);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(BotGroup::class);
    }

    public function totalsubscribers(): HasMany
    {
        return $this->hasMany(GroupSubscriber::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function primaryUser()
    {
        return $this->hasOne(User::class)->where('is_primary',1);
    }

    public function timeZone()
    {
        return $this->belongsTo(Timezone::class, 'timezone');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'client_id')
        ->where('purchase_date', '<=', now())
            ->where('expire_date', '>=', now())
            ->where('status', 1)
            ->latest();
    }

    public function pendingSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'client_id')->where('purchase_date', '<=', now())
            ->where('expire_date', '>=', now())->where('status', 0)->latest();
    }

    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getProfilePicAttribute(): string
    {
        return arrayCheck('image_40x40', $this->user->images) && is_file_exists($this->user->images['image_40x40'], $this->user->images['storage']) ?
            get_media($this->user->images['image_40x40'], $this->user->images['storage']) : static_asset('images/default/user.jpg');
    }

    public function aiCredential(): ?string
    {
        return $this->clientSetting()->value('open_ai_key');
    }

    public function clientSetting(): HasOne
    {
        return $this->hasOne(ClientSetting::class, 'client_id');
    }
}

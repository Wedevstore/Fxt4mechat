<?php
namespace App\Traits;
use App\Traits\CommonTrait;

trait MetaTrait
{
    use SendNotification, CommonTrait;

    public $facebook_api = 'https://graph.facebook.com/v19.0/';


}

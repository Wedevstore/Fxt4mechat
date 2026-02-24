<?php

namespace App\Enums;

enum TypeEnum: string
{

    case WHATSAPP    = 'whatsapp';
    case TELEGRAM    = 'telegram';
    case MESSENGER   = 'messenger';
    case INSTAGRAM   = 'instagram';
    case BIGCOMMERCE = 'bigcommerce';
    case SHOPIFY     = 'shopify';
    case WOOCOMMERCE = 'woocommerce';
    case RAPIWA      = 'rapiwa';
    case WEB         = 'web';
   
}

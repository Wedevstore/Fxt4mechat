<?php

namespace App\Enums;

enum VerifyNumberStatusEnum: string
{
    case PROCESSING   = 'processing';
    case COMPLETED    = 'completed';
    case PAUSED       = 'paused';
    case STOPPED      = 'stopped';

    /**
     * Get all enum values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
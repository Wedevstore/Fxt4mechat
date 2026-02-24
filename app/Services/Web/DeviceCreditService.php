<?php

namespace App\Services\Web;

use App\Models\Device;
use App\Models\CreditTransaction;
use App\Models\DeviceCredit;
use Illuminate\Support\Facades\DB;
use Exception;

class DeviceCreditService
{
    public function deduct(Device $device, string $messageType, string $reference = null)
    {
        $credits = $this->resolveCredits($messageType);

        return DB::transaction(function () use ($device, $credits, $messageType, $reference) {

            $credit = $device->credit()->lockForUpdate()->first();

            if (!$credit) {
                throw new Exception('Device credit account not found');
            }

            if ($credit->used_credits >= $credit->total_credits) {
                throw new \Exception('Insufficient credits');
            }

            $credit->increment('used_credits', $credits);

            CreditTransaction::create([
                'client_id'     => $device->client_id,
                'device_id'     => $device->id,
                'type'          => 'debit',
                'credits'       => $credits,
                'message_type'  => $messageType,
                'reference'     => $reference,
                'balance_after'=> $credit->remaining_credits,
                'description'  => ucfirst($messageType) . ' message sent'
            ]);

            return true;
        });
    }

    protected function resolveCredits(string $messageType): int
    {
        return match ($messageType) {
            'text'      => 1,
            'image',
            'video',
            'audio',
            'document'  => 2,
            default     => 1,
        };
    }
}

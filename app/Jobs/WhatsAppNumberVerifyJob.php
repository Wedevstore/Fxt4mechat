<?php

namespace App\Jobs;

use App\Enums\VerifyNumberStatusEnum;
use App\Models\Contact;
use App\Models\Device;
use App\Models\VerifyNumber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNumberVerifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $verifyNumberId;

    public function __construct(int $verifyNumberId)
    {
        $this->verifyNumberId = $verifyNumberId;
    }


    public function handle()
    {

        $verifyNumber = VerifyNumber::find($this->verifyNumberId);

        if (!$verifyNumber) {
            Log::error("VerifyNumber ID {$this->verifyNumberId} not found.");
            return;
        }

        // $verifyNumber = $this->verifyNumber;

        // --- Ensure contact_ids is an array ---
        $contactIds = $verifyNumber->contact_ids ?? [];
        if (!is_array($contactIds)) {
            $contactIds = json_decode($contactIds, true) ?: [];
        }

        if (empty($contactIds)) {
            Log::warning("VerifyNumber ID {$verifyNumber->id} has no contacts.");
            $verifyNumber->update(['status' => VerifyNumberStatusEnum::COMPLETED->value]);
            return;
        }

        $device = Device::find($verifyNumber->device_id);
        if (!$device || empty($device->whatsapp_session)) {
            $verifyNumber->update(['status' => VerifyNumberStatusEnum::COMPLETED->value]);
            Log::error("Device not found or WhatsApp session missing for VerifyNumber ID {$verifyNumber->id}.");
            return;
        }

        $totalVerified   = 0;
        $totalUnverified = 0;

        foreach ($contactIds as $id) {
            $contact = Contact::find($id);
            if (!$contact) continue;

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $device->whatsapp_session,
                    'Content-Type'  => 'application/json',
                ])->post('https://app.rapiwa.com/api/verify-whatsapp', [
                    'number' => $contact->phone,
                ]);

                $exists = $response->json('data.exists', false);

                $contact->verify_whatsapp = $exists ? 1 : 2;
                $contact->save();

                $exists ? $totalVerified++ : $totalUnverified++;

            } catch (\Throwable $e) {
                Log::error("Error verifying Contact ID {$id}: " . $e->getMessage());

                $contact->verify_whatsapp = 2;
                $contact->save();
                $totalUnverified++;
            }

            sleep(10);
        }

        // --- Update VerifyNumber totals and status ---
        $verifyNumber->update([
            'total_verify'   => $totalVerified,
            'total_unverify' => $totalUnverified,
            'status'         => VerifyNumberStatusEnum::COMPLETED->value,
        ]);

    }

}

<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\WarmupContact;
use App\Models\WhatsAppWarmup;
use App\Models\WhatsAppWarmupMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WhatsAppWarmupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        $warmups = WhatsAppWarmup::where('is_completed', 0)->get();

        foreach ($warmups as $warmup) {
            $device = Device::find($warmup->device_id);

            if (empty($device)) {
                Log::warning("Device............ {$device}");
                continue;
            }

            if (!$device) {
                Log::warning("Device not found for warm-up ID {$warmup->id}");
                continue;
            }

            if (empty($device->whatsapp_session)) {
                Log::warning("Device key missing for device {$device}");
                continue;
            }
            
            $contacts = WarmupContact::where('warmup_id', $warmup->id)
                ->where('client_id', $device->client_id)
                ->where('status', 1)
                ->inRandomOrder()
                ->limit($this->getDailyLimit($warmup->day))
                ->get();

            
            if ($contacts->isEmpty()) {
                Log::info("❌ No contacts found for warm-up ID {$warmup->id}.");
                continue;
            }

            foreach ($contacts as $contact) {
                
                if ($contact->warmup_id != $warmup->id || $contact->client_id != $device->client_id) {
                    Log::warning("Skipping mismatched contact {$contact->phone_number} for warm-up {$warmup->id}");
                    continue;
                }
                try {
                    $message = $this->generateMessage($warmup->day);

                    // Create pending log entry first
                    $history = WhatsAppWarmupMessage::create([
                        'client_id'         => $warmup->client_id,
                        'warmup_id'         => $warmup->id,
                        'device_id'         => $device->id,
                        'warmup_contact_id' => $contact->id,
                        'phone_number'      => $contact->phone_number,
                        'message'           => $message,
                        'status'            => 'pending',
                    ]);

                    // Send message via Rapiwa API
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $device->whatsapp_session,
                        'Content-Type'  => 'application/json',
                    ])->post('https://app.rapiwa.com/api/send-message', [
                        'number'        => $contact->phone_number,
                        'message_type'  => 'text',
                        'message'       => $message,
                    ]);

                    if ($response->successful()) {
                        $history->update([
                            'status'   => 'sent',
                            'response' => $response->body(),
                        ]);
                        Log::info("Warm-up message sent to {$contact->phone_number}");

                    } else {
                        $history->update([
                            'status'   => 'failed',
                            'response' => $response->body(),
                        ]);
                        Log::warning("Failed to send message to {$contact->phone_number}: " . $response->body());
                    }

                    sleep(rand(10, 20));



                } catch (\Throwable $e) {
                    WhatsAppWarmupMessage::create([
                        'client_id'         => $warmup->client_id,
                        'warmup_id'         => $warmup->id,
                        'device_id'         => $device->id,
                        'warmup_contact_id' => $contact->id,
                        'phone_number'      => $contact->phone_number,
                        'message'           => $message ?? 'unknown',
                        'status'            => 'failed',
                        'response'          => $e->getMessage(),
                    ]);

                    Log::error("Warm-up message error for {$contact->phone_number}: {$e->getMessage()}");
                }
            }

            //  Increment warm-up day
            $lastSent = $warmup->last_sent_at ? Carbon::parse($warmup->last_sent_at) : null;

            // Check if we've already processed today
            $today = $now->toDateString();
            $lastSentDate = $lastSent ? $lastSent->toDateString() : null;

            if (is_null($lastSent) || $today !== $lastSentDate) {
                $warmup->day += 1;
                $warmup->last_sent_at = $now;
                // $warmup->messages_sent_today = 0;
                $warmup->save();
                Log::info("Warm-up day incremented for device {$warmup->device_id} to day {$warmup->day}");
            } else {
                $nextIncrement = $lastSent->copy()->addDay();
                $hoursLeft = $now->diffInHours($nextIncrement, false); // false returns negative if past due
                Log::info("Warm-up day not incremented for device {$warmup->device_id}. {$hoursLeft} hours remaining until next increment.");
            }

            //  Complete warm-up after day 10
            if ($warmup->day > 10) {
                $warmup->update(['is_completed' => 1]);
                Log::info("Warm-up completed for device {$device->device_name}");
            }

        }
    }

    /**
     * Get daily message limit based on the current warm-up day.
     */
    private function getDailyLimit(int $day): int
    {
        return match (true) {
            $day <= 2 => 5,
            $day <= 5 => 15,
            $day <= 8 => 30,
            default   => 50,
        };
    }

    /**
     * Generate a random warm-up message template.
     */
    private function generateMessage(int $day): string
    {
        $templates = [
            'Hey! Just testing this number.',
            'Hi there',
            'Good morning',
            'How’s your day going?',
            'Testing this WhatsApp account',
            'Everything working great!',
            'Hope you’re doing awesome',
            'Just another warm-up message',
            'Making sure this account stays active',
            'Checking message delivery'
        ];

        return $templates[array_rand($templates)];
    }

}

<?php
namespace App\Jobs;
use App\Models\Message;
use App\Traits\WhatsAppWebTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWhatsAppWebCampaignSingleContactMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, WhatsAppWebTrait;

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function handle()
    {
        // Call methods from WhatsAppWebTrait
        $this->sendWhatsAppWebCampaignSingleContactMessage($this->message);
    }
}

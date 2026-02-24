<?php
namespace App\Jobs;
use App\Traits\SMSTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendScheduleSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,SMSTrait;

    protected $messageBody;
    protected $mobile;
    protected $template_id;
    protected $smsId;
     public function __construct($messageBody, $mobile,$template_id, $smsId)
    {
        $this->messageBody = $messageBody;
        $this->mobile = $mobile;
        $this->template_id = $template_id;
        $this->smsId = $smsId;
    }

    public function handle()
    {
        $this->send($this->mobile, $this->messageBody,$this->template_id,$this->smsId);
      
    }
}

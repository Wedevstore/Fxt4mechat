<?php
namespace App\Jobs;
use App\Traits\SMSTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckScheduleSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,SMSTrait;

    protected $smsId;
     public function __construct($smsId)
    {
        $this->smsId = $smsId;
    }

    public function handle()
    {
        $this->checkMessageStatusAndUpdate($this->smsId,$provider = '');
      
    }
}

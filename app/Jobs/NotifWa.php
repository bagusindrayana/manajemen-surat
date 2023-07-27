<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifWa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $no;
    public $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($no, $message)
    {
        if (substr($no, 0, 1) == '0') {
            $this->no = '62' . substr($no, 1);
        } else {
            $this->no = $no;
        }
        $this->no = str_replace("-", "", $this->no);

        $this->message = "NOTIFIKASI - " . env("APP_NAME") . "\n
----------------------------------------------------\n
        " . $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //check if first no is 0 and replace with 62


        NotificationHelper::sendWa($this->no,$this->message);
    }
}
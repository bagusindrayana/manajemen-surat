<?php

namespace App\Jobs;

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
    public function __construct($no,$message)
    {   
        if(substr($no,0,1) == '0'){
            $this->no = '62'.substr($no,1);
        } else {
            $this->no = $no;
        }
        
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        //check if first no is 0 and replace with 62
        
       
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', env("WA_API_URL").env("WA_API_KEY"), [
            'form_params' => [
                'id' => $this->no,
                'message' => "NOTIFIKASI - ".env("APP_NAME")."\n
----------------------------------------------------\n
".$this->message,
            ]
        ]);
        Log::info($res->getStatusCode());
        // if((int)$res->getStatusCode() != 200){
        //     throw new Exception("Error Processing Request", 1);
        // }
    }
}

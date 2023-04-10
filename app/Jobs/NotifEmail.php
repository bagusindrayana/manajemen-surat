<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $kontak;
    public $message;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($kontak,$message)
    {   
        $this->kontak = $kontak;
        $this->message = $message;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        try {
            Mail::to($this->kontak)->send(new \App\Mail\NotifikasiDisposisiEmail($this->message));
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
        
    }
}

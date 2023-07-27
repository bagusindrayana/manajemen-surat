<?php
namespace App\Helpers;

use App\Jobs\NotifEmail;
use App\Jobs\NotifWa;
use App\Models\Notifikasi;
use App\Models\Surat;
use App\Models\SuratDisposisi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationHelper
{
    public static function mySuratDisposisi()
    {
        $_disposisi = SuratDisposisi::where(function($w){
            $w->where(function($wu){
                $wu->where('user_id', auth()->user()->id)->where('role_id','!=',0);
            })->orWhere(function($wr){
                $wr->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())->where('user_id',0);
            });
        })->where('status','belum')->get();
        $disposisi = [];
        foreach ($_disposisi as $key => $_d) {
            if($_d->menunggu_persetujuan_id != null && $_d->menunggu_persetujuan_id != 0){
                $cek = SuratDisposisi::where('surat_id', $_d->surat_id)->where('status','diterima')->where(function($w)use($_d){
                    $w->where(function($wr)use($_d){
                        $wr->where('role_id', $_d->menunggu_persetujuan_id)->where('user_id',0);
                    });
                })->first();
                if($cek){
                    $disposisi[] = $_d;
                }
            } else {
                $disposisi[] = $_d;
            }
        }
        return $disposisi;
    }

    public static function jumlahSuratPerluDiperiksa(){
        return Surat::where('pemeriksa_id',Auth::user()->id)->where('status','diperiksa')->count();
    }

    public static function mySurat()
    {
        $_surat = Surat::where('status','ditolak')->where('user_id',Auth::user()->id)->get();
        return $_surat;
    }

    public static function createNotification($user_id,$keterangan,$url,$type = "info")
    {
        $notif = Notifikasi::create([
            'user_id' => $user_id,
            'keterangan' => $keterangan,
            'url' => $url,
            'type' => $type
        ]);
        $message = $notif->keterangan." \n ".url($notif->url);
        foreach ($notif->user->kontak_notifikasis  as $key => $value) {
            
            if($value->type == "wa"){
                dispatch(new NotifWa($value->kontak,$message));
            }

            if($value->type == "email"){
                
                dispatch(new NotifEmail($value->kontak,$message));
            }
        }
        return $notif;
    }

    public static function myNotification()
    {
        return Notifikasi::where('user_id',Auth::user()->id)->where('is_read',false)->orderBy('created_at','DESC')->get();
    }

    public static function myTotalUnreadNotification()
    {
        return Notifikasi::where('user_id',Auth::user()->id)->where('is_read',false)->count();
    }

    public static function sendWa($no,$message) {
        if (substr($no, 0, 1) == '0') {
            $no = '62' . substr($no, 1);
        }
        $client = new \GuzzleHttp\Client();
        try {
            if (env("WA_API_KEY") != "" && env("WA_API_URL") != null) {
                $res = $client->request('POST', env("WA_API_URL") . env("WA_API_KEY"), [
                    'form_params' => [
                        'id' => $no,
                        'message' => $message,
                    ]
                ]);
    
    
                Log::info($res->getStatusCode());
            } else {
                $url = env("WA_API_URL");
                $session = env("WA_SESSION_NAME");
                $res = $client->request('POST', $url, [
                    'form_params' => [
                        'session' => $session,
                        'to' => $no,
                        'text' => $message,
                    ]
                ]);
                Log::info($res->getStatusCode());
            }
       
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    public static function sendEmail($email,$message) {
        try {
            Mail::to($email)->send($message);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
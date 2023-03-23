<?php
namespace App\Helpers;

use App\Models\Surat;
use App\Models\SuratDisposisi;
use Exception;
use Illuminate\Support\Facades\Auth;

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

    public static function mySurat()
    {
        $_surat = Surat::where('status','ditolak')->where('user_id',Auth::user()->id)->get();
        return $_surat;
    }
}
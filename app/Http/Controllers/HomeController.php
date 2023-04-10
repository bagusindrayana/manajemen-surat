<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use App\Models\Surat;

class HomeController extends Controller
{
    public function index()
    {   

        
        $totalSuratMasuk = Surat::count();
        $totalSuratMasukSelesai = Surat::where('status','selesai')->count();
        $totalSuratMasukDitolak = Surat::where('status','ditolak')->count();
        return view('welcome',compact('totalSuratMasuk','totalSuratMasukSelesai','totalSuratMasukDitolak'));
    }
}

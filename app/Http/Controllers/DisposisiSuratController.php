<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Request;

class DisposisiSuratController extends Controller
{
    public function index()
    {   
        $surats = Surat::filtersInput(null, 'search');
        
        if (auth()->user()->can('View All Surat')) {
            $surats = $surats->where('status', 'diperiksa');
        } else {
            $surats = $surats->where(function($w){
                $w->where('user_id', auth()->user()->id)
                ->orWhereHas('disposisis',function($wd){
                    $wd->where('user_id', auth()->user()->id);
                })->orWhere('pemeriksa_id', auth()->user()->id);
            })->where('status', 'diperiksa');
        }
        $surats = $surats->orderBy('created_at', 'desc')->paginate(10)->appends(request()->input());
        $data = [
            'surats' => $surats,
            'title' => 'Disposisi Surat'
        ];
        return view('disposisi-surat.index', $data);
    }
}

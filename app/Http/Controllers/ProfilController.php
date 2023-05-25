<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfilController extends Controller
{
    public function index()
    {   
        $user = Auth::user();
        return view('profil.index',compact('user'));
    }

    public function update(Request $request)
    {   
        $request->validate([
            'nama'=>'required',
            'username'=>'required|unique:users,username,'.Auth::user()->id,
        ]);
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $user->update([
                'nama'=>$request->nama,
                'username'=>$request->username,
            ]);
            if($request->has('new_password') && $request->ubah_password){
                $request->validate([
                    'new_password'=>'required|min:6'
                ]);
                $user->update([
                    'password'=>bcrypt($request->new_password),
                ]);
            }
            $user->kontak_notifikasis()->delete();
            foreach ($request->kontak ?? [] as $index => $kontak) {
                $user->kontak_notifikasis()->create([
                    'kontak'=>$kontak,
                    'type'=>$request->type[$index]
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success','Profil berhasil diupdate');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error','Profil gagal diupdate : '.$th->getMessage());
        }
        
    }
}

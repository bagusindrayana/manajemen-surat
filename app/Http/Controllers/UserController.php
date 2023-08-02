<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Helpers\UserLogHelper;
use App\Models\KontakNotifikasi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        if(!auth()->user()->can('View User'))
            return abort(403,'Anda tidak memiliki cukup hak akses');
        $data = [
            'users'=>User::filtersInput(null, 'search')->paginate(10),
            'title'=>'User'
        ];

        return view('user.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $data = [
            'title'=>'Tambah User',
            'roles'=>Role::all()
        ];

        return view('user.create',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $request->validate([
            'nama'=>'required|max:150',
            'username'=>'required|string|min:5|max:20|unique:users,username',
            'password'=>'required|string|min:5|max:50',
        ]);
        DB::beginTransaction();
        try {
            $user = new User;
            $user->nama = $request->nama;
            $user->username = $request->username;
            $user->password = bcrypt($request->password);
            // $user->email = $request->email;
            // $user->no_telp = $request->no_telp;
            $user->save();
            foreach ($request->kontak ?? [] as $index => $kontak) {
                
                if($kontak == null){
                    throw new \Exception("Kontak tidak boleh kosong");
                } else {
                    //validate kontak email
                    if($request->type[$index] == "email"){
                        if(!filter_var($kontak, FILTER_VALIDATE_EMAIL)){
                            throw new \Exception("Kontak email tidak valid");
                        }
                    }
                    if($request->type[$index] == "wa"){
                        if(!preg_match('/^[0-9,+]+$/', $kontak)){
                            throw new \Exception("Kontak whatsapp tidak valid");
                        }
                    }
                    $user->kontak_notifikasis()->create([
                        'kontak'=>$kontak,
                        'type'=>$request->type[$index]
                    ]);
                }
            }
            //asign role_id
            $user->roles()->attach($request->role_id);
            UserLogHelper::create('menambah user baru dengan nama : '.$user->nama);
            DB::commit();
            return redirect()->route('user.index')->with('success','User berhasil ditambahkan');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error','User gagal ditambahkan : '.$th->getMessage());
        } 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $data = [
            'title'=>'Detail User',
            'user'=>$user
        ];

        

        return view('user.show',$data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $data = [
            'title'=>'Ubah User',
            'user'=>$user,
            'roles'=>Role::all()
        ];
       
        return view('user.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama'=>'required|max:150',
            'username'=>'required|string|min:5|max:20|unique:users,username,'.$user->id,
            
        ]);
        DB::beginTransaction();
        try {
            $user->nama = $request->nama;
            $user->username = $request->username;
            if($request->has('ubah_password')){
                $request->validate([
                    'password'=>'required|string|min:5|max:50',
                ]);
                $user->password = bcrypt($request->password);
            }
            // $user->email = $request->email;
            // $user->no_telp = $request->no_telp;
            $user->save();
            $user->kontak_notifikasis()->delete();
            foreach ($request->kontak ?? [] as $index => $kontak) {
                if($kontak == null){
                    throw new \Exception("Kontak tidak boleh kosong");
                } else {
                    //validate kontak email
                    if($request->type[$index] == "email"){
                        if(!filter_var($kontak, FILTER_VALIDATE_EMAIL)){
                            throw new \Exception("Kontak email tidak valid");
                        }
                    }
                    if($request->type[$index] == "wa"){
                        if(!preg_match('/^[0-9,+]+$/', $kontak)){
                            throw new \Exception("Kontak whatsapp tidak valid");
                        }
                    }
                    $user->kontak_notifikasis()->create([
                        'kontak'=>$kontak,
                        'type'=>$request->type[$index]
                    ]);
                }
            }
            //asign role_id
            $user->roles()->sync($request->role_id);
            UserLogHelper::create('mengubah user : '.$user->nama);
            DB::commit();
            return redirect()->route('user.index')->with('success','User berhasil ubah');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error','User gagal ubah : '.$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {   
        if($user->id == 1){
            return redirect()->route('user.index')->with('error','User gagal dihapus : User ini tidak dapat dihapus');
        }
        DB::beginTransaction();
        try {
            $user->delete();
            UserLogHelper::create('menghapus user : '.$user->nama);
            DB::commit();
            return redirect()->route('user.index')->with('success','User berhasil dihapus');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('user.index')->with('error','User gagal dihapus : '.$th->getMessage());
        }
    }

    function testNotifikasi($id) {
        //check post "test-notifikasi"
        if(request()->has('test-notifikasi')){
            $kontak = KontakNotifikasi::find(request()->kontak_id);
            if($kontak->type == "wa"){
                NotificationHelper::sendWa($kontak->kontak,"Test Notifikasi Sistem Disposisi Surat Masuk : ".date("Y-m-d"));
            } else if($kontak->type == "email"){
                NotificationHelper::sendEmail($kontak->kontak,"Test Notifikasi Sistem Disposisi Surat Masuk : ".date("Y-m-d"));
            }
        }

        return redirect()->back();
    }
}

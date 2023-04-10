<?php

namespace App\Http\Controllers;

use App\Helpers\GhostscriptHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\StorageHelper;
use App\Helpers\UserLogHelper;
use App\Models\Berkas;
use App\Models\CloudStorage;
use App\Models\Role;
use App\Models\Surat;
use App\Models\SuratDisposisi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use ZipArchive;

class SuratController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $surats = Surat::filtersInput(null, 'search')->orderBy('created_at','desc')->paginate(10);
        $data = [
            'surats' => $surats,
            'title' => 'Surat'
        ];
        return view('surat.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tmpFiles = StorageHelper::getTmpFiles();
        $users = User::where('id', '!=', auth()->user()->id)->get();
        $roles = Role::all();
        $storages = CloudStorage::where('status', 'active')->get();
        $data = [
            'title' => 'Tambah Surat',
            'tmpFiles' => $tmpFiles,
            'users' => $users,
            'roles' => $roles,
            'storages' => $storages
        ];


        return view('surat.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $surat = Surat::create([
                'user_id' => Auth::user()->id,
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' => $request->tanggal_surat,
                'perihal' => $request->perihal,
                'sifat' => $request->sifat,
                'isi' => $request->isi,
            ]);
            foreach ($request->user_id as $key => $user_id) {
                $role_id = $request->role_id[$key];
                $surat->disposisis()->create([
                    'user_id' => $user_id,
                    'role_id' => $role_id,
                    'menunggu_persetujuan_id'=> $request->menunggu_persetujuan_id[$key],
                    'keterangan' => $request->keterangan[$key],
                ]);
                if($request->menunggu_persetujuan_id[$key] != null){
                    continue;
                }
                $type = "info";
                if($request->sifat != "biasa"){
                    $type = "warning";
                }
                if($user_id == 0){
                    $users = User::whereHas('roles',function($wr)use($role_id){
                        $wr->where('id',$role_id);
                    })->get();

                    foreach ($users as $user) {
                        NotificationHelper::createNotification($user->id,'Surat Masuk Perlu Disposisi : '.$surat->nomor_surat,'surat/'.$surat->id,$type);
                    }
                } else {
                    NotificationHelper::createNotification($user_id,'Surat Masuk Perlu Disposisi : '.$surat->nomor_surat,'surat/'.$surat->id,$type);
                }
            }

            $tmpFiles = StorageHelper::getTmpFiles();
            $activeStorages = [];
            if ($request->has('all_storage') && $request->all_storage == "true") {
                
                $activeStorages = CloudStorage::where('status', 'active')->get();
                
            } else {
                $activeStorages = CloudStorage::where('status', 'active')->whereIn('id',$request->cloud_storage_id)->get();
            }

            foreach ($tmpFiles as $key => $tmpFile) {
                $berkas = $surat->berkas()->create([
                    // 'storage_id'=>$activeStorage->id,
                    'nama_berkas' => $tmpFile['name'],
                    'path' => $tmpFile['path'],
                    'mime_type' => $tmpFile['mime_type'],
                    'size' => $tmpFile['size'],
                ]);
                
                
                foreach ($activeStorages as $key => $activeStorage) {
                    $uploadedResult = $activeStorage->uploadFile($tmpFile['path']);
                    $berkas->berkas_storages()->create([
                        'storage_id' => $activeStorage->id,
                        'berkas_id' => $berkas->id,
                        'path' => $uploadedResult,
                    ]);

                    
                }
                $_path = 'surat/' . Auth::user()->id . '/' . basename($tmpFile['path']);
                $berkas->update([
                    'path' => $_path
                ]);
                Storage::move($tmpFile['path'], $_path);


            }

            
            UserLogHelper::create('menambah surat baru dengan nomor : '.$surat->nomor_surat);
            DB::commit();
            return redirect()->route('surat.index')->with('success', 'Surat berhasil ditambahkan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Surat gagal ditambahkan : ' . $th->getMessage())->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Surat  $surat
     * @return \Illuminate\Http\Response
     */
    public function show(Surat $surat)
    {   

        $disposisi_berikutnya = [];
        $cek = SuratDisposisi::where('surat_id', $surat->id)->where(function($w){
            $w->where(function($wu){
                $wu->where('user_id', auth()->user()->id)->where('role_id','!=',0);
            })->orWhere(function($wr){
                $wr->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())->where('user_id',0);
            });
        })->first();
        if($cek == null && $surat->user_id != auth()->user()->id){
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan disposisi');
        }
        $disposisi_berikutnya = SuratDisposisi::where('surat_id', $surat->id)->whereIn('menunggu_persetujuan_id', auth()->user()->roles->pluck('id')->toArray())->get();
        $data = [
            'surat' => $surat,
            'disposisi_berikutnya'=>$disposisi_berikutnya,
            'cek'=>$cek,
            'title' => 'Detail Surat'
        ];
        return view('surat.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Surat  $surat
     * @return \Illuminate\Http\Response
     */
    public function edit(Surat $surat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Surat  $surat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Surat $surat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Surat  $surat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Surat $surat)
    {   
        DB::beginTransaction();
        try {
            $berkas = $surat->berkas;
            foreach ($berkas as $b) {
                foreach ($b->storages as $s) {
                    if($s->type == "local"){
                        Storage::delete($b->path);
                    }
                }
            }
            $surat->disposisis()->delete();
            $surat->delete();
            DB::commit();
            return redirect()->route('surat.index')->with('success', 'Surat berhasil dihapus');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('surat.index')->with('error', 'Surat gagal di hapus');

        }
    }

    public function downloadPdf(Surat $surat)
    {
        $data = [
            'surat' => $surat,
            'title' => 'Detail Surat'
        ];
        
        $nomor = $surat->nomor_surat;
        $nomor = str_replace('/', '-', $nomor);
        $file_name = 'lembar_disposisi_' . $nomor . '.pdf';
        $path = storage_path() . '/app/generated/';
        $pdf = Pdf::loadView('surat.pdf', $data)->setPaper('a4', 'potrait');
        $pdf->save($path .'tmp_'. $file_name);

        try {
            //try to merge pdf
            $pdfMerger = PDFMerger::init();
            $pdfMerger->addPDF($path . $file_name);
            foreach ($surat->berkas as $berkas) {
                // $isLocal = $berkas->storages()->where('type', 'local')->count();
                // if ($isLocal > 0) {
                //     $pdfMerger->addPDF(storage_path() . '/app/'.$berkas->path);
                // }
                $pdfMerger->addPDF(storage_path() . '/app/'.$berkas->path);
            }
            $pdfMerger->merge();
            $pdfMerger->save($path);
        } catch (\Throwable $th) {
            //try again using ghostscript
            // if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //     $gs_path = storage_path()."/ghostscripts/gs-win.exe";
            // } else {
            //     $gs_path = storage_path()."/ghostscripts/gs-linux";
            // }
            if(env("GHOSTSCRIPT_BIN") != null){
                try {
                    $gs = new GhostscriptHelper(env("GHOSTSCRIPT_BIN"));
                    $gs->addInputFile($path .'tmp_'. $file_name);
                    foreach ($surat->berkas as $berkas) {
                        // $isLocal = $berkas->storages()->where('type', 'local')->count();
                        // if ($isLocal > 0) {
                        //     $gs->addInputFile('"'.storage_path() . '/app/'.$berkas->path.'"');
                        // }
                        $gs->addInputFile('"'.storage_path() . '/app/'.$berkas->path.'"');
                    }
                    $gs->setOutputFile($path . $file_name);
                    $gs->merge();
                    return response()->download($path . $file_name)->deleteFileAfterSend(true);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }

            //jika tidak bisa merge maka di kompres saja
            $zipArchive = new ZipArchive();
            $file_name = 'lembar_disposisi_' . $nomor . '.zip';
            if (!$zipArchive->open($path.$file_name, ZIPARCHIVE::OVERWRITE)){
                return redirect()->back()->with('error', 'Gagal membuat zip file');
            } else {
                $zipArchive->addGlob($path .'tmp_'. $file_name);
                foreach ($surat->berkas as $berkas) {
                    // $isLocal = $berkas->storages()->where('type', 'local')->count();
                    // if ($isLocal > 0) {
                    //     $zipArchive->addGlob(storage_path() . '/app/'.$berkas->path);
                    // }
                    $zipArchive->addGlob(storage_path() . '/app/'.$berkas->path);
                }
                
                if (!$zipArchive->status == ZIPARCHIVE::ER_OK){
                    return redirect()->back()->with('error', 'Gagal membuat zip file');
                } else {
                    $zipArchive->close();
                }
                   
            }
        }
        

        return response()->download($path . $file_name)->deleteFileAfterSend(true);
        //return $pdf->download('lembar_disposisi_' . $nomor . '.pdf');

    }

    public function getUserByRole(Role $role)
    {
        return User::whereHas('roles', function ($query) use ($role) {
            $query->where('id', $role->id);
        })->get();
    }

    function viewBerkas(Surat $surat,Berkas $berkas)
    {
        $isLocal = $berkas->storages()->where('type', 'local')->count();
        if ($isLocal > 0) {
            return response()->file(storage_path() . '/app/'.$berkas->path);
        }
        abort(404);
    }

    public function disposisi(Request $request,Surat $surat)
    {
        $cek = SuratDisposisi::where('surat_id', $surat->id)->where(function($w){
            $w->where(function($wu){
                $wu->where('user_id', auth()->user()->id)->where('role_id','!=',0);
            })->orWhere(function($wr){
                $wr->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())->where('user_id',0);
            });
        })->first();
        if($cek == null){
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan disposisi');
        }
        DB::beginTransaction();
        try {
            $cek->update([
                'status'=>$request->status,
                'keterangan'=>$request->keterangan,
            ]);
            $cek->riwayat_disposisis()->create([
                'status'=>$request->status,
                'keterangan'=>$request->keterangan,
            ]);

            if($request->status == "ditolak"){
                NotificationHelper::createNotification($surat->user_id,'Surat : '.$surat->nomor_surat.'. Ditolak oleh '.Auth::user()->nama,'surat/'.$surat->id,"danger");
                $_mp_id = $surat->disposisis()
                ->whereNotNull('menunggu_persetujuan_id')
                ->whereIn('role_id',auth()->user()->roles->pluck('id')->toArray())
                ->where(function($w){
                    $w->where('user_id',auth()->user()->id)->orWhere('user_id',0);
                })->pluck('menunggu_persetujuan_id')->toArray();
                foreach ($surat->disposisis()->whereIn('role_id',$_mp_id)->get() as $d) {
                    if($d->user_id == 0){
                        $users = User::whereHas('roles',function($wr)use($d){
                            $wr->where('id',$d->role_id);
                        })->get();
    
                        foreach ($users as $user) {
                            NotificationHelper::createNotification($user->id,'Surat : '.$surat->nomor_surat.'. Ditolak oleh '.Auth::user()->nama,'surat/'.$surat->id,"danger");
                        }
                    } else {
                        NotificationHelper::createNotification($d->user_id,'Surat : '.$surat->nomor_surat.'. Ditolak oleh '.Auth::user()->nama,'surat/'.$surat->id,"danger");
                    }
                }
            }

            if($request->status == "diterima"){
                NotificationHelper::createNotification($surat->user_id,'Surat : '.$surat->nomor_surat.'. Diterima oleh '.Auth::user()->nama,'surat/'.$surat->id,"success");
                foreach ($surat->disposisi_berikutnya as $db) {
                    if($db->menunggu_persetujuan_id != null){
                        $_cek = $surat->disposisis()->where('role_id',$db->menunggu_persetujuan_id)->where('status','diterima')->count();
                        if($_cek <= 0){
                            continue;
                        }
                    }
                    if($db->user_id == 0){
                        $users = User::whereHas('roles',function($wr)use($db){
                            $wr->where('id',$db->role_id);
                        })->get();
    
                        foreach ($users as $user) {
                            NotificationHelper::createNotification($user->id,'Surat Masuk Perlu Disposisi : '.$surat->nomor_surat,'surat/'.$surat->id,"info");
                        }
                    } else {
                        NotificationHelper::createNotification($db->user_id,'Surat Masuk Perlu Disposisi : '.$surat->nomor_surat,'surat/'.$surat->id,"info");
                    }
                }
            }
    
            $_ditolak = SuratDisposisi::where('surat_id', $surat->id)->where('status','ditolak')->count();
            $_diterima = SuratDisposisi::where('surat_id', $surat->id)->where('status','diterima')->count();
            if($_ditolak > 0){
                $cek->surat->update([
                    'status'=>'ditolak'
                ]);
            } elseif($_diterima == $cek->disposisis){
                $cek->surat->update([
                    'status'=>'selesai'
                ]);
            }  elseif($_diterima > 0){
                $cek->surat->update([
                    'status'=>'proses'
                ]);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal untuk melakukan disposisi. '.$th->getMessage())->withInput($request->all());
        }
        
        return redirect()->route('surat.index')->with('success', 'Surat berhasil di-disposisikan');

    }

}
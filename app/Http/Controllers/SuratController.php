<?php

namespace App\Http\Controllers;

use App\Helpers\GhostscriptHelper;
use App\Helpers\StorageHelper;
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
        $surats = Surat::filtersInput(null, 'search')->paginate(10);
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
                $surat->disposisis()->create([
                    'user_id' => $user_id,
                    'role_id' => $request->role_id[$key],
                    'menunggu_persetujuan_id'=> $request->menunggu_persetujuan_id[$key],
                    'keterangan' => $request->keterangan[$key],
                ]);
            }
            if ($request->has('all_storage') && $request->all_storage == "true") {
                $tmpFiles = StorageHelper::getTmpFiles();
                $activeStorages = CloudStorage::where('status', 'active')->get();
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

                        $berkas->update([
                            'path' => $uploadedResult
                        ]);
                    }


                }
            }
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
        //
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
                $isLocal = $berkas->storages()->where('type', 'local')->count();
                if ($isLocal > 0) {
                    $pdfMerger->addPDF(storage_path() . '/app/'.$berkas->path);
                }
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
                        $isLocal = $berkas->storages()->where('type', 'local')->count();
                        if ($isLocal > 0) {
                            $gs->addInputFile('"'.storage_path() . '/app/'.$berkas->path.'"');
                        }
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
                    $isLocal = $berkas->storages()->where('type', 'local')->count();
                    if ($isLocal > 0) {
                        $zipArchive->addGlob(storage_path() . '/app/'.$berkas->path);
                    }
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
            return redirect()->back()->with('error', 'Gagal untuk melakukan disposisi');
        }
        
        return redirect()->route('surat.index')->with('success', 'Surat berhasil di-disposisikan');

    }

}
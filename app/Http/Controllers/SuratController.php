<?php

namespace App\Http\Controllers;

use App\Helpers\GhostscriptHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\StorageHelper;
use App\Helpers\UserLogHelper;
use App\Jobs\UploadCloudStorage;
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
use Illuminate\Support\Facades\Log;
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
        $surats = Surat::filtersInput(null, 'search');
        if(!auth()->user()->can('View All Surat')){
            $surats = $surats->where(function($w){
                $w->where('user_id', auth()->user()->id)
                ->orWhereHas('disposisis',function($wd){
                    $wd->where('user_id', auth()->user()->id);
                })->orWhere('pemeriksa_id', auth()->user()->id);
            });
        }
        $surats = $surats->orderBy('created_at', 'desc')->paginate(10)->appends(request()->input());
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
        $userPemeriksa = User::where('id', '!=', auth()->user()->id)->whereHas('roles', function ($q) {
            $q->whereHas('permissions', function ($q) {
                $q->where('name', 'Check Surat');
            });
        })->get();
        $roles = Role::all();
        $storages = CloudStorage::where('status', 'active')->get();
        $data = [
            'title' => 'Tambah Surat',
            'tmpFiles' => $tmpFiles,
            'users' => $users,
            'roles' => $roles,
            'storages' => $storages,
            'userPemeriksa'=>$userPemeriksa
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
        $movedFiles = [];
        $request->validate([
            'nomor_surat' => 'required',
            'tanggal_surat' => 'required',
            'perihal' => 'required',
            'sifat' => 'required',
            'isi' => 'required',
            'pemeriksa_id' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $surat = Surat::create([
                'user_id' => Auth::user()->id,
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' => $request->tanggal_surat,
                'perihal' => $request->perihal,
                'sifat' => $request->sifat,
                'isi' => $request->isi,
                'pemeriksa_id'=>$request->pemeriksa_id,
                'status'=>'diperiksa'
            ]);
            $user_id = $request->pemeriksa_id;
            $type = "info";
            if ($request->sifat != "biasa") {
                $type = "warning";
            }
            NotificationHelper::createNotification($user_id, 'Surat Masuk Perlu Di Periksa Untuk Disposisi : ' . $surat->nomor_surat, 'surat/' . $surat->id, $type);
            // foreach ($request->user_id as $key => $user_id) {
            //     $role_id = $request->role_id[$key];
            //     $surat->disposisis()->create([
            //         'user_id' => $user_id,
            //         'role_id' => $role_id,
            //         'menunggu_persetujuan_id' => $request->menunggu_persetujuan_id[$key],
            //         'keterangan' => $request->keterangan[$key],
            //     ]);
            //     if ($request->menunggu_persetujuan_id[$key] != null) {
            //         continue;
            //     }
            //     $type = "info";
            //     if ($request->sifat != "biasa") {
            //         $type = "warning";
            //     }
            //     if ($user_id == 0) {
            //         $users = User::whereHas('roles', function ($wr) use ($role_id) {
            //             $wr->where('id', $role_id);
            //         })->get();

            //         foreach ($users as $user) {
            //             NotificationHelper::createNotification($user->id, 'Surat Masuk Perlu Disposisi : ' . $surat->nomor_surat, 'surat/' . $surat->id, $type);
            //         }
            //     } else {
            //         NotificationHelper::createNotification($user_id, 'Surat Masuk Perlu Disposisi : ' . $surat->nomor_surat, 'surat/' . $surat->id, $type);
            //     }
            // }
            $tmpFiles = StorageHelper::getTmpFiles();
            foreach ($tmpFiles as $key => $tmpFile) {
                $_path = 'surat/' . Auth::user()->id . '/' . basename($tmpFile['path']);
                $berkas = $surat->berkas()->create([
                    // 'storage_id'=>$activeStorage->id,
                    'nama_berkas' => $tmpFile['name'],
                    'path' => $_path,
                    'mime_type' => $tmpFile['mime_type'],
                    'size' => $tmpFile['size'],
                ]);

                Storage::move($tmpFile['path'], $_path);

                $movedFiles[] = $_path;
            }


            UserLogHelper::create('menambah surat baru dengan nomor : ' . $surat->nomor_surat);
            DB::commit();
            dispatch(new UploadCloudStorage($surat, $request->all(), Auth::user()->id));
            return redirect()->route('surat.index')->with('success', 'Surat berhasil ditambahkan');
        } catch (\Throwable $th) {
            DB::rollBack();
            //check movedFiles
            foreach ($movedFiles as $key => $movedFile) {
                $np = str_replace('surat/', '_tmp/', $movedFile);
                Storage::move($movedFile, $np);
            }
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

        $stillUpload = DB::table('jobs')->where('payload', 'like', '%UploadCloudStorage%')->count() != 0;
        $disposisi_berikutnya = [];
        $cek = SuratDisposisi::where('surat_id', $surat->id)->where(function ($w) {
            $w->where(function ($wu) {
                $wu->where('user_id', auth()->user()->id)->where('role_id', '!=', 0);
            })->orWhere(function ($wr) {
                $wr->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())->where('user_id', 0);
            });
        })->first();
        if ($cek == null && auth()->user()->id != $surat->user_id && $surat->status != "diperiksa") {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan disposisi');
        }
        $disposisi_berikutnya = SuratDisposisi::where('surat_id', $surat->id)->whereIn('menunggu_persetujuan_id', auth()->user()->roles->pluck('id')->toArray())->get();
        $users = User::where('id', '!=', auth()->user()->id)->get();
        $roles = Role::all();
        $data = [
            'surat' => $surat,
            'disposisi_berikutnya' => $disposisi_berikutnya,
            'cek' => $cek,
            'title' => 'Detail Surat',
            'stillUpload' => $stillUpload,
            'roles'=>$roles,
            'users'=>$users,
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
                Storage::delete($b->path);
                // foreach ($b->storages as $s) {
                //     if ($s->type == "local") {
                //         Storage::delete($b->path);
                //     }
                // }
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
        $pdf->save($path . 'tmp_' . $file_name);

        try {
            //try to merge pdf
            $pdfMerger = PDFMerger::init();
            $pdfMerger->addPDF($path . $file_name);
            foreach ($surat->berkas as $berkas) {
                // $isLocal = $berkas->storages()->where('type', 'local')->count();
                // if ($isLocal > 0) {
                //     $pdfMerger->addPDF(storage_path() . '/app/'.$berkas->path);
                // }
                $pdfMerger->addPDF(storage_path() . '/app/' . $berkas->path);
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
            if (env("GHOSTSCRIPT_BIN") != null) {
                try {
                    $gs = new GhostscriptHelper(env("GHOSTSCRIPT_BIN"));
                    $gs->addInputFile($path . 'tmp_' . $file_name);
                    foreach ($surat->berkas as $berkas) {
                        // $isLocal = $berkas->storages()->where('type', 'local')->count();
                        // if ($isLocal > 0) {
                        //     $gs->addInputFile('"'.storage_path() . '/app/'.$berkas->path.'"');
                        // }
                        $gs->addInputFile('"' . storage_path() . '/app/' . $berkas->path . '"');
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
            $zip_name = 'lembar_disposisi_' . $nomor . '.zip';
            if ($zipArchive->open($path . $zip_name, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)  !== TRUE) {
                return redirect()->back()->with('error', 'Gagal membuat zip file (1)');
            } else {

                try {
                    $zipArchive->addFile($path . 'tmp_' . $file_name,$file_name);
                    foreach ($surat->berkas as $berkas) {
                        // $isLocal = $berkas->storages()->where('type', 'local')->count();
                        // if ($isLocal > 0) {
                        //     $zipArchive->addGlob(storage_path() . '/app/'.$berkas->path);
                        // }
                        $zipArchive->addFile(storage_path() . '/app/' . $berkas->path,basename($berkas->path));
                    }
                } catch (\Throwable $th) {
                    Log::error($th);
                    return redirect()->back()->with('error', 'Gagal membuat zip file (2)');
                }

                if (!$zipArchive->status == ZIPARCHIVE::ER_OK) {
                    return redirect()->back()->with('error', 'Gagal membuat zip file (3) : ' . $zipArchive->status);
                } else {
                    @$zipArchive->close();
                    
                }

            }
        }

        $zipPath = $path . $zip_name;
        //check if file exists
        if (!file_exists($zipPath)) {
            return redirect()->back()->with('error', 'Gagal membuat zip file (4)');
        }
        return response()->download($zipPath)->deleteFileAfterSend(true);
        //return $pdf->download('lembar_disposisi_' . $nomor . '.pdf');

    }

    public function getUserByRole(Role $role)
    {
        return User::whereHas('roles', function ($query) use ($role) {
            $query->where('id', $role->id);
        })->get();
    }

    function viewBerkas(Surat $surat, Berkas $berkas)
    {
        //$isLocal = $berkas->storages()->where('type', 'local')->count();
        $cek = Storage::exists($berkas->path);
        if ($cek) {
            $get = Storage::get($berkas->path);
            $mime = Storage::mimeType($berkas->path);
            return response($get)->header('Content-Type', $mime);
        }
        
        abort(404);
    }

    public function disposisi(Request $request, Surat $surat)
    {
        $cek = SuratDisposisi::where('surat_id', $surat->id)->where(function ($w) {
            $w->where(function ($wu) {
                $wu->where('user_id', auth()->user()->id)->where('role_id', '!=', 0);
            })->orWhere(function ($wr) {
                $wr->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())->where('user_id', 0);
            });
        })->first();
        if ($cek == null) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan disposisi');
        }
        DB::beginTransaction();
        try {
            $cek->update([
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);
            $cek->riwayat_disposisis()->create([
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            if ($request->status == "ditolak") {
                NotificationHelper::createNotification($surat->user_id, 'Surat : ' . $surat->nomor_surat . '. Ditolak oleh ' . Auth::user()->nama, 'surat/' . $surat->id, "danger");
                $_mp_id = $surat->disposisis()
                    ->whereNotNull('menunggu_persetujuan_id')
                    ->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())
                    ->where(function ($w) {
                        $w->where('user_id', auth()->user()->id)->orWhere('user_id', 0);
                    })->pluck('menunggu_persetujuan_id')->toArray();
                foreach ($surat->disposisis()->whereIn('role_id', $_mp_id)->get() as $d) {
                    if ($d->user_id == 0) {
                        $users = User::whereHas('roles', function ($wr) use ($d) {
                            $wr->where('id', $d->role_id);
                        })->get();

                        foreach ($users as $user) {
                            NotificationHelper::createNotification($user->id, 'Surat : ' . $surat->nomor_surat . '. Ditolak oleh ' . Auth::user()->nama, 'surat/' . $surat->id, "danger");
                        }
                    } else {
                        NotificationHelper::createNotification($d->user_id, 'Surat : ' . $surat->nomor_surat . '. Ditolak oleh ' . Auth::user()->nama, 'surat/' . $surat->id, "danger");
                    }
                }
            }

            if ($request->status == "diterima") {
                NotificationHelper::createNotification($surat->user_id, 'Surat : ' . $surat->nomor_surat . '. Diterima oleh ' . Auth::user()->nama, 'surat/' . $surat->id, "success");
                foreach ($surat->disposisi_berikutnya as $db) {
                    if ($db->menunggu_persetujuan_id != null) {
                        $_cek = $surat->disposisis()->where('role_id', $db->menunggu_persetujuan_id)->where('status', 'diterima')->count();
                        if ($_cek <= 0) {
                            continue;
                        }
                    }
                    if ($db->user_id == 0) {
                        $users = User::whereHas('roles', function ($wr) use ($db) {
                            $wr->where('id', $db->role_id);
                        })->get();

                        foreach ($users as $user) {
                            NotificationHelper::createNotification($user->id, 'Surat Masuk Perlu Disposisi : ' . $surat->nomor_surat, 'surat/' . $surat->id, "info");
                        }
                    } else {
                        NotificationHelper::createNotification($db->user_id, 'Surat Masuk Perlu Disposisi : ' . $surat->nomor_surat, 'surat/' . $surat->id, "info");
                    }
                }
            }

            $_ditolak = SuratDisposisi::where('surat_id', $surat->id)->where('status', 'ditolak')->count();
            $_diterima = SuratDisposisi::where('surat_id', $surat->id)->where('status', 'diterima')->count();
            if ($_ditolak > 0) {
                $cek->surat->update([
                    'status' => 'ditolak'
                ]);
            } elseif ($_diterima == $cek->disposisis) {
                $cek->surat->update([
                    'status' => 'selesai'
                ]);
            } elseif ($_diterima > 0) {
                $cek->surat->update([
                    'status' => 'proses'
                ]);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal untuk melakukan disposisi. ' . $th->getMessage())->withInput($request->all());
        }

        return redirect()->route('surat.index')->with('success', 'Surat berhasil di-disposisikan');

    }

}
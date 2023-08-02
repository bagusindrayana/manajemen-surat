<?php

namespace App\Http\Controllers;

use App\Helpers\GhostscriptHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\StorageHelper;
use App\Helpers\UserLogHelper;
use App\Jobs\UploadCloudStorage;
use App\Models\Berkas;
use App\Models\CloudStorage;
use App\Models\Notifikasi;
use App\Models\Role;
use App\Models\Surat;
use App\Models\SuratDisposisi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Storage;
use PulkitJalan\Google\Facades\Google;
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
        if (!auth()->user()->can('View Surat'))
            return abort(403, 'Anda tidak memiliki cukup hak akses');
        $surats = Surat::filtersInput(null, 'search');
        if (!auth()->user()->can('View All Surat')) {
            $surats = $surats->where(function ($w) {
                $w->where('user_id', auth()->user()->id)
                    ->orWhereHas('disposisis', function ($wd) {
                        $wd->where('user_id', auth()->user()->id)->orWhere(function ($ww) {
                            $ww->whereNull('user_id')->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray());
                        });
                    })->orWhere('pemeriksa_id', auth()->user()->id);
            });
        }
        $surats = $surats->orderBy('created_at', 'desc')->paginate(10)->appends(request()->input());
        $data = [
            'surats' => $surats,
            'title' => 'Surat',
            'view_all' => auth()->user()->can("View All Surat")
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
        $userPemeriksa = User::whereHas('roles', function ($q) {
            $q->whereHas('permissions', function ($q) {
                $q->where('name', 'Check Surat');
            });
        })->orderBy('id', 'DESC')->get();
        $roles = Role::all();
        $storages = CloudStorage::where('status', 'active')->where('personal',false)->get();
        $data = [
            'title' => 'Tambah Surat',
            'tmpFiles' => $tmpFiles,
            'users' => $users,
            'roles' => $roles,
            'storages' => $storages,
            'userPemeriksa' => $userPemeriksa
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
            'nomor_surat' => 'required|string|max:50',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:200',
            'sifat' => 'required',
            // 'isi' => 'required',
            'pemeriksa_id' => 'required',
        ]);
        $tmpFiles = StorageHelper::getTmpFiles();
        if (count($tmpFiles) == 0) {
            return redirect()->back()->with('error', 'Berkas tidak boleh kosong')->withInput($request->all());
        }
        DB::beginTransaction();
        try {
            $surat = Surat::create([
                'user_id' => Auth::user()->id,
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' => $request->tanggal_surat,
                'perihal' => $request->perihal,
                'sifat' => $request->sifat,
                'isi' => $request->isi,
                'pemeriksa_id' => $request->pemeriksa_id,
                'status' => 'diperiksa'
            ]);
            $user_id = $request->pemeriksa_id;
            $type = "info";
            if ($request->sifat != "biasa") {
                $type = "warning";
            }
            
            $keterangan = "Surat Masuk Perlu Disposisi : \n
Nomor : *".$surat->nomor_surat ."* \n
Perihal : *".$surat->perihal ."* \n
Sifat : *".$surat->perihal ."* \n
Silahkan Login Ke Web Aplikasi Untuk Segera Memeriksa Surat Masuk Dan Meneruskan Disposisi";
$url = 'surat/' . $surat->id;
            $notif = Notifikasi::create([
                'user_id' => $user_id,
                'keterangan' => "Surat Masuk Perlu Disposisi : <br> Nomor : <b>" . $surat->nomor_surat . "</b> <br> Perihal : <b>" . $surat->perihal . "</b> <br> Sifat : <b>" . $surat->perihal . "</b>",
                'url' => $url,
                'type' => $type
            ]);
            NotificationHelper::createNotification($user_id,$keterangan , $url, $type);
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
                $_path = 'surat/' . $surat->id . '/' . basename($tmpFile['path']);
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
            if (isset($request->all_storage) && $request->all_storage == "true") {
                $activeStorages = CloudStorage::where('status', 'active')->where('personal', false)->get();
            } else {
                $activeStorages = CloudStorage::where('status', 'active')->where('personal', false)->whereIn('id', $request->cloud_storage_id)->get();
            }
            foreach ($activeStorages as $key => $activeStorage) {
                dispatch(new UploadCloudStorage($surat, $activeStorage->id));
            }

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
    public function show($id)
    {

        $surat = Surat::with('berkas.berkas_storages.storage')->find($id);
        $stillUpload = DB::table('jobs')->where('payload', 'like', '%UploadCloudStorage%')->count() != 0;
        $disposisi_berikutnya = [];
        $cek = SuratDisposisi::where('surat_id', $surat->id)->where(function ($w) {
            $w->where(function ($wu) {
                $wu->where('user_id', auth()->user()->id)->where('role_id', '!=', 0);
            })->orWhere(function ($wr) {
                $wr->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())->whereNull('user_id');
            });
        })->first();
        if ($cek == null && auth()->user()->id != $surat->user_id && $surat->status != "diperiksa" && $surat->pemeriksa_id != auth()->user()->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan disposisi');
        }
        // $disposisi_berikutnya = SuratDisposisi::where('surat_id', $surat->id)->whereIn('menunggu_persetujuan_id', auth()->user()->roles->pluck('id')->toArray())->get();
        $disposisi_berikutnya = SuratDisposisi::where('surat_id', $surat->id)->get();
        $users = User::where('id', '!=', auth()->user()->id)->get();
        $roles = Role::all();
        
        $data = [
            'surat' => $surat,
            'disposisi_berikutnya' => $disposisi_berikutnya,
            'cek' => $cek,
            'title' => 'Detail Surat',
            'stillUpload' => $stillUpload,
            'roles' => $roles,
            'users' => $users,
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
        $tmpFiles = StorageHelper::getTmpFiles();
        $users = User::where('id', '!=', auth()->user()->id)->get();
        $userPemeriksa = User::whereHas('roles', function ($q) {
            $q->whereHas('permissions', function ($q) {
                $q->where('name', 'Check Surat');
            });
        })->get();
        $roles = Role::all();
        $storages = CloudStorage::where('status', 'active')->where('personal',false)->get();
        $data = [
            'title' => 'Ubah Surat',
            'tmpFiles' => $tmpFiles,
            'users' => $users,
            'roles' => $roles,
            'storages' => $storages,
            'userPemeriksa' => $userPemeriksa,
            'surat' => $surat
        ];
        return view('surat.edit', $data);
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
        if ($request->berikan_disposisi == "berikan_disposisi") {
            $request->validate([
                'role_id' => 'required|array',
                'keterangan' => 'required|array',
            ]);
            DB::beginTransaction();
            try {
                $surat->update([
                    'status' => 'pending',
                ]);
                foreach ($request->user_id as $key => $user_id) {
                    $role_id = $request->role_id[$key];
                    $surat->disposisis()->create([
                        'user_id' => $user_id,
                        'role_id' => $role_id,
                        'menunggu_persetujuan_id' => @$request->menunggu_persetujuan_id[$key] ?? null,
                        'keterangan' => $request->keterangan[$key],
                    ]);
                    if (isset($request->menunggu_persetujuan_id[$key]) && $request->menunggu_persetujuan_id[$key] != null) {
                        continue;
                    }
                    $type = "info";
                    if ($request->sifat != "biasa") {
                        $type = "warning";
                    }
                    $keterangan =  "
*DISPOSISI SURAT MASUK* : \n
Nomor : *".$surat->nomor_surat ."* \n
Perihal : *".$surat->perihal ."* \n
Sifat : *".$surat->perihal ."* \n
Silahkan Login Ke Aplikasi Web Untuk Melihat Dan Memeriksa Disposisi Surat Masuk";
                $url = 'surat/' . $surat->id;
                
                    if ($user_id == null) {
                        $users = User::whereHas('roles', function ($wr) use ($role_id) {
                            $wr->where('id', $role_id);
                        })->get();
                        if(count($users) == 0){
                            DB::rollBack();
                            return redirect()->back()->with('error', "tidak ada user yang menerima disposisi")->withInput($request->all());
                        }

                        foreach ($users as $user) {
                            $notif = Notifikasi::create([
                                'user_id' => $user->id,
                                'keterangan' => "DISPOSISI SURAT MASUK : <br> Nomor : <b>" . $surat->nomor_surat . "</b> <br> Perihal : <b>" . $surat->perihal . "</b> <br> Sifat : <b>" . $surat->perihal . "</b>",
                                'url' => $url,
                                'type' => $type
                            ]);
                            
                            $activeStorages = CloudStorage::where('status', 'active')->where('user_id',$user->id)->where('personal', true)->get();
                            foreach ($activeStorages as $key => $activeStorage) {
                                if($surat->sifat == "rahasia"){
                                    // $drive = Google::make('drive');
                                    // $setting = StorageHelper::createRefreshToken($activeStorage);
                                    // //create shortcut
                                    // $drive->getClient()->setAccessType('offline');
                                    // $drive->getClient()->setApprovalPrompt("force");
                                    // $drive->getClient()->setAccessToken($setting->access_token);
                                    // foreach($surat->berkas as $berkas){
                                    //     foreach($berkas->berkas_storages as $b){
                                    //         $setting2 = StorageHelper::createRefreshToken($b->storage);
                                    //         $folder_id = $setting2->folder_id;
                                    //         $d_file = new DriveFile();
                                    //         $d_file->setName(basename($setting));
                                    //         $d_file->setParents([$folder_id]);
                                    //     }
                                    // }
                                } else {
                                    dispatch(new UploadCloudStorage($surat, $activeStorage->id));
                                }
                                
                            }
                            NotificationHelper::createNotification($user->id, $keterangan,$url, $type);
                        }
                    } else {
                        $activeStorages = CloudStorage::where('status', 'active')->where('user_id',$user_id)->where('personal', true)->get();
                        foreach ($activeStorages as $key => $activeStorage) {
                            dispatch(new UploadCloudStorage($surat, $activeStorage->id));
                        }
                        $notif = Notifikasi::create([
                            'user_id' => $user_id,
                            'keterangan' => "DISPOSISI SURAT MASUK : <br> Nomor : <b>" . $surat->nomor_surat . "</b> <br> Perihal : <b>" . $surat->perihal . "</b> <br> Sifat : <b>" . $surat->perihal . "</b>",
                            'url' => $url,
                            'type' => $type
                        ]);
                        NotificationHelper::createNotification($user_id, $keterangan,$url, $type);
                    }
                }
                UserLogHelper::create('memberikan disposisi surat dengan nomor : ' . $surat->nomor_surat);
                DB::commit();
                return redirect()->route('surat.index')->with('success', 'Surat berhasil di-disposisikan');
            } catch (\Throwable $th) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Surat gagal di-disposisikan : ' . $th->getMessage())->withInput($request->all());
            }
        } else {
            $movedFiles = [];
            DB::beginTransaction();
            try {
                $old_berkas_ids = $request->old_berkas_id;
                $berkas_ids = $surat->berkas->pluck('id')->toArray();
                $deleted_berkas_ids = array_diff($berkas_ids, $old_berkas_ids);
                $updateData = [
                    'nomor_surat' => $request->nomor_surat,
                    'tanggal_surat' => $request->tanggal_surat,
                    'perihal' => $request->perihal,
                    'sifat' => $request->sifat,
                    'isi' => $request->isi

                ];

                if ($surat->status == "diperiksa") {
                    $updateData['pemeriksa_id'] = $request->pemeriksa_id;
                }
                if ($request->pemeriksa_id != $surat->pemeriksa_id) {
                    $type = "info";
                    if ($request->sifat != "biasa") {
                        $type = "warning";
                    }
$keterangan =  "
Surat Masuk Perlu Diperiksa & Disposisi : n
Nomor : *".$surat->nomor_surat ."* \n
Perihal : *".$surat->perihal ."* \n
Sifat : *".$surat->perihal ."* \n
Silahkan Login Ke Web Aplikasi Untuk Segera Memeriksa Surat Masuk Dan Meneruskan Disposisi";
                $url = 'surat/' . $surat->id;
                $notif = Notifikasi::create([
                    'user_id' => $request->pemeriksa_id,
                    'keterangan' => "Surat Masuk Perlu Diperiksa & Disposisi : <br> Nomor : <b>" . $surat->nomor_surat . "</b> <br> Perihal : <b>" . $surat->perihal . "</b> <br> Sifat : <b>" . $surat->perihal . "</b>",
                    'url' => $url,
                    'type' => $type
                ]);
                    NotificationHelper::createNotification($request->pemeriksa_id,$keterangan, $url, $type);
                }
                $surat->update($updateData);


                $tmpFiles = StorageHelper::getTmpFiles();
                foreach ($tmpFiles as $key => $tmpFile) {
                    $_path = 'surat/' . $surat->id . '/' . basename($tmpFile['path']);
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

                $surat->berkas()->whereIn('id', $deleted_berkas_ids)->delete();


                UserLogHelper::create('mengubah surat baru dengan nomor : ' . $surat->nomor_surat);
                DB::commit();
                if (isset($request->all_storage) && $request->all_storage == "true") {
                    $activeStorages = CloudStorage::where('status', 'active')->where('personal', false)->get();
                } else {
                    $activeStorages = CloudStorage::where('status', 'active')->where('personal', false)->whereIn('id', $request->cloud_storage_id)->get();
                }
                foreach ($activeStorages as $key => $activeStorage) {
                    dispatch(new UploadCloudStorage($surat, $activeStorage->id));
                }

                return redirect()->route('surat.index')->with('success', 'Surat berhasil diubah');
            } catch (\Throwable $th) {
                //throw $th;
                //check movedFiles
                foreach ($movedFiles as $key => $movedFile) {
                    $np = str_replace('surat/', '_tmp/', $movedFile);
                    Storage::move($movedFile, $np);
                }
                return redirect()->back()->with('error', 'Surat gagal diubah : ' . $th->getMessage())->withInput($request->all());
            }
        }

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
                foreach ($b->storages as $s) {
                    try {
                        $s->deleteFile($s->path);
                        $s->delete();
                    } catch (\Throwable $th) {
                        //throw $th;
                        Log::error($th);
                    }
                }
            }
            Notifikasi::where('user_id', auth()->user()->id)->where('url', 'like', '%surat/' . $surat->id . '%')->delete();
            $surat->disposisis()->delete();
            $surat->delete();
            UserLogHelper::create('menghapus surat dengan nomor : ' . $surat->nomor_surat);
            DB::commit();
            return redirect()->route('surat.index')->with('success', 'Surat berhasil dihapus');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('surat.index')->with('error', 'Surat gagal di hapus : ' . $th->getMessage());

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
            if ($zipArchive->open($path . $zip_name, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== TRUE) {
                return redirect()->back()->with('error', 'Gagal membuat zip file (1)');
            } else {

                try {
                    $zipArchive->addFile($path . 'tmp_' . $file_name, $file_name);
                    foreach ($surat->berkas as $berkas) {
                        // $isLocal = $berkas->storages()->where('type', 'local')->count();
                        // if ($isLocal > 0) {
                        //     $zipArchive->addGlob(storage_path() . '/app/'.$berkas->path);
                        // }
                        $zipArchive->addFile(storage_path() . '/app/' . $berkas->path, basename($berkas->path));
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
            //change fila name
            $file_name = $berkas->nama_berkas;
            $file_name = str_replace('/', '-', $file_name);
            $file_name = str_replace(' ', '_', $file_name);
            return response($get, 200)
                ->header('Content-Type', $mime)
                ->header('Content-Disposition', 'inline; filename="' . $file_name . '"');
        }

        abort(404);
    }

    public function disposisi(Request $request, Surat $surat)
    {
        $cek = SuratDisposisi::where('surat_id', $surat->id)->where(function ($w) {
            $w->where(function ($wu) {
                $wu->where('user_id', auth()->user()->id)->where('role_id', '!=', 0);
            })->orWhere(function ($wr) {
                $wr->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())->where('user_id', null);
            });
        })->first();
        if ($cek == null) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan disposisi');
        }
        DB::beginTransaction();
        try {
            //update disposisi
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
                NotificationHelper::createNotification($surat->pemeriksa_id, 'Surat : ' . $surat->nomor_surat . '. Ditolak oleh ' . Auth::user()->nama, 'surat/' . $surat->id, "danger");
                $_mp_id = $surat->disposisis()
                    ->whereNotNull('menunggu_persetujuan_id')
                    ->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())
                    ->where(function ($w) {
                        $w->where('user_id', auth()->user()->id)->orWhere('user_id', null);
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
                $keterangan = 
'Surat : ' . $surat->nomor_surat . '. Diterima oleh ' . Auth::user()->nama;
                $url = 'surat/' . $surat->id;
                $type = "success";
                $notif = Notifikasi::create([
                    'user_id' => $surat->user_id,
                    'keterangan' => $keterangan,
                    'url' => $url,
                    'type' => $type
                ]);
                NotificationHelper::createNotification($surat->user_id,$keterangan, $url,$type);
                NotificationHelper::createNotification($surat->pemeriksa_id,$keterangan, $url,$type);
//                 foreach ($surat->disposisi_berikutnya as $db) {
//                     if ($db->menunggu_persetujuan_id != null) {
//                         $_cek = $surat->disposisis()->where('role_id', $db->menunggu_persetujuan_id)->where('status', 'diterima')->count();
//                         if ($_cek <= 0) {
//                             continue;
//                         }
//                     }
//                     if ($db->user_id == 0) {
//                         $users = User::whereHas('roles', function ($wr) use ($db) {
//                             $wr->where('id', $db->role_id);
//                         })->get();
// $keterangan = "
// *DISPOSISI SURAT MASUK* : \n
// Nomor : *".$surat->nomor_surat ."* \n
// Perihal : *".$surat->perihal ."* \n
// Sifat : *".$surat->perihal ."* \n
// Silahkan Login Ke Aplikasi Web Untuk Melihat Dan Memeriksa Disposisi Surat Masuk";
//                             $url = 'surat/' . $surat->id;
//                             $type = "info";
//                         foreach ($users as $user) {
//                             $notif = Notifikasi::create([
//                                 'user_id' => $user->id,
//                                 'keterangan' => $keterangan,
//                                 'url' => $url,
//                                 'type' => $type
//                             ]);
//                             NotificationHelper::createNotification($user->id,$keterangan, $url,$type);
//                         }
//                     } else {
//                         NotificationHelper::createNotification($db->user_id, $keterangan, $url,$type);
//                     }
//                 }
            }



            //update notifikasi jadi read
            Notifikasi::where('user_id', auth()->user()->id)->where('url', 'like', '%surat/' . $surat->id . '%')->update([
                'is_read' => true
            ]);
            UserLogHelper::create('melakukan disposisi surat dengan nomor : ' . $surat->nomor_surat);
            DB::commit();
            $_ditolak = SuratDisposisi::where('surat_id', $surat->id)->where('status', 'ditolak')->count();
            $_diterima = SuratDisposisi::where('surat_id', $surat->id)->where('status', 'diterima')->count();
            $_all = SuratDisposisi::where('surat_id', $surat->id)->count();
            if ($_ditolak > 0) {
                $cek->surat->update([
                    'status' => 'ditolak'
                ]);
            } elseif ($_diterima == $_all) {
                $cek->surat->update([
                    'status' => 'selesai'
                ]);
            } elseif ($_diterima > 0) {
                $cek->surat->update([
                    'status' => 'proses'
                ]);
            }
            if ($request->status == "diterima") {
                return redirect()->route('surat.index')->with('success', 'Berhasil konfirmasi menerima disposisi');
            } else {
                return redirect()->route('surat.index')->with('success', 'Berhasil konfirmasi menolak disposisi');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->status == "diterima") {
                return redirect()->back()->with('error', 'Gagal untuk melakukan konfirmasi disposisi. ' . $th->getMessage())->withInput($request->all());
            } else {
                return redirect()->back()->with('error', 'Gagal untuk menolak disposisi. ' . $th->getMessage())->withInput($request->all());
            }

        }



    }

    public function ubahLampiran(Request $request)
    {   
        $request->validate([
            'nama_berkas.*' => 'required',
        ]);
        foreach ($request->nama_berkas as $berkas_id => $nama_berkas) {
            $berkas = Berkas::find($berkas_id);
            $old_nama = $berkas->nama_berkas;
            $old_extension = pathinfo($old_nama, PATHINFO_EXTENSION);
            $new_extension = pathinfo($nama_berkas, PATHINFO_EXTENSION);
            if ($old_extension != $new_extension) {
                $nama_berkas = $nama_berkas . '.' . $old_extension;
            }

            $nama_berkas = str_replace('/', '-', $nama_berkas);
            $nama_berkas = str_replace(' ', '_', $nama_berkas);
            $berkas->update([
                'nama_berkas' => $nama_berkas
            ]);
        }

        return redirect()->back()->with('success', 'Berhasil mengubah nama lampiran');
    }

}
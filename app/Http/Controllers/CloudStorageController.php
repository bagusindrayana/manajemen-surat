<?php

namespace App\Http\Controllers;

use App\Models\CloudStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PulkitJalan\Google\Facades\Google;

class CloudStorageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cloudStorages = CloudStorage::paginate(10);
        $data = [
            'cloudStorages' => $cloudStorages,
            'title' => 'Cloud Storage'
        ];
        request()->session()->forget('token');
        request()->session()->forget('cs_id');
        return view('cloud-storage.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        request()->session()->forget('token');
        request()->session()->forget('cs_id');
        return view('cloud-storage.create');
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
            'name' => 'required',
            'type' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $cs = CloudStorage::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'type' => $request->type,
            ]);
            if ($request->type == "google") {
                $cs->update([
                    'status' => 'inactive'
                ]);
                DB::commit();
                return redirect()->route('login.google')->with('cs_id', $cs->id);
            }
            if($request->type == "local"){
                $cs->update([
                    'setting_json' => json_encode([
                        'directory_name' => $request->directory_name
                    ])
                ]);
            }

            if($request->type == "s3"){
                $cs->update([
                    'setting_json' => json_encode([
                        'access_key_id' => $request->access_key_id,
                        'secret_access_key' => $request->secret_access_key,
                        'region' => $request->region,
                        'bucket' => $request->bucket ?? 'us-east-1',
                    ])
                ]);
            }

            if($request->type == "ftp"){
                $cs->update([
                    'setting_json' => json_encode([
                        'host' => $request->host,
                        'port' => $request->port,
                        'username' => $request->username,
                        'password' => $request->password,
                        'root' => $request->root,
                    ])
                ]);
            }
            DB::commit();
            return redirect()->route('cloud-storage.index')->with('success','Storage berhasil dibuat');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error','Storage gagal dibuat')->withInput($request->all());
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CloudStorage  $cloudStorage
     * @return \Illuminate\Http\Response
     */
    public function show(CloudStorage $cloudStorage)
    {
        
        $files = [];

        if($cloudStorage->setting_json != null){
            // $drive = Google::make('drive');
            // $drive->getClient()->setAccessType('offline');
            // $drive->getClient()->setApprovalPrompt("force");
            // $json = json_decode($cloudStorage->setting_json);
            // $drive->getClient()->setAccessToken($json->access_token);
            // $optParams = array(
            //     'pageSize' => 10,
            //     'fields' => 'nextPageToken, files(id, name, size, mimeType, webViewLink, webContentLink, iconLink, trashed, createdTime, modifiedTime)',
            //     'q' => "trashed=false and '$json->folder_id' in parents"
            // );
            // $files = $drive->files->listFiles($optParams)->files;
            $files = $cloudStorage->listFiles;
           
        }

        $data = [
            'cloudStorage' => $cloudStorage,
            'title' => 'Detail Cloud Storage',
            'subtitle'=> 'Berkas-berkas yang sudah di upload ke storage ini',
            'files'=>$files
        ];
        return view('cloud-storage.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CloudStorage  $cloudStorage
     * @return \Illuminate\Http\Response
     */
    public function edit(CloudStorage $cloudStorage)
    {
        $data = [
            'cloudStorage' => $cloudStorage,
            'title' => 'Ubah Cloud Storage',
            
        ];
        return view('cloud-storage.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CloudStorage  $cloudStorage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CloudStorage $cloudStorage)
    {
        $request->validate([
            'name' => 'required',
            'status'=>'required'
        ]);
        $cloudStorage->update([
            'name' => $request->name,
            'type' => $request->ubah_type ? $request->type : $cloudStorage->type,
            'status' => $request->status
        ]);

        if($cloudStorage->type == "s3"){
            $cloudStorage->update([
                'setting_json' => json_encode([
                    'access_key_id' => $request->access_key_id,
                    'secret_access_key' => $request->secret_access_key,
                    'region' => $request->region,
                    'bucket' => $request->bucket ?? 'us-east-1',
                ])
            ]);
        }

        if($cloudStorage->type == "ftp"){
            $cloudStorage->update([
                'setting_json' => json_encode([
                    'host' => $request->host,
                    'port' => $request->port,
                    'username' => $request->username,
                    'password' => $request->password,
                    'root' => $request->root,
                ])
            ]);
        }
        if ($request->type == "google") {
            return redirect()->route('login.google')->with('cs_id', $cloudStorage->id);
        }
        return redirect()->route('cloud-storage.index')->with('success','Storage berhasil dibuat');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CloudStorage  $cloudStorage
     * @return \Illuminate\Http\Response
     */
    public function destroy(CloudStorage $cloudStorage)
    {   
        DB::beginTransaction();
        try {
            $cloudStorage->delete();
            DB::commit();
            return redirect()->route('cloud-storage.index')->with('success','Storage berhasil dihapus');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error','Storage gagal dihapus');
        }
    }
}
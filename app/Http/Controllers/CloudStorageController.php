<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use App\Models\CloudStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Google\Service\Drive\DriveFile;
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
        if(!auth()->user()->can('View Cloud Storage'))
            return abort(403,'Anda tidak memiliki cukup hak akses');
        $cloudStorages = CloudStorage::where('personal',false)->filtersInput(null, 'search')->orderBy('created_at', 'desc')->paginate(10);
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
                return redirect()->route('cloud-storage.login-google')->with('cs_id', $cs->id);
            }
            if ($request->type == "local") {
                $cs->update([
                    'setting_json' => json_encode([
                        'directory_name' => $request->directory_name
                    ])
                ]);
            }

            if ($request->type == "s3") {
                $cs->update([
                    'setting_json' => json_encode([
                        'access_key_id' => $request->access_key_id,
                        'secret_access_key' => $request->secret_access_key,
                        'region' => $request->region,
                        'bucket' => $request->bucket ?? 'us-east-1',
                    ])
                ]);
            }

            if ($request->type == "ftp") {
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
            return redirect()->route('cloud-storage.index')->with('success', 'Storage berhasil dibuat');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error', 'Storage gagal dibuat')->withInput($request->all());
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

        if($cloudStorage->personal){
            return abort(404);
        }
        $files = [];

        if ($cloudStorage->setting_json != null) {
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
            'subtitle' => 'Berkas-berkas yang sudah di upload ke storage ini',
            'files' => $files
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
        if($cloudStorage->personal){
            return abort(404);
        }
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
        if($cloudStorage->personal){
            return abort(404);
        }
        $request->validate([
            'name' => 'required',
            'status' => 'required'
        ]);
        $cloudStorage->update([
            'name' => $request->name,
            'type' => $request->ubah_type ? $request->type : $cloudStorage->type,
            'status' => $request->status
        ]);

        if ($cloudStorage->type == "s3") {
            $cloudStorage->update([
                'setting_json' => json_encode([
                    'access_key_id' => $request->access_key_id,
                    'secret_access_key' => $request->secret_access_key,
                    'region' => $request->region,
                    'bucket' => $request->bucket ?? 'us-east-1',
                ])
            ]);
        }

        if ($cloudStorage->type == "ftp") {
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
            return redirect()->route('cloud-storage.login-google')->with('cs_id', $cloudStorage->id);
        }
        return redirect()->route('cloud-storage.index')->with('success', 'Storage berhasil dibuat');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CloudStorage  $cloudStorage
     * @return \Illuminate\Http\Response
     */
    public function destroy(CloudStorage $cloudStorage)
    {   
        if($cloudStorage->personal && $cloudStorage->user_id != Auth::user()->id){
            return abort(404);
        }
        DB::beginTransaction();
        try {
            $cloudStorage->delete();
            if($cloudStorage->setting != null){
                if($cloudStorage->setting->access_token != null){
                    $oauth2 = Google::make('oauth2');
                    $setting = StorageHelper::createRefreshToken($cloudStorage);
                    $oauth2->getClient()->setAccessType('offline');
                    $oauth2->getClient()->setApprovalPrompt("force");
                    $oauth2->getClient()->setAccessToken($setting->access_token);
                    //disconnect
                    $oauth2->getClient()->revokeToken();
                }
            }
            DB::commit();
            return redirect()->route('cloud-storage.index')->with('success', 'Storage berhasil dihapus');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error', 'Storage gagal dihapus');
        }
    }

    public function authGoogle(Request $request)
    {
        $oauth2 = Google::make('oauth2');
        $oauth2->getClient()->setAccessType('offline');
        //set redirect uri
        $oauth2->getClient()->setRedirectUri(url('cloud-storage/login-google'));
        $oauth2->getClient()->setApprovalPrompt("force");
        $oauth2->getClient()->setScopes(
            array(
                // 'https://www.googleapis.com/auth/plus.me',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/drive.file',
                // 'https://www.googleapis.com/auth/drive'
            )
        );
        $request->session()->put('cs_id', $request->session()->get('cs_id'));
        if ($request->get('code')) {
            $oauth2->getClient()->authenticate($request->get('code'));
            $request->session()->put('token', $oauth2->getClient()->getAccessToken());
            $oauth2->getClient()->setState($request->session()->get('cs_id')."|".$request->get('code'));
        }
        if ($request->session()->get('token')) {
            $oauth2->getClient()->setAccessToken($request->session()->get('token'));
        }
        if ($oauth2->getClient()->getAccessToken()) {
            $goole_user = $oauth2->userinfo->get();
            if ($request->get('state') && $request->get('code')) {
                $state = explode("|", $request->get('state'));
                $json = $oauth2->getClient()->getAccessToken();
                $json['code'] = $request->get('code');
                $json['refresh_token'] = $oauth2->getClient()->getRefreshToken();
                $optParams = array(
                    'fields' => 'nextPageToken, files(id, name, trashed, createdTime, modifiedTime)',
                    'q' => "trashed=false and name='_manajemen_surat'"
                );
                $drive = Google::make('drive');
                $drive->getClient()->setAccessType('offline');
                $drive->getClient()->setApprovalPrompt("force");
                $drive->getClient()->setAccessToken($json['access_token']);
                //cek dir
                $CEK = $drive->files->listFiles($optParams)->files;
                if(count($CEK)>0){
                    $folder = $CEK[0];
                } else {
                    //create new folder
                    $fileMetadata = new DriveFile(
                        array(
                            'name' => '_manajemen_surat',
                            'mimeType' => 'application/vnd.google-apps.folder'
                        )
                    );
                    $folder = $drive->files->create($fileMetadata,[
                        'fields' => 'id'
                    ]);
                }

                //update data
                $id = $state[0];
                $cs = CloudStorage::find($id);
                if(!$cs){
                    return "storage not found";
                }
                $json['folder_id'] = $folder->id;
                $cs->update([
                    'auth_name' => $goole_user['email'],
                    'status' => 'active',
                    'setting_json' => $json
                ]);

                //clear session
                $request->session()->forget('token');
                $request->session()->forget('cs_id');
                return redirect()->route('cloud-storage.index')->with('success', 'Google Drive has been connected successfully');
            } else {
                return abort(404);
            }
            //dd($goole_user);

            //$request->session()->put('name', $goole_user['name']);
            // if ($set_user = User::where('email',$goole_user['email'])->first())
            // {
            // 	//logged your user via auth login
            // }else{
            // 	//register your user with response data
            // }               

            //return redirect()->route('login.google-success');          
        } else {
            //For Guest user, get google login url
            $oauth2->getClient()->setState( $request->session()->get('cs_id')."|");
            $get_authUrl = $oauth2->getClient()->createAuthUrl();
            return redirect()->to($get_authUrl);
        }

    }

    public function successAuthGoogle()
    {
        $oauth2 = Google::make('oauth2');
        if (request()->session()->get('token')) {
            $oauth2->getClient()->setAccessToken(request()->session()->get('token'));
        }
        if ($oauth2->getClient()->getAccessToken()) {
            //For logged in user, get details from google using access token
            $goole_user = $oauth2->userinfo->get();
            dd($goole_user);
        }
        dd($oauth2);
    }
}
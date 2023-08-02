<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CloudStorage;
use Google\Service\Drive\DriveFile;
use PulkitJalan\Google\Facades\Google;

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
            'nama'=>'required|max:150',
            'username'=>'required|min:5|max:20|unique:users,username,'.Auth::user()->id,
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
                if($request->type[$index] == "wa" || $request->type[$index] == "email"){
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
                
            }
            DB::commit();
            return redirect()->back()->with('success','Profil berhasil diupdate');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error','Profil gagal diupdate : '.$th->getMessage());
        }
        
    }

    public function authGoogle(Request $request)
    {   
        
        $oauth2 = Google::make('oauth2');
        $client = $oauth2->getClient();
        //set redirect uri
        $client->setRedirectUri(url('profil/login-google'));
        $client->setAccessType('offline');
        $client->setApprovalPrompt("force");
        $client->setScopes(
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
            $client->authenticate($request->get('code'));
            $request->session()->put('token', $client->getAccessToken());
            $client->setState($request->session()->get('cs_id')."|".$request->get('code'));
        }
        if ($request->session()->get('token')) {
            $client->setAccessToken($request->session()->get('token'));
        }
        if ($client->getAccessToken()) {
            $goole_user = $oauth2->userinfo->get();
            if ($request->get('state') && $request->get('code')) {
                $state = explode("|", $request->get('state'));
                $json = $client->getAccessToken();
                $json['code'] = $request->get('code');
                $json['refresh_token'] = $client->getRefreshToken();
                $optParams = array(
                    'fields' => 'nextPageToken, files(id, name, trashed, createdTime, modifiedTime)',
                    'q' => "trashed=false and name='_disposisi_surat'"
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
                            'name' => '_disposisi_surat',
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
                return redirect()->route('profil.index')->with('success', 'Google Drive has been connected successfully');
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

    public function tambahCloudStorage(Request $request)
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
                'personal'=>true
            ]);
            if ($request->type == "google") {
                $cs->update([
                    'status' => 'inactive'
                ]);
                DB::commit();
                return redirect()->route('profil.login-google')->with('cs_id', $cs->id);
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
            return redirect()->route('profile.index')->with('success', 'Storage berhasil dibuat');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error', 'Storage gagal dibuat')->withInput($request->all());
        }
    }

    public function updateCloudStorage (Request $request,$id)
    {   
        $cs = CloudStorage::find($id);
        return redirect()->route('profil.login-google')->with('cs_id', $cs->id);
        $request->validate([
            'name' => 'required',
            'type' => 'required',
        ]);
        DB::beginTransaction();
        try {
            
            $cs->update([
                'name' => $request->name,
                'type' => $request->type,
                'personal'=>true
            ]);
            if ($request->type == "google") {
                $cs->update([
                    'status' => 'inactive'
                ]);
                DB::commit();
                return redirect()->route('profil.login-google')->with('cs_id', $cs->id);
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
            return redirect()->route('profile.index')->with('success', 'Storage berhasil dibuat');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error', 'Storage gagal dibuat')->withInput($request->all());
        }
    }

    public function hapusCloudStorage($id)
    {
        $cloudStorage = CloudStorage::find($id);
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
            return redirect()->route('profil.index')->with('success', 'Storage berhasil dihapus');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error', 'Storage gagal dihapus');
        }
    }
}


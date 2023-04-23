<?php

namespace App\Http\Controllers;

use App\Models\CloudStorage;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\Request;
use PulkitJalan\Google\Facades\Google;

class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function auth(Request $request)
    {
        //check auth
        $credentials = $request->only('username', 'password');

        if (auth()->attempt($credentials)) {
            // Authentication passed...
            //check intended url
            if ($request->session()->has('url.intended')) {
                return redirect()->intended();
            }
            return redirect()->route('home');
        }

        return redirect()->route('login')->with('error', 'Username or password is incorrect');
    }

    public function authGoogle(Request $request)
    {   
        
        $oauth2 = Google::make('oauth2');
        $oauth2->getClient()->setAccessType('offline');
        $oauth2->getClient()->setApprovalPrompt("force");
        $oauth2->getClient()->setScopes(
            array(
                'https://www.googleapis.com/auth/plus.me',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/drive.file',
                'https://www.googleapis.com/auth/drive'
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
            
            //$oauth2->getClient()->setState($request->get('state'));
        }
        if ($oauth2->getClient()->getAccessToken()) {
            
            //For logged in user, get details from google using access token
            $goole_user = $oauth2->userinfo->get();
            if ($request->get('state') && $request->get('code')) {
                $state = explode("|", $request->get('state'));
                //create folder
                
                
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
                $CEK = $drive->files->listFiles($optParams)->files;
                if(count($CEK)>0){
                    $folder = $CEK[0];
                } else {
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

    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }
}
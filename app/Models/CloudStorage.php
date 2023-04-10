<?php

namespace App\Models;

use App\Helpers\StorageHelper;
use Google\Service\Drive\DriveFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use PulkitJalan\Google\Facades\Google;

class CloudStorage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'auth_name',
        'type',
        'setting_json',
        'status',
    ];

    public function getSettingAttribute()
    {
        //string to json to object
        return json_decode($this->setting_json);
    }

    public function getListFilesAttribute()
    {
        $files = [];
        if ($this->setting_json == null) {
            return $files;
        }
        switch ($this->type) {
            case 'google':

                try {
                    $drive = Google::make('drive');
                    $setting = StorageHelper::createRefreshToken($this);
                    $drive->getClient()->setAccessType('offline');
                    $drive->getClient()->setApprovalPrompt("force");
                    $drive->getClient()->setAccessToken($setting->access_token);
                    $optParams = array(
                        'pageSize' => 10,
                        'fields' => 'nextPageToken, files(id, name, size, mimeType, webViewLink, webContentLink, iconLink, trashed, createdTime, modifiedTime)',
                        'q' => "trashed=false and '$setting->folder_id' in parents"
                    );
                    $files = $drive->files->listFiles($optParams)->files;
                } catch (\Throwable $th) {
                    dd($th);
                }
                break;
            case 'local':
                $storageFiles = Storage::files($this->setting->directory_name."/".Auth::user()->id);
                foreach ($storageFiles as $sf) {
                    $files[] = json_decode(json_encode([
                        'name' => basename($sf),
                        'size' => Storage::size($sf),
                        'url' => Storage::url($sf),
                        'createdTime' => Storage::lastModified($sf),
                        'mimeType' => Storage::mimeType($sf),
                    ]), FALSE);
                }
                break;
            case 's3':
                $setting = $this->setting;
                $storage = Storage::createS3Driver([
                    'driver' => 's3',
                    'key'    => $setting->access_key_id,
                    'secret' => $setting->secret_access_key,
                    'region' => $setting->region ?? 'us-east-1',
                    'bucket' => $setting->bucket,
                    'endpoint'=>$setting->endpoint,
                ]);
                $storageFiles = $storage->files("_manajemen_surat/".Auth::user()->id);
                foreach ($storageFiles as $sf) {
                    $files[] = json_decode(json_encode([
                        'name' => basename($sf),
                        'size' => $storage->size($sf),
                        'url' => $storage->url($sf),
                        'createdTime' => $storage->lastModified($sf),
                        'mimeType' => $storage->mimeType($sf),
                    ]), FALSE);
                }
                break;
            case 'ftp':
                $setting = $this->setting;
                $storage = Storage::createFtpDriver([
                    'driver' => 'ftp',
                    'host'    => $setting->host,
                    'port' => $setting->port,
                    'username' => $setting->username,
                    'password' => $setting->password,
                    'root'=>$setting->root,
                ]);
                $storageFiles = $storage->files("_manajemen_surat/".Auth::user()->id);
                foreach ($storageFiles as $sf) {
                    $files[] = json_decode(json_encode([
                        'name' => basename($sf),
                        'size' => $storage->size($sf),
                        'url' => '-',
                        'createdTime' => $storage->lastModified($sf),
                        'mimeType' => $storage->mimeType($sf),
                    ]), FALSE);
                }
                break;
                
            default:
                # code...
                break;
        }
        return $files;
    }

    public function scopeUploadRequest(UploadedFile $file)
    {
        $setting = $this->setting;
        $result = null;
        switch ($this->type) {
            case 'google':
                $drive = Google::make('drive');
                $setting = StorageHelper::createRefreshToken($this);
                $drive->getClient()->setAccessToken($setting->access_token);
                $drive->getClient()->getAccessToken();


                // instansiasi obyek file yg akan diupload ke Google Drive
                $d_file = new DriveFile();
                // set nama file di Google Drive disesuaikan dg nama file aslinya
                $d_file->setName($file->getClientOriginalName());
                // proses upload file ke Google Drive dg multipart
                $result = $drive->files->create(
                    $d_file,
                    array(
                        'data' => file_get_contents($file->getRealPath()),
                        'mimeType' => $file->getMimeType(),
                        'uploadType' => 'multipart'
                    )
                );
                break;
            case 'local':
                $_path = $setting->directory_name . '/' . Auth::user()->id;
                $file->storeAs($_path, $file->getClientOriginalName());
                break;
            default:
                # code...
                break;
        }

        return $result;
    }

    public function scopeUploadFile($q, $path)
    {
        $setting = $this->setting;
        $result = null;
        switch ($this->type) {
            case 'google':

                $drive = Google::make('drive');
                $setting = StorageHelper::createRefreshToken($this);
                $drive->getClient()->setAccessType('offline');
                $drive->getClient()->setApprovalPrompt("force");
                $drive->getClient()->setAccessToken($setting->access_token);

                $folder_id = $setting->folder_id;

                $d_file = new DriveFile();
                $d_file->setName(basename($path));
                $d_file->setParents([$folder_id]);

                // proses upload file ke Google Drive dg multipart
                $_upload = $drive->files->create(
                    $d_file,
                    array(
                        'data' => Storage::get($path),
                        'mimeType' => 'application/octet-stream',
                        'uploadType' => 'multipart'
                    )
                );

                //result path
                $result = $_upload->id;
               
                break;
            case 'local':
                $result = $_path = $setting->directory_name . '/' . Auth::user()->id . '/' . basename($path);
                Storage::move($path, $_path);
                break;
            case 's3':
                $result = $_path = '_manajemen_surat/' . Auth::user()->id . '/' . basename($path);
                $storage = Storage::createS3Driver([
                    'driver' => 's3',
                    'key'    => $setting->access_key_id,
                    'secret' => $setting->secret_access_key,
                    'region' => $setting->region ?? 'us-east-1',
                    'bucket' => $setting->bucket,
                    'endpoint'=>$setting->endpoint,
                ]);
                try {
                    $content = Storage::get($path);
                    $cek = $storage->put($_path, $content);
                    if(!$cek){
                       throw new \Exception("Gagal upload ke s3");
                    }
                } catch (\Aws\S3\Exception\S3Exception $th) {
                    throw new \Exception($th->getMessage());
                }
                
                
                break;
            case 'ftp':
                $result = $_path = '_manajemen_surat/' . Auth::user()->id . '/' . basename($path);
                $storage = Storage::createFtpDriver([
                    'driver' => 'ftp',
                    'host'    => $setting->host,
                    'port' => $setting->port,
                    'username' => $setting->username,
                    'password' => $setting->password,
                    'root'=>$setting->root,
                ]);
                try {
                    $content = Storage::get($path);
                    $cek = $storage->put($_path, $content);
                    if(!$cek){
                        throw new \Exception("Gagal upload ke ftp");
                    }
                } catch (\Aws\S3\Exception\S3Exception $th) {
                    throw new \Exception($th->getMessage());
                }
                
                
                break;
            default:
                # code...
                break;
        }

        return $result;
    }

    
}
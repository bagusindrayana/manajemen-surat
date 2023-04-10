<?php
namespace App\Helpers;

use App\Models\CloudStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PulkitJalan\Google\Facades\Google;

class StorageHelper {
    public static function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
    
        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 
    
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 

    public static function getTmpFiles()
    {   
        $user = Auth::user();
        //get list file from /storage/app/_temp
        $files = Storage::files('_tmp/'.$user->id);
        $tempFile = [];
        foreach ($files as $file) {
            $tempFile[] = [
                'name' => basename($file),
                'size'=>Storage::size($file),
                'size_format' => self::formatBytes(Storage::size($file)),
                'url' => Storage::url($file),
                'path'=> $file,
                'mime_type' => Storage::mimeType($file),
            ];
        }
        return $tempFile;
    }

    public static function createRefreshToken(CloudStorage $cloudStorage)
    {
        $setting = $cloudStorage->setting;
        $expires_in = $setting->expires_in;
        $created = $setting->created;
        $created_1_hour = $created;
        //request new access token if expired
        $drive = Google::make('drive');
        if($expires_in < time()){
            
            $drive->getClient()->refreshToken($setting->refresh_token);
            $_tmp = $drive->getClient()->getAccessToken();
            $_tmp['code'] = $setting->code;
            $_tmp['folder_id'] = $setting->folder_id;

            $setting = (object)$_tmp;

            $setting->expires_in = $drive->getClient()->getAccessToken()['created'] + $drive->getClient()->getAccessToken()['expires_in'];
            $cloudStorage->update([
                'setting_json' => json_encode($setting)
            ]);
        }
       

        $cloudStorage->update([
            'setting_json' => json_encode($setting)
        ]);
        return $setting;

    }

    public static function localDiskInfo()
    {
        $disktotal = disk_total_space('/'); //DISK usage
        $disktotalsize = $disktotal / 1073741824;

        $diskfree  = disk_free_space('/');
        $used = $disktotal - $diskfree;

        $diskusedize = $used / 1073741824;
        $diskuse1   = round(100 - (($diskusedize / $disktotalsize) * 100));
        $diskuse = round(100 - ($diskuse1)) . '%';
        return [
            'total' => $disktotalsize,
            'used' => $diskusedize,
            'free' => $diskfree / 1073741824,
            'use' => $diskusedize,
        ];
    }

    

    
}
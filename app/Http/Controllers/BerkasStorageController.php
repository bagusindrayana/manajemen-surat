<?php

namespace App\Http\Controllers;

use App\Models\BerkasStorage;
use Illuminate\Http\Request;

class BerkasStorageController extends Controller
{
    public function view($berkas_storage_id)
    {
        $bs = BerkasStorage::find($berkas_storage_id);
        switch ($bs->storage->type) {
            case 'google':
                return redirect()->away("https://drive.google.com/file/d/".$bs->path."/view?usp=sharing");
                break;
            case 's3':
                $storage = $bs->storage->driver;
                $path = $bs->path;
                $file = $storage->get($path);
                $type = $storage->getMimetype($path);
                $response = response($file, 200)->header('Content-Type', $type);
                return $response;
                break;
            case 'ftp':
                $storage = $bs->storage->driver;
                $path = $bs->path;
                $file = $storage->get($path);
                $type = $storage->getMimetype($path);
                $response = response($file, 200)->header('Content-Type', $type);
                return $response;
                break;
            default:
                return response()->json([
                    'message' => 'Tipe storage tidak dikenali'
                ], 400);
                break;
        }
    }
}

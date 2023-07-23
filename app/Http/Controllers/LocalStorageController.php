<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use Illuminate\Http\Request;

class LocalStorageController extends Controller
{
    public function index()
    {   
        if(!auth()->user()->can('View Cloud Storage'))
            return abort(403,'Anda tidak memiliki cukup hak akses');
        $localDiskInfo = StorageHelper::localDiskInfo();
        return view('local-storage.index',compact('localDiskInfo'));
    }
}

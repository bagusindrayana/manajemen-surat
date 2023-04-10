<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use Illuminate\Http\Request;

class LocalStorageController extends Controller
{
    public function index()
    {   
        $localDiskInfo = StorageHelper::localDiskInfo();
        return view('local-storage.index',compact('localDiskInfo'));
    }
}

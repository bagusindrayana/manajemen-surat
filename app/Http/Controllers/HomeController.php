<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Providers\ChunkUploadServiceProvider;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class HomeController extends Controller
{
    public function index()
    {   
        return view('welcome');
    }
}

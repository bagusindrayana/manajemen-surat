<?php

use App\Http\Controllers\BerkasController;
use App\Http\Controllers\CloudStorageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalStorageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/login',[LoginController::class,'index'])->name('login');
Route::post('/login',[LoginController::class,'auth'])->name('login.auth');
Route::get('/login-google',[LoginController::class,'authGoogle'])->name('login.google');
Route::get('/success-login-google',[LoginController::class,'successAuthGoogle'])->name('login.google-success');

Route::group(['middleware'=>['auth']],function(){
    Route::post('/logout',[LoginController::class,'logout'])->name('login.logout');
    Route::get('/',[HomeController::class,'index'])->name('home');
    Route::resource('/role',RoleController::class);
    Route::resource('/cloud-storage',CloudStorageController::class);
    Route::resource('/surat',SuratController::class);
    Route::get('local-storage',[LocalStorageController::class,'index'])->name('local-storage.index');
    Route::get('surat/{surat}/download-pdf',[SuratController::class,'downloadPdf'])->name('surat.download-pdf');
    Route::get('surat/{surat}/view-berkas/{berkas}',[SuratController::class,'viewBerkas'])->name('surat.view-berkas');
    Route::post('surat/{surat}/disposisi',[SuratController::class,'disposisi'])->name('surat.disposisi');
    Route::resource('/user',UserController::class);
    Route::group(['prefix'=>'berkas'],function(){
        Route::post('upload', [BerkasController::class, 'upload'])->name('berkas.upload');
        Route::post('upload/delete', [BerkasController::class, 'uploadDelete'])->name('berkas.upload-delete');
    });

    Route::group(['prefix'=>'ajax'],function(){
        Route::get('user-by-role/{role}',[SuratController::class,'getUserByRole'])->name('ajax.user-by-role');
        Route::post('read-notification/{id}',[NotificationController::class,'readNotification'])->name('ajax.read-notification');
    });
});

Route::get('berkas-storage/{berkas_storage_id}',[BerkasStorageController::class,'view'])->name('berkas-storage.view');

Route::get('clear-cache',function(){
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    return "Cache is cleared";
});

Route::group(['prefix'=>'cron-job'],function(){
    Route::get('queue',function(){
        Artisan::call('queue:work --stop-when-empty');
        return "Queue is working";
    });
});





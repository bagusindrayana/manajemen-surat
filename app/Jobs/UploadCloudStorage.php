<?php

namespace App\Jobs;

use App\Helpers\StorageHelper;
use App\Models\CloudStorage;
use App\Models\KontakNotifikasi;
use App\Models\Surat;
use Google\Service\Drive\Permission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PulkitJalan\Google\Facades\Google;

class UploadCloudStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $surat;
    // public $request;
    // public $user_ids;
    public $cloud_storage_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        Surat $surat,
        // Array $request,
        // $user_ids = null,
        $cloud_storage_id
    ) {
        $this->surat = $surat;
        // $this->request = $request;
        // $this->user_ids = $user_ids;
        $this->cloud_storage_id = $cloud_storage_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->surat->berkas as $berkas) {
            $activeStorage = CloudStorage::find($this->cloud_storage_id);
            $uploadedResult = $activeStorage->uploadFile($berkas->path, $this->surat->id);

            //goole drive permission
            if($activeStorage->type == "google" && $activeStorage->status == "active" && $activeStorage->personal == false){
                $drive = Google::make('drive');
                $setting = StorageHelper::createRefreshToken($activeStorage);
                $drive->getClient()->setAccessType('offline');
                $drive->getClient()->setApprovalPrompt("force");
                $drive->getClient()->setAccessToken($setting->access_token);
                // $drive->getClient()->setAccessToken($setting->access_token);
                $drive->getClient()->getAccessToken();
                $drive->getClient()->setUseBatch(true);
                $batch = $drive->createBatch();
                
                if($this->surat->sifat == "biasa"){
                    $permission = new Permission();
                    $permission->setRole('reader');
                    $permission->setType('anyone');
                    $request = $drive->permissions->create(
                        $uploadedResult,
                        $permission
                    );
                    $batch->add($request, 'anyone');
                    $results = $batch->execute();
                } else if($this->surat->sifat != "rahasia") {
                    $user_ids = [$this->surat->pemeriksa_id,$this->surat->user_id];
                    $user_disposisis = $this->surat->disposisis->pluck('user_id')->toArray();
                    $user_ids = array_merge($user_ids,$user_disposisis);
                    $emails = KontakNotifikasi::where('type','email')->whereIn('user_id',$user_ids)->pluck('kontak')->toArray();

                    foreach ($emails as $email) {
                        $permission = new Permission();
                        $permission->setRole('reader');
                        $permission->setType('user');
                        $permission->setEmailAddress($email);
                        $request = $drive->permissions->create(
                            $uploadedResult,
                            $permission
                        );
                        $batch->add($request, $email);
                    }
                    $results = $batch->execute();
                    
                } else {
                    $emails = KontakNotifikasi::where('type','email')->pluck('kontak')->toArray();

                    foreach ($emails as $email) {
                        $permission = new Permission();
                        $permission->setRole('reader');
                        $permission->setType('user');
                        $permission->setEmailAddress($email);
                        $request = $drive->permissions->create(
                            $uploadedResult,
                            $permission
                        );
                        $batch->add($request, $email);
                    }
                    $results = $batch->execute();
                }
                
                
            }
            
            $berkas->berkas_storages()->create([
                'storage_id' => $activeStorage->id,
                'berkas_id' => $berkas->id,
                'path' => $uploadedResult,
            ]);

        }

        //$tmpFiles = StorageHelper::getTmpFiles();
        // $activeStorages = [];
        // if($this->user_ids != null && is_array($this->user_ids) && count($this->user_ids) > 0){
        //     $activeStorages = CloudStorage::where('status', 'active')->whereIn('user_id',$this->user_ids)->get();
        // } else {
        //     if (isset($this->request['all_storage']) && $this->request['all_storage'] == "true") {
        //         $activeStorages = CloudStorage::where('status', 'active')->where('personal',false)->get();
        //     } else {
        //         $activeStorages = CloudStorage::where('status', 'active')->where('personal',false)->whereIn('id',$this->request['cloud_storage_id'])->get();
        //     }
        // }


        // foreach ($this->surat->berkas as $berkas) {
        //     foreach ($activeStorages as $key => $activeStorage) {
        //         $uploadedResult = $activeStorage->uploadFile($berkas->path,$this->surat->id);
        //         $berkas->berkas_storages()->create([
        //             'storage_id' => $activeStorage->id,
        //             'berkas_id' => $berkas->id,
        //             'path' => $uploadedResult,
        //         ]);
        //     }
        // }

        // foreach ($tmpFiles as $key => $tmpFile) {
        //     $berkas = $this->surat->berkas()->create([
        //         'nama_berkas' => $tmpFile['name'],
        //         'path' => $tmpFile['path'],
        //         'mime_type' => $tmpFile['mime_type'],
        //         'size' => $tmpFile['size'],
        //     ]);

        //     foreach ($activeStorages as $key => $activeStorage) {
        //         $uploadedResult = $activeStorage->uploadFile($tmpFile['path']);
        //         $berkas->berkas_storages()->create([
        //             'storage_id' => $activeStorage->id,
        //             'berkas_id' => $berkas->id,
        //             'path' => $uploadedResult,
        //         ]);
        //     }
        //     $_path = 'surat/' . Auth::user()->id . '/' . basename($tmpFile['path']);
        //     $berkas->update([
        //         'path' => $_path
        //     ]);
        //     Storage::move($tmpFile['path'], $_path);
        // }
    }
}
<?php

namespace App\Jobs;

use App\Helpers\StorageHelper;
use App\Models\CloudStorage;
use App\Models\Surat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadCloudStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $surat;
    public $request;
    public $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Surat $surat,Array $request,$user_id)
    {
        $this->surat = $surat;
        $this->request = $request;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //$tmpFiles = StorageHelper::getTmpFiles();
        $activeStorages = [];
        if (isset($this->request['all_storage']) && $this->request['all_storage'] == "true") {
            $activeStorages = CloudStorage::where('status', 'active')->get();
        } else {
            $activeStorages = CloudStorage::where('status', 'active')->whereIn('id',$this->request['cloud_storage_id'])->get();
        }

        foreach ($this->surat->berkas as $berkas) {
            foreach ($activeStorages as $key => $activeStorage) {
                $uploadedResult = $activeStorage->uploadFile($berkas->path,$this->user_id);
                $berkas->berkas_storages()->create([
                    'storage_id' => $activeStorage->id,
                    'berkas_id' => $berkas->id,
                    'path' => $uploadedResult,
                ]);
            }
        }

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

<div class="mb-4">
    <label for="name">Storage Name</label>
    <input type="text" name="name" required class="form-control" id="name" aria-describedby="name"
        placeholder="Storage name..." value="{{ old('name', @$cloudStorage->name) }}">

</div>
<div x-data="{ ubahType: false }">
    @isset($cloudStorage)
        <div class="mb-4">
            <label class="my-1 me-2" for="status">Status</label> 
            <select class="form-select" id="status" name="status" aria-label="Status">
                <option value="active" @if($cloudStorage->status == 'active') selected @endif>Active</option>
                <option value="inactive" @if($cloudStorage->status == 'inactive') selected @endif>Inactive</option>
                <option value="optional" @if($cloudStorage->status == 'optional') selected @endif>Optional</option>
            </select>
        </div>
        <p>Type : {{ $cloudStorage->type }} | {{ $cloudStorage->auth_name }}</p>
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" value="true"
                x-model="ubahType" id="ubah_type" name="ubah_type"> <label class="form-check-label" for="ubah_type">Ubah
                Type</label></div>
        <div x-show="ubahType" id="type">
            <div class="mt-4">
                <button name="type" type="submit" value="google" class="btn btn-pill btn-outline-gray-500 me-2"><i
                        class="fab fa-google-drive"></i> Google Drive</button>
                <button name="type" type="button" value="local" class="btn btn-pill btn-outline-gray-500 me-2"><i
                        class="far fa-hdd"></i> Local</button>
                <button name="type" type="button" value="local" class="btn btn-pill btn-outline-gray-500 me-2"><i
                        class="fas fa-cloud"></i> S3</button>
            </div>
        </div>
        <div x-show="!ubahType">
            <button type="submit" class="btn btn-success mt-4">Simpan</button>
        </div>
    @else
        <button name="type" type="button" value="local" class="btn btn-pill btn-outline-gray-500 me-2" data-bs-toggle="modal" data-bs-target="#modal-local"><i
        class="far fa-hdd"></i> Local</button>
        <button name="type" type="button" value="google" class="btn btn-pill btn-outline-gray-500 me-2" data-bs-toggle="modal" data-bs-target="#modal-google"><i
                class="fab fa-google-drive"></i> Google Drive</button>
        <button name="type" type="button" value="local" class="btn btn-pill btn-outline-gray-500 me-2"><i
                class="fas fa-cloud"></i> S3</button>
    @endisset
</div>

<div class="modal fade" id="modal-local" tabindex="-1" role="dialog" aria-labelledby="modal-local" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="h6 modal-title">Penyimpanan Lokal</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Simpan berkas yang di upload ke penyimpanan lokal server</p>
                <div class="mb-4">
                    <label for="directory_name">Nama Direktori</label>
                    <input type="text" name="directory_name" required class="form-control" id="directory_name" aria-describedby="directory_name"
                        placeholder="Directory name..." value="{{ old('directory_name', @$cloudStorage->setting->directory_name) }}">

                </div>
            </div>
            <div class="modal-footer">
                <button name="type" type="submit" value="local" class="btn btn-secondary">Accept</button>
                <button type="button" class="btn btn-link text-gray ms-auto" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-google" tabindex="-1" role="dialog" aria-labelledby="modal-google" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="h6 modal-title">Penyimpanan Google Drive</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Simpan berkas yang di upload ke penyimpanan cloud dengan akun google drive</p>
                <p>Anda akan di minta login dan akses ke akun google drive, yang nantinya sistem akan membuat folder baru di google drive anda dengan nama <b>_manajemen_surat</b></p>
            </div>
            <div class="modal-footer">
                <button name="type" type="submit" value="google"  class="btn btn-secondary">Accept</button>
                <button type="button" class="btn btn-link text-gray ms-auto" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-google" tabindex="-1" role="dialog" aria-labelledby="modal-google" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="h6 modal-title">Penyimpanan S3</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Simple Storage Service (Amazon S3) adalah layanan penyimpanan objek yang menawarkan skalabilitas, ketersediaan data, keamanan, dan kinerja terdepan di industri</p>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary">Accept</button>
                <button type="button" class="btn btn-link text-gray ms-auto" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@push('scripts')
    <script>
        //listen change from input name
        document.getElementById('name').addEventListener('change', function() {
            //change value from input auth_name
            var name = this.value;
            //replace whitespace with underscore and remove symbol
            var directory_name = name.replace(/\s/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
            document.getElementById('directory_name').value = directory_name;
        });
    </script>
@endpush
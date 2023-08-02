<div class="mb-3">
    <label for="name">Nama Jabatan/Role <small class="text-danger">*</small></label>
    <input type="text" name="name" id="name" class="form-control" placeholder="Nama Jabatan/Role..." value="{{ old('name',@$role->name) }}">
    @error('name')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</div>
<div class="mb-3">
    <label for="description">Deskripsi</label>
    <input type="text" name="description" id="description" class="form-control" placeholder="Deskripsi Jabatan/Role..." value="{{ old('description',@$role->description) }}">
    @error('description')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</div>
@php
$permissions = (isset($role))?@$role->permissions->pluck('id')->toArray():[];
@endphp
{{-- <div class="mb-3">
    <div class="d-flex">
        <label for="">Hak Akses </label>
        <div class="form-check mx-1">
            <input class="form-check-input" value="true" type="checkbox" id="pilih_semua" > 
            <label class="form-check-label" for="pilih_semua">Pilih Semua</label>
        </div>
    </div>
    @foreach ($groups as $group)
        <div class="row">
            <div class="col-md-12">
                <p>{{ $group->name }}</p>
            </div>
        </div>
        <div class="row">
           
            @foreach ($group->permissions as $permision)
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permission_ids[]" @if(in_array($permision->id,$permissions)) checked="checked" @endif value="{{ $permision->id }}" id="permission-{{ $permision->id }}"> 
                        <label class="form-check-label" for="permission-{{ $permision->id }}" >{{ $permision->name }}</label>
                    </div>
                    
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-md-12">
                <hr>
            </div>
        </div>
    @endforeach
</div> --}}

<div class="mb-3">
    <div class="d-flex">
        <label for="">Hak Akses </label>
        <div class="form-check mx-1">
            <input class="form-check-input" value="true" type="checkbox" id="pilih_semua">
            <label class="form-check-label" for="pilih_semua">Pilih Semua</label>
        </div>
    </div>
    @error('permission_ids')
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">{{ $message }}</div>
            </div>
        </div>
    @enderror
    <div class="row">
        <div class="col-md-12">
            <p>Menentukan Hak Akses</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="1"  @if(in_array(1,$permissions)) checked="checked" @endif
                    id="permission-1">
                <label class="form-check-label" for="permission-1">Lihat Role</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="2" @if(in_array(2,$permissions)) checked="checked" @endif
                    id="permission-2">
                <label class="form-check-label" for="permission-2">Tambah Role</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="3" @if(in_array(3,$permissions)) checked="checked" @endif
                    id="permission-3">
                <label class="form-check-label" for="permission-3">Ubah Role</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="4" @if(in_array(4,$permissions)) checked="checked" @endif
                    id="permission-4">
                <label class="form-check-label" for="permission-4">Hapus Role</label>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p>Manage User</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="5" @if(in_array(5,$permissions)) checked="checked" @endif
                    id="permission-5">
                <label class="form-check-label" for="permission-5">Lihat User</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="6" @if(in_array(6,$permissions)) checked="checked" @endif
                    id="permission-6">
                <label class="form-check-label" for="permission-6">Tambah User</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="7" @if(in_array(7,$permissions)) checked="checked" @endif
                    id="permission-7">
                <label class="form-check-label" for="permission-7">Ubah User</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="8" @if(in_array(8,$permissions)) checked="checked" @endif
                    id="permission-8">
                <label class="form-check-label" for="permission-8">Hapus User</label>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p>Manage Surat</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="9" @if(in_array(9,$permissions)) checked="checked" @endif
                    id="permission-9">
                <label class="form-check-label" for="permission-9">Lihat Surat</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="10" @if(in_array(10,$permissions)) checked="checked" @endif
                    id="permission-10">
                <label class="form-check-label" for="permission-10">Lihat Semua Surat</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="11" @if(in_array(11,$permissions)) checked="checked" @endif
                    id="permission-11">
                <label class="form-check-label" for="permission-11">Tambah Surat</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="12" @if(in_array(12,$permissions)) checked="checked" @endif
                    id="permission-12">
                <label class="form-check-label" for="permission-12">Ubah Surat</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="13" @if(in_array(13,$permissions)) checked="checked" @endif
                    id="permission-13">
                <label class="form-check-label" for="permission-13">Hapus Surat</label>
            </div>

        </div>
        {{-- <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="14"
                    id="permission-14">
                <label class="form-check-label" for="permission-14">Setujui Surat</label>
            </div>

        </div> --}}
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="15" @if(in_array(15,$permissions)) checked="checked" @endif
                    id="permission-15">
                <label class="form-check-label" for="permission-15">Menerima Disposisi Surat</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="16" @if(in_array(16,$permissions)) checked="checked" @endif
                    id="permission-16">
                <label class="form-check-label" for="permission-16">Memeriksa Surat</label>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p>Manage Cloud Storage</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="17" @if(in_array(17,$permissions)) checked="checked" @endif
                    id="permission-17">
                <label class="form-check-label" for="permission-17">Lihat Cloud Storage</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="18" @if(in_array(18,$permissions)) checked="checked" @endif
                    id="permission-18">
                <label class="form-check-label" for="permission-18">Tambah Cloud Storage</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="19" @if(in_array(19,$permissions)) checked="checked" @endif
                    id="permission-19">
                <label class="form-check-label" for="permission-19">Ubah Cloud Storage</label>
            </div>

        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="20" @if(in_array(20,$permissions)) checked="checked" @endif
                    id="permission-20">
                <label class="form-check-label" for="permission-20">Hapus Cloud Storage</label>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <hr>
        </div>
    </div>
</div>

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@push('scripts')
    <script>
        const pilihSemua = document.getElementById('pilih_semua');
       
        //add event listener
        pilihSemua.addEventListener('change', function(e){
            console.log("test")
            const checkboxes = document.querySelectorAll('input[name="permission_ids[]"]');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = e.target.checked;
            });
        });
    </script>
@endpush

<div class="mb-3">
    <label for="name">Nama Jabatan/Role</label>
    <input type="text" name="name" id="name" class="form-control" placeholder="Nama Jabatan/Role..." value="{{ old('name',@$role->name) }}">
</div>
<div class="mb-3">
    <label for="description">Deskripsi</label>
    <input type="text" name="description" id="description" class="form-control" placeholder="Deskripsi Jabatan/Role..." value="{{ old('description',@$role->description) }}">
</div>
<div class="mb-3">
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
            @php
                $permissions = (isset($role))?@$role->permissions->pluck('id')->toArray():[];
            @endphp
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

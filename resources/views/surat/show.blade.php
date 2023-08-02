@extends('layouts.app')

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        table.lembar-disposisi,
        .lembar-disposisi td {
            border: 1px solid;
        }

        .lembar-disposisi td {
            padding: 5px;
        }
    </style>
@endpush

@push('scripts')
    @if ($surat->status == 'diperiksa' && $surat->pemeriksa_id == auth()->user()->id)
        <script>
            //scroll to #form-disposisi
            document.querySelector('#berikan_disposisi').scrollIntoView({
                behavior: 'smooth'
            });
            location.href = "#";
            location.href = "#berikan_disposisi";
        </script>
    @endif
    <script>
        function handler() {
            const user_ids = {!! json_encode(old('user_id', [])) !!};
            const role_ids = {!! json_encode(old('role_id', [])) !!};
            const keterangans = {!! json_encode(old('keterangan', [])) !!};
            const menunggu_persetujuan_ids = {!! json_encode(old('menunggu_persetujuan_id', [])) !!};

            function createOption(role_id, parent, _index) {

                if (parent == null || parent == undefined) {
                    return;
                }




                parent.querySelector('.pilih-role').value = role_id;
                const selectUser = parent.querySelector('.pilih-user');
                //const selectPersetujuan = parent.querySelector('.pilih-persetujuan');
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '/ajax/user-by-role/' + role_id);
                xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const data = JSON.parse(xhr.responseText);
                        if(data.length == 0){
                            alert("Tidak ada user yang bisa di disposisikan");
                        }
                        let options = '<option value="">Semua User Di Jabatan</option>';
                        // let options2 =
                        //     '<option value="">Langsung Dikirim</option><option value="0">Semua User Di Jabatan</option>';
                        data.forEach(function(user) {
                            options += '<option value="' + user.id + '" ' + ((user_ids.length > 0) ? ((user_ids[
                                _index] == user.id) ? 'selected' : '') : '') + '>' + user.nama + '</option>';
                            // options2 += '<option value="' + user.id + '" ' + ((menunggu_persetujuan_ids.length >
                            //         0) ? ((menunggu_persetujuan_ids[_index] == user.id) ? 'selected' : '') :
                            //     '') + '>' + user.nama + '</option>';
                        });
                        //set option to select
                        selectUser.innerHTML = options;
                        // selectPersetujuan.innerHTML = options2;
                    } else {
                        console.log('Request failed.  Returned status of ' + xhr.status);
                    }
                };
                xhr.onerror = function() {
                    console.log('Request failed.  Please try again later.');
                };

                xhr.send();
            }


            let _fields = [];
            for (let i = 0; i < user_ids.length; i++) {
                console.log(user_ids[i]);
                _fields.push({
                    user_id: user_ids[i] ?? null,
                    role_id: role_ids[i] ?? null,
                    keterangan: keterangans[i] ?? null,
                    menunggu_persetujuan_id: menunggu_persetujuan_ids[i] ?? null
                });

            }
            setTimeout(() => {
                
                for (let x = 0; x < _fields.length; x++) {
                    const f = _fields[x];
                    createOption(role_ids[x], document.querySelector('#list-' + x), x);

                }
            }, 1000);




            return {
                fields: _fields,
                addNewField(e) {
                    this.fields.push({
                        user_id: 0,
                        role_id: 0,
                        keterangan: ''
                    });
                },
                removeField(index) {
                    this.fields.splice(index, 1);
                },
                getUserByRole(e) {
                    const parent = e.target.parentElement.parentElement;
                    var listIndex = parent.getAttribute('id').split('-')[1];

                    const role_id = e.target.value;
                    this.fields[listIndex].role_id = role_id;
                    _fields[listIndex].role_id = role_id;
                    createOption(role_id, parent, listIndex);
                    //send POST request to route ajax.user-by-role


                }
            }
        }
    </script>
@endpush

@section('content')
    @foreach ($surat->berkas as $berkas)
        <div class="modal fade" id="modal-cloud-list-{{ $berkas->id }}" tabindex="-1" role="dialog"
            aria-labelledby="modal-cloud-list" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h6 modal-title">Cloud Storage Yang Tertaut Dengan Berkas Ini</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body table-responsive text-center">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        No
                                    </th>
                                    <th>
                                        Storage
                                    </th>
                                    <th>
                                        Type
                                    </th>
                                    <th>
                                        Link
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($berkas->berkas_storages as $v)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $v->storage->name }}
                                        </td>
                                        <td>
                                            {{ $v->storage->type }}
                                        </td>
                                        <td>
                                            <a target="_blank" href="{{ route('berkas-storage.view', $v->id) }}"
                                                class="btn btn-sm btn-info"><i class="fas fa-link"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-link text-gray ms-auto" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('surat.index') }}" class="btn btn-primary mx-2"><i class="fas fa-angle-left"></i>
                        Kembali</a>
                    @if (Auth::user()->id == $surat->user_id)
                        <a href="{{ route('surat.edit', $surat->id) }}" class="btn btn-warning mx-2"><i
                                class="fas fa-edit"></i>
                            Ubah</a>
                        <form action="{{ route('surat.destroy', $surat->id) }}" method="POST" class="d-inline mx-2">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger hapus-data"><i class="fas fa-trash"></i> Hapus</button>
                        </form>
                    @endif

                    <a href="{{ route('surat.download-pdf', $surat->id) }}" class="btn btn-success mx-2 text-white"
                        target="_blank"><i class="fas fa-download"></i> Download PDF</a>
                </div>
                <div class="card-body">
                    <table class="w-100 lembar-disposisi">
                        <tr>
                            <td colspan="3" class="text-center">
                                <h4>LEMBAR DISPOSISI</h4>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                Nomor Surat : {{ $surat->nomor_surat }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                Tanggal Surat : {{ $surat->tanggal_surat }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                Perihal : {{ $surat->perihal }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                Sifat Surat : {{ $surat->sifat }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-center">
                                <h4>
                                    DISPOSISI KEPADA
                                </h4>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3">
                                <ul>
                                    @foreach ($surat->disposisis as $item)
                                        <li>{{ $item->kepada }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <u>Isi Disposisi : </u><br>
                                {!! $surat->isi !!}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Lampiran Surat/Berkas <small><b>
                            @if ($stillUpload)
                                Prosess Upload Ke Cloud...
                            @endif
                        </b></small>
                </div>

                <div class="card-body table-responsive">


                    <form action="{{ route('surat.ubah-lampiran', $surat->id) }}" method="POST">
                        @csrf
                        <table class="table table-centered table-nowrap mb-0 rounded">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0 rounded-start">#</th>
                                    <th class="border-0">Name</th>
                                    <th class="border-0">Mime Type</th>
                                    <th class="border-0">Size</th>
                                    <th class="border-0">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $memeriksa = auth()
                                        ->user()
                                        ->can('Check Surat');
                                @endphp
                                @foreach ($surat->berkas as $item)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            @if ($memeriksa)
                                                <input type="text" class="form-control"
                                                    name="nama_berkas[{{ $item->id }}]"
                                                    value="{{ $item->nama_berkas }}" required>
                                            @else
                                                {{ $item->nama_berkas }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $item->mime_type }}
                                        </td>
                                        <td>
                                            {{ StorageHelper::formatBytes($item->size) }}
                                        </td>
                                        <td>
                                            <a href="{{ route('surat.view-berkas', [$surat->id, $item->id]) }}"
                                                target="_blank" class="btn btn-success text-white"><i
                                                    class="fas fa-file"></i></a>
                                            <button type="button" data-bs-toggle="modal"
                                                data-bs-target="#modal-cloud-list-{{ $item->id }}"
                                                class="btn btn-info text-white"><i class="fas fa-cloud"></i></button>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                            @if ($memeriksa)
                                <tfoot>
                                    <tr>
                                        <td colspan="5">
                                            <button class="btn btn-info btn-sm">Simpan Nama Lampiran</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if ($surat->status == 'diperiksa' && $surat->pemeriksa_id == auth()->user()->id)
        <form action="{{ route('surat.update', $surat->id) }}" method="POST" id="form-disposisi">
            @method('PUT')
            @csrf
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <p>Disposisi Surat</p>
                            @error('user_id')
                                <div class="alert alert-danger">
                                    Silahkan pilih user yang akan diberikan disposisi
                                </div>
                            @enderror
                        </div>
                        <div class="card-body table-responsive" x-data="handler()">
                            <table class="table table-centered table-nowrap mb-0 rounded">
                                <thead class="thead-light">
                                    <tr>
                                        <th colspan="2">
                                            Disposisi Ke (Jabatan/User)
                                        </th>
                                        <th>
                                            Keterangan
                                        </th>
                                        {{-- <th>
                                            Menunggu Di Setujui <i
                                                title="apakah pengiriman disposisi ini harus menunggu persetujuan user lainnya ?"
                                                class="fa-regular fa-circle-question " style="cursor: pointer;"></i>
                                        </th> --}}
                                        <th>
                                            #
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(field, index) in fields" :key="index">
                                        <tr x-bind:id="'list-' + index">
                                            <td>
                                                <select x-bind:name="'role_id[' + index + ']'"
                                                    class="form-control pilih-role" @change="getUserByRole" required>
                                                    <option value="">Pilih Jabatan/Role</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}"
                                                            x-bind:selected="field.user_id == {{ $role->id }}">
                                                            {{ $role->name }}</option>
                                                    @endforeach
                                                </select>

                                            </td>
                                            <td>
                                                <select x-bind:name="'user_id[' + index + ']'"
                                                    class="form-control pilih-user">
                                                    <option value="">Semua User Di Jabatan</option>
                                                </select>

                                            </td>
                                            <td>
                                                <input type="text" x-bind:name="'keterangan[' + index + ']'"
                                                    class="form-control" placeholder="Keterangan..."
                                                    x-bind:value="field.keterangan">
                                            </td>
                                            {{-- <td>
                                                <select x-bind:name="'menunggu_persetujuan_id[' + index + ']'"
                                                    class="form-control pilih-persetujuan">
                                                    <option value="">Langsung Dikirim</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}"
                                                            x-bind:selected="field.menunggu_persetujuan_id == {{ $role->id }}"
                                                            x-show="field.role_id != {{ $role->id }}">
                                                            {{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td> --}}
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    @click="removeField(index)"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>

                                    </template>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right text-end">
                                            <button type="button" class="btn btn-info" @click="addNewField">+ Tambah
                                                Disposisi</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-success text-white" name="berikan_disposisi"
                        value="berikan_disposisi" id="berikan_disposisi">Simpan</button>
                </div>
            </div>
        </form>
    @endif
    @if ($surat->status != 'diperiksa')
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center border">
                                <p><b>Dibuat</b></p>
                                <br>
                                <br>
                                <br>
                                <u><b>{{ $surat->user->nama }}</b></u>
                                <br>
                            </div>
                            @foreach ($surat->disposisis as $item)
                                <div class="col-md-3 text-center border">
                                    <p><b>Disposisi</b></p>
                                    <br>
                                    @if ($item->status == 'belum')
                                        <span class="badge bg-warning">Belum</span>
                                    @endif
                                    @if ($item->status == 'diterima')
                                        <span class="badge bg-success">Diterima & Diproses</span>
                                    @endif
                                    {{-- @if ($item->status == 'ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                        <p>{{ $item->keterangan }}</p>
                                    @endif --}}
                                    <br>
                                    <br>
                                    <u><b>{{ $item->kepada }}</b></u>
                                    <br>
                                    <i>Ket : {{ $item->keterangan }}</i>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if ($surat->user_id != Auth::user()->id && isset($cek))
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        @if (@$cek->status == 'belum')
                            <form action="{{ route('surat.disposisi', $surat->id) }}" method="POST">
                                @csrf
                                <textarea name="keterangan" id="keterangan" rows="5" class="form-control"
                                    placeholder="Keterangan tambahan...">{{ old('keterangan') }}</textarea>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-success text-white" name="status"
                                        value="diterima"><i class="fa-solid fa-check-double"></i> Terima & Proses
                                        {{-- @if (count($disposisi_berikutnya) > 0)
                                            & Disposisikan
                                        @endif --}}
                                    </button>
                                    {{-- <button type="submit" class="btn btn-danger text-white" name="status"
                                        value="ditolak"><i class="fa-solid fa-xmark"></i> Tolak & Kembalikan</button> --}}
                                </div>
                            </form>
                        @elseif (@$cek->status == 'ditolak')
                            <p>Riwayat</p>
                            <ul>
                                @foreach ($cek->riwayat_disposisis as $item)
                                    <li>
                                        {{ $item->created_at->format('Y-m-d') }} - <span
                                            class="badge @if ($item->status == 'ditolak') bg-danger @endif @if ($item->status == 'diterima') bg-success @endif">{{ $item->status }}</span>
                                        - {{ $item->keterangan }}
                                    </li>
                                @endforeach
                            </ul>
                            <hr>
                            {{-- <form action="{{ route('surat.disposisi',$surat->id) }}" method="POST">
                                @csrf
                                <textarea name="keterangan" id="keterangan" rows="5" class="form-control" placeholder="Keterangan tambahan..."></textarea>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-success text-white" name="status" value="diterima"><i class="fa-solid fa-check-double"></i> Terima @if (count($disposisi_berikutnya) > 0) & Disposisikan @endif</button>
                                    <button type="submit" class="btn btn-danger text-white" name="status" value="ditolak"><i class="fa-solid fa-xmark"></i> Tolak & Kembalikan</button>
                                </div>
                            </form> --}}
                        @else
                            <p>
                                Sudah di disposisikan ke :
                            </p>
                            <ul>
                                @foreach ($disposisi_berikutnya as $item)
                                    <li>{{ $item->kepada }}</li>
                                @endforeach
                            </ul>
                            <p>
                                Ket :
                            </p>
                            <p>
                                {{ $cek->keterangan }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

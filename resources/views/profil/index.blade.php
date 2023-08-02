@extends('layouts.app')

@section('content')
    <form action="{{ route('profil.tambah-cloud-storage') }}" method="POST">
        @csrf
        <div class="modal fade" id="modal-tambah-cloud" tabindex="-1" role="dialog" aria-labelledby="modal-tambah-cloud"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h6 modal-title">Penyimpanan Lokal</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label for="name">Storage Name</label>
                            <input type="text" name="name" required class="form-control" id="name"
                                aria-describedby="name" placeholder="Storage name..."
                                value="{{ old('name', @$cloudStorage->name) }}">

                        </div>
                        <button name="type" type="button" value="google" class="btn btn-pill btn-outline-gray-500 me-2"
                            data-bs-toggle="modal" data-bs-target="#modal-google"><i class="fab fa-google-drive"></i> Google
                            Drive</button>
                        {{-- <button name="type" type="button" value="local" class="btn btn-pill btn-outline-gray-500 me-2"
                            data-bs-toggle="modal" data-bs-target="#modal-s3"><i class="fas fa-cloud"></i> S3</button>
                        <button name="type" type="button" value="local" class="btn btn-pill btn-outline-gray-500 me-2"
                            data-bs-toggle="modal" data-bs-target="#modal-ftp"><i class="fa-solid fa-folder-tree"></i>
                            FTP</button> --}}

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link text-gray ms-auto" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal-google" tabindex="-1" role="dialog" aria-labelledby="modal-google"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h6 modal-title">Penyimpanan Google Drive</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Simpan berkas yang di upload ke penyimpanan cloud dengan akun google drive</p>
                        <p>Anda akan di minta login dan akses ke akun google drive, yang nantinya sistem akan membuat folder
                            baru di google drive anda dengan nama <b>_manajemen_surat</b></p>
                    </div>
                    <div class="modal-footer">
                        <button name="type" type="submit" value="google" class="btn btn-secondary">Accept</button>
                        <button type="button" class="btn btn-link text-gray ms-auto" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-s3" tabindex="-1" role="dialog" aria-labelledby="modal-google"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h6 modal-title">Penyimpanan S3</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Simple Storage Service (Amazon S3) adalah layanan penyimpanan objek yang menawarkan skalabilitas,
                            ketersediaan data, keamanan, dan kinerja terdepan di industri</p>
                        @include('cloud-storage.inputs.s3')
                    </div>
                    <div class="modal-footer">
                        <button name="type" type="submit" value="s3" class="btn btn-secondary">Accept</button>
                        <button type="button" class="btn btn-link text-gray ms-auto" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-ftp" tabindex="-1" role="dialog" aria-labelledby="modal-google"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h6 modal-title">Penyimpanan FTP</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>FTP</p>
                        @include('cloud-storage.inputs.ftp')
                    </div>
                    <div class="modal-footer">
                        <button name="type" type="submit" value="ftp" class="btn btn-secondary">Accept</button>
                        <button type="button" class="btn btn-link text-gray ms-auto"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form action="{{ route('profil.update') }}" class="form" method="POST" id="myForm">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Update Profil
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="nama">Nama</label>
                            <input type="text" name="nama" required class="form-control" id="nama"
                                aria-describedby="nama" placeholder="Nama..." maxlength="150" value="{{ old('nama', @$user->nama) }}">
                            @error('nama')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="username">Username</label>
                            <input type="text" name="username" required class="form-control" id="username"
                                aria-describedby="username" placeholder="Username..." minlength="5" maxlength="20"
                                value="{{ old('username', @$user->username) }}">
                            @error('username')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div x-data="{ ubahPassword: false }">
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" value="true" x-model="ubahPassword"
                                    id="ubah_password" name="ubah_password" checked="checked">
                                <label class="form-check-label" for="ubah_password">Ubah Password</label>
                            </div>
                            <div x-show="ubahPassword">
                                <div class="mb-4">
                                    <label for="new_password">Password Baru</label>
                                    <input type="password" name="new_password" x-bind:required="ubahPassword"
                                        class="form-control" id="new_password" aria-describedby="new_password"
                                        placeholder="Password..." value="" autocomplete="off" minlength="5" maxlength="50">
                                    @error('new_password')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <p>Kontak Untuk Menerima Notifikasi</p>
                    </div>
                    <div class="card-body" x-data="handler()">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        Kontak
                                    </th>
                                    <th>
                                        Jenis Kontak
                                    </th>
                                    <th>
                                        #
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(field, index) in fields" :key="index">
                                    <tr>
                                        <td>
                                            <input type="text" x-bind:name="'kontak[' + index + ']'"
                                                class="form-control" x-bind:id="'kontak-' + index"
                                                aria-describedby="kontak" placeholder="Kontak..."
                                                x-bind:value="field.kontak">
                                            @error('kontak.0')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <select x-bind:name="'type[' + index + ']'" x-bind:id="'type-' + index"
                                                class="form-control">
                                                <option value="email" x-bind:selected="field.type == 'email'">Email
                                                </option>
                                                <option value="wa" x-bind:selected="field.type == 'wa'">Whatsapp
                                                </option>
                                                {{-- <option value="telegram" x-bind:selected="field.type == 'telegram'">
                                                    Telegram
                                                </option> --}}
                                                {{-- <option value="push" x-bind:selected="field.type == 'push'">Browser Notification</option> --}}
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                @click="removeField(index)"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </template>

                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3">
                                        <button type="button" class="btn btn-sm btn-primary" @click="addNewField"><i
                                                class="fas fa-plus"></i></button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>



    </form>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <p>Cloud Storage Pribadi</p>
                        </div>
                        <div class="col-md-6" style="text-align: right;">
                            <button type="button" class="btn btn-primary float-right" data-bs-toggle="modal"
                                data-bs-target="#modal-tambah-cloud"><i class="fas fa-plus"></i> Tambah
                                Cloud Storage</button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-centered table-nowrap mb-0 rounded">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 rounded-start">#</th>
                                <th class="border-0">Name</th>
                                <th class="border-0">Auth</th>
                                <th class="border-0">Type</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 rounded-end">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user->my_cloud_storages as $item)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $item->name }}
                                    </td>
                                    <td>
                                        @if ($item->type == 'google' && $item->auth_name == '')
                                            <form action="{{ route('profil.update-cloud-storage', @$item->id) }}"
                                                method="POST" class="d-inline mx-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="name" value="{{ $item->name }}">
                                                <input type="hidden" name="status" value="{{ $item->status }}">
                                                <button name="type" type="submit" value="google"
                                                    class="btn btn-default btn-outline-gray-500 me-2"><i
                                                        class="fab fa-google-drive"></i> Login Google Drive</button>
                                            </form>
                                        @else
                                            {{ $item->auth_name }}
                                        @endif

                                    </td>
                                    <td>
                                        {{ $item->type }}
                                    </td>
                                    <td>
                                        {{ $item->status }}
                                    </td>
                                    <td>
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target="#modal-detail-{{ $item->id }}"
                                            class="btn btn-warning btn-sm"><i class="fas fa-eye"></i> Detail</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach ($user->my_cloud_storages as $cloudStorage)
        <div class="modal fade" id="modal-detail-{{ $cloudStorage->id }}" tabindex="-1" role="dialog"
            aria-labelledby="modal-detail-{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="h6 modal-title">Detail Cloud Storage</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul>
                            <li>
                                Nama : {{ $cloudStorage->name }}
                            </li>
                            <li>
                                Type : {{ $cloudStorage->type }}
                            </li>
                        </ul>
                        <table class="table table-centered table-nowrap mb-0 rounded">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0 rounded-start">#</th>
                                    <th class="border-0">Name</th>
                                    <th class="border-0">Mime Type</th>
                                    <th class="border-0">Size</th>
                                    <th class="border-0">Created Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $files = [];
                                    if ($cloudStorage->setting_json != null) {
                                        $files = $cloudStorage->listFiles;
                                    }
                                @endphp
                                @foreach ($files as $item)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $item->name }}
                                        </td>
                                        <td>
                                            {{ $item->mimeType }}
                                        </td>
                                        <td>
                                            {{ StorageHelper::formatBytes($item->size) }}
                                        </td>
                                        <td>
                                            {{ $item->createdTime }}
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">

                        <div>
                            <a href="#" class="btn btn-warning mx-2"><i
                                    class="fas fa-edit"></i> Edit</a>
                            <form action="{{ route('profil.hapus-cloud-storage', $cloudStorage->id) }}" method="POST"
                                class="d-inline mx-2">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                        </div>
                        <button type="button" class="btn btn-link text-gray ms-auto"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="row mt-4">
        <div class="col-md-12">
            <button type="submit" class="btn btn-success text-white"
                onclick="document.getElementById('myForm').submit()"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </div>
@endsection

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        function handler() {
            return {
                fields: {!! json_encode(isset($user->kontak_notifikasis) ? $user->kontak_notifikasis->toArray() : []) !!} || [],
                addNewField(e) {
                    this.fields.push({
                        kontak: '',
                        type: 'email',
                    });
                },
                removeField(index) {
                    this.fields.splice(index, 1);
                },

            }
        }
    </script>
@endpush

@push('scripts')
    <script>
        document.querySelectorAll('.modal .required').forEach(function(el) {
            el.setAttribute('disabled', true);
        });
        //listen change from input name
        document.getElementById('name').addEventListener('change', function() {
            //change value from input auth_name
            var name = this.value;
            //replace whitespace with underscore and remove symbol
            var directory_name = name.replace(/\s/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
            document.getElementById('directory_name').value = directory_name;
        });

        //add event to button with attribute data-bs-target
        document.querySelectorAll('button[data-bs-target]').forEach(function(el) {
            el.addEventListener('click', function() {
                var modalId = this.getAttribute('data-bs-target');
                document.querySelectorAll('.modal .required').forEach(function(el) {
                    el.setAttribute('disabled', true);
                });
                //disable input with .required class inside modalId
                document.querySelectorAll(modalId + ' .required').forEach(function(el) {
                    //remove disabled attribute
                    el.removeAttribute('disabled');
                });
            });
        });
    </script>
@endpush

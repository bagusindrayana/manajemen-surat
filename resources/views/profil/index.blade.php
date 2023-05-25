@extends('layouts.app')

@section('content')
    <form action="{{ route('profil.update') }}" class="form" method="POST">
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
                                aria-describedby="nama" placeholder="Nama..." value="{{ old('nama', @$user->nama) }}">
                            @error('nama')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="username">Username</label>
                            <input type="text" name="username" required class="form-control" id="username"
                                aria-describedby="username" placeholder="Username..."
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
                                        placeholder="Password..." value="" autocomplete="off" minlength="6">
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
                                            <input type="text" x-bind:name="'kontak[' + index + ']'" class="form-control"
                                                x-bind:id="'kontak-' + index" aria-describedby="kontak"
                                                placeholder="Kontak..." x-bind:value="field.kontak">
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
                                                <option value="telegram" x-bind:selected="field.type == 'telegram'">Telegram
                                                </option>
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

        <div class="row mt-4">
            <div class="col-md-12">
                <button type="submit" class="btn btn-success text-white"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </div>

    </form>
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

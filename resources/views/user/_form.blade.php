<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <a href="{{ route('user.index') }}" class="btn btn-primary"><i class="fas fa-angle-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <label for="nama">Nama</label>
                <input type="text" name="nama" required class="form-control" id="nama" aria-describedby="nama"
                    placeholder="Nama..." value="{{ old('nama', @$user->nama) }}">
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

            @if (!isset($user))
                <div class="mb-4">
                    <label for="password">Password</label>
                    <input type="password" name="password" required class="form-control" id="password"
                        aria-describedby="password" placeholder="Password..." value="" autocomplete="off">
                    @error('password')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <div class="mb-4">
                <label for="role_id">Role</label>
                <select class="form-select" id="role_id" aria-label="Status" name="role_id">
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}"
                            {{ old('role_id', in_array($role->id, isset($user) ? $user->roles->pluck('id')->toArray() : [])) == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- <div class="mb-4">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" id="email" aria-describedby="email"
                    placeholder="Email..." value="{{ old('email', @$user->email) }}">
                @error('email')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="no_telp">No Telp/HP</label>
                <input type="number" name="no_telp" class="form-control" id="no_telp" aria-describedby="no_telp"
                    placeholder="No Telp..." value="{{ old('no_telp', @$user->no_telp) }}">
                @error('no_telp')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div> --}}

            @isset($user)
                <div x-data="{ ubahPassword: false }">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" value="true" x-model="ubahPassword"
                            id="ubah_password" name="ubah_password" checked="checked">
                        <label class="form-check-label" for="ubah_password">Ubah Password</label>
                    </div>
                    <div x-show="ubahPassword">
                        <div class="mb-4">
                            <label for="password">Password</label>
                            <input type="password" name="password" x-bind:required="ubahPassword" class="form-control"
                                id="password" aria-describedby="password" placeholder="Password..." value=""
                                autocomplete="off">
                            @error('password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @endisset
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <p>Kontak User</p>
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
                                <input type="text" x-bind:name="'kontak[' + index + ']'" class="form-control" x-bind:id="'kontak-' + index"
                                    aria-describedby="kontak" placeholder="Kontak..." x-bind:value="field.kontak">
                                @error('kontak.0')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <select x-bind:name="'type[' + index + ']'" x-bind:id="'type-' + index" class="form-control">
                                    <option value="email" x-bind:selected="field.type == 'email'">Email</option>
                                    <option value="wa" x-bind:selected="field.type == 'wa'">Whatsapp</option>
                                    <option value="telegram" x-bind:selected="field.type == 'telegram'">Telegram</option>
                                    <option value="push" x-bind:selected="field.type == 'push'">Browser Notification</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger" @click="removeField(index)"><i
                                        class="fas fa-trash" ></i></button>
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




@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        function handler() {
            return {
                fields: {!! json_encode(@$user->kontak_notifikasis->toArray() ?? []) !!} ||[],
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

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
    <input type="text" name="username" required class="form-control" id="username" aria-describedby="username"
        placeholder="Username..." value="{{ old('username', @$user->username) }}">
    @error('username')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</div>

@if (!isset($user))
    <div class="mb-4">
        <label for="password">Password</label>
        <input type="password" name="password" required class="form-control" id="password" aria-describedby="password"
            placeholder="Password..." value="" autocomplete="off">
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

<div class="mb-4">
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
</div>

@isset($user)
    <div x-data="{ ubahPassword: false }">
        <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" value="true" x-model="ubahPassword" id="ubah_password"
                name="ubah_password" checked="checked">
            <label class="form-check-label" for="ubah_password">Ubah Password</label>
        </div>
        <div x-show="ubahPassword">
            <div class="mb-4">
                <label for="password">Password</label>
                <input type="password" name="password" x-bind:required="ubahPassword" class="form-control" id="password"
                    aria-describedby="password" placeholder="Password..." value="" autocomplete="off">
                @error('password')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
@endisset



@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

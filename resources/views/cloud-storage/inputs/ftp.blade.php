<div class="mb-3">
    <label for="host{{ @$ext }}">HOST <span class="text-danger">*</span></label>
    <input type="text" name="host" required class="form-control" id="host{{ @$ext }}"
        aria-describedby="host" placeholder="HOST..."
        value="{{ old('host', @$data->host) }}">
</div>

<div class="mb-3">
    <label for="username{{ @$ext }}">USERNAME <span class="text-danger">*</span></label>
    <input type="text" name="username" required class="form-control" id="username{{ @$ext }}"
        aria-describedby="username" placeholder="USERNAME..."
        value="{{ old('username', @$data->username) }}">
</div>

<div class="mb-3">
    <label for="password{{ @$ext }}">PASSWORD <span class="text-danger">*</span></label>
    <input type="password" name="password" required class="form-control" id="password{{ @$ext }}"
        aria-describedby="password" placeholder="PASSWORD..."
        value="{{ old('password', @$data->password) }}">
</div>

<div class="mb-3">
    <label for="port{{ @$ext }}">PORT <span class="text-danger">*</span></label>
    <input type="text" name="port" required class="form-control" id="port{{ @$ext }}"
        aria-describedby="port" placeholder="PORT..."
        value="{{ old('port', @$data->port) }}">
</div>

<div class="mb-3">
    <label for="root{{ @$ext }}">ROOT <span class="text-danger">*</span></label>
    <input type="text" name="root" required class="form-control" id="root{{ @$ext }}"
        aria-describedby="root" placeholder="ROOT..."
        value="{{ old('root', @$data->root) }}">
</div>
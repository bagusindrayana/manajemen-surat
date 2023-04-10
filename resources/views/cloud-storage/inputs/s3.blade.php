<div class="mb-3">
    <label for="access_key_id{{ @$ext }}">ACCESS KEY ID <span class="text-danger">*</span></label>
    <input type="password" name="access_key_id" required class="form-control" id="access_key_id{{ @$ext }}"
        aria-describedby="access_key_id" placeholder="ACCESS KEY ID..."
        value="{{ old('access_key_id', @$data->access_key_id) }}">

</div>
<div class="mb-3">
    <label for="secret_access_key{{ @$ext }}">SECRET ACCESS KEY <span class="text-danger">*</span></label>
    <input type="password" name="secret_access_key" required class="form-control" id="secret_access_key{{ @$ext }}"
        aria-describedby="secret_access_key" placeholder="SECRET ACCESS KEY..."
        value="{{ old('secret_access_key', @$data->secret_access_key) }}">

</div>
<div class="mb-3">
    <label for="bucke{{ @$ext }}t">BUCKET <span class="text-danger">*</span></label>
    <input type="text" name="bucket" required class="form-control" id="bucket{{ @$ext }}" aria-describedby="bucket"
        placeholder="BUCKET..." value="{{ old('bucket', @$data->bucket) }}">

</div>
<div class="mb-3">
    <label for="endpoint{{ @$ext }}">ENDPOINT <span class="text-danger">*</span></label>
    <input type="text" name="endpoint" required class="form-control" id="endpoint{{ @$ext }}" aria-describedby="endpoint"
        placeholder="ENDPOINT..." value="{{ old('endpoint', @$data->endpoint) }}">

</div>
<div class="mb-3">
    <label for="region{{ @$ext }}">REGION</label>
    <input type="text" name="region" class="form-control" id="region{{ @$ext }}" aria-describedby="region"
        placeholder="REGION..." value="{{ old('region', @$data->region) }}">

</div>

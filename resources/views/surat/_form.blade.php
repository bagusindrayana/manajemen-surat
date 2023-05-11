<div class="modal fade" id="modal-scan-dokumen" tabindex="-1" role="dialog" aria-labelledby="modal-scan-dokumen" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="h6 modal-title">Buka Kamera & Scan Dokumen Untuk Di Upload</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="row">
                    <div class="col-md-12">
                        <video id="video" height="300"></video> 
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="canvas" style="width: 100%;display: block;"></canvas>
                        <!-- original video -->
                    </div>
                    <div class="col-md-6">
                        
                        <canvas id="result"  style="width: 100%;display: block;"></canvas>
                        <!-- highlighted video -->
                    </div>
                </div>
                
                <div id="result-scan"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" id="save-scan">Scan Dokumen</button>
                <button type="button" class="btn btn-link text-gray ms-auto" data-bs-dismiss="modal" id="close-scan">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('surat.index') }}" class="btn btn-primary"><i class="fas fa-angle-left"></i> Kembali</a>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label for="nomor_surat">Nomor Surat</label>
                    <input type="text" name="nomor_surat" required class="form-control" id="nomor_surat"
                        aria-describedby="nomor_surat" placeholder="Nomor Surat..."
                        value="{{ old('nomor_surat', @$surat->nomor_surat) }}">
                    @error('nomor_surat')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="tanggal_surat">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" required class="form-control" id="tanggal_surat"
                        aria-describedby="tanggal_surat" placeholder="Tanggal Surat..."
                        value="{{ old('tanggal_surat', @$surat->tanggal_surat) }}">
                    @error('tanggal_surat')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="perihal">Perihal</label>
                    <input type="text" name="perihal" required class="form-control" id="perihal"
                        aria-describedby="perihal" placeholder="Perihal Surat..."
                        value="{{ old('perihal', @$surat->perihal) }}">
                    @error('perihal')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="sifat">Sifat</label>
                    <select class="form-select" id="sifat" aria-label="Status" name="sifat">
                        <option value="biasa" @if (old('sifat', @$surat->sifat) == 'biasa') selected @endif>Biasa</option>
                        <option value="rahasia" @if (old('sifat', @$surat->sifat) == 'rahasia') selected @endif>Rahasia</option>
                        <option value="penting" @if (old('sifat', @$surat->sifat) == 'penting') selected @endif>Penting</option>
                        <option value="sangat penting" @if (old('sifat', @$surat->sifat) == 'sangat penting') selected @endif>Sangat Penting
                        </option>
                    </select>
                    @error('perihal')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                Isi Disposisi
            </div>
            <div class="card-body">
                <div id="editor" style="height: 350px;">
                    {!! old('isi', @$surat->isi) !!}
                </div>
                <input type="hidden" id="isi" name="isi">
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-12 mb-4">
        <div class="card mb-4">
            <div class="card-header">
                <span>Berkas Surat/Lampiran </span> <button class="btn btn-info btn-sm" type="button" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#modal-scan-dokumen" id="open-modal-scan-dokumen"><i class="fas fa-camera"> Scan Dokumen</i></button>
            </div>
            <div class="card-body">
                <div id="my-awesome-dropzone" class="dropzone">

                </div>
            </div>
        </div>

    </div>
    <div class="col-lg-6 col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div x-data="{ allStorage: true }">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" value="true" x-model="allStorage"
                            id="all_storage" name="all_storage" checked="checked">
                        <label class="form-check-label" for="all_storage">Simpan Ke Semua Storage</label>
                    </div>
                    <div class="mt-3" x-show="!allStorage">
                        <div class="row">
                            @foreach ($storages as $storage)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" @if ($storage->status == 'active') checked @endif
                                            name="cloud_storage_id[]" type="checkbox" value="{{ $storage->id }}"
                                            id="{{ 'storage-' . $storage->id }}">
                                        <label class="form-check-label"
                                            for="{{ 'storage-' . $storage->id }}">{{ $storage->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <p>Disposisi Surat</p>
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
                            <th>
                                Menunggu Di Setujui <i
                                    title="apakah pengiriman disposisi ini harus menunggu persetujuan user lainnya ?"
                                    class="fa-regular fa-circle-question " style="cursor: pointer;"></i>
                            </th>
                            <th>
                                #
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(field, index) in fields" :key="index">
                            <tr x-bind:id="'list-' + index">
                                <td>
                                    <select x-bind:name="'role_id[' + index + ']'" class="form-control pilih-role"
                                        @change="getUserByRole" required>
                                        <option value="">Pilih Jabatan/Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                x-bind:selected="field.user_id == {{ $role->id }}">
                                                {{ $role->name }}</option>
                                        @endforeach
                                    </select>

                                </td>
                                <td>
                                    <select x-bind:name="'user_id[' + index + ']'" class="form-control pilih-user">
                                        <option value="0">Semua User Di Jabatan</option>
                                        {{-- @foreach ($users as $user)
                                            
                                            <option value="{{ $user->id }}">{{ $user->nama }}</option>
                                        @endforeach --}}
                                    </select>

                                </td>
                                <td>
                                    <input type="text" x-bind:name="'keterangan[' + index + ']'"
                                        class="form-control" placeholder="Keterangan..."
                                        x-bind:value="field.keterangan">
                                </td>
                                <td>
                                    <select x-bind:name="'menunggu_persetujuan_id[' + index + ']'"
                                        class="form-control pilih-persetujuan">
                                        <option value="">Langsung Dikirim</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                x-bind:selected="field.menunggu_persetujuan_id == {{ $role->id }}" x-show="field.role_id != {{ $role->id }}">
                                                {{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm"
                                        @click="removeField(index)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>

                        </template>

                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right text-end">
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



<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-success text-white">Simpan</button>
            </div>
        </div>
    </div>
</div>


@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .dropzone {
            background: white;
            border-radius: 5px;
            border: 2px dashed rgb(0, 135, 247);
            border-image: none;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'isi disposisi...'
        });
        const editor = document.querySelector('#editor');
        const isi = document.querySelector('#isi');
        const form = document.querySelector('#myForm');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const content = quill.root.innerHTML;
            isi.value = content;
            form.submit();
        });

        const token = document.head.querySelector('meta[name="csrf-token"]').content;
        const tmpFiles = {!! json_encode($tmpFiles) !!}
        // A quick way setup
        var myDropzone = new Dropzone("#my-awesome-dropzone", {
            dictDefaultMessage: "Klik bagian ini untuk memilih file atau Drag & Drop file disini",
            // Setup chunking
            url: "/berkas/upload",
            chunking: true,
            method: "POST",
            chunkSize: 2000000, //2 mb
            // If true, the individual chunks of a file are being uploaded simultaneously.
            //parallelChunkUploads: true
            addRemoveLinks: true,
            init: function() {
                this.on('maxfilesexceeded', function(file) {
                    this.removeAllFiles();
                    this.addFile(file);
                });


                for (var i = 0; i < tmpFiles.length; i++) {
                    var tmp = tmpFiles[i];
                    tmp.accepted = true;

                    this.files.push(tmp);
                    this.emit('addedfile', tmp);
                    this.createThumbnailFromUrl(tmp, tmp.url);
                    this.emit('complete', tmp);
                }
            },
            removedfile: function(file) {
                x = confirm('Do you want to delete?');
                if (!x) return false;
                var name = file.name;
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/berkas/upload/delete');
                xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        console.log(xhr.responseText);
                        //remove file from dropzone
                        file.previewElement.remove();
                    } else {
                        console.log('Request failed.  Returned status of ' + xhr.status);
                    }
                };
                xhr.onerror = function() {
                    console.log('Request failed.  Please try again later.');
                };
                var data = {
                    _token: token,
                    name: name
                };
                xhr.send(JSON.stringify(data));


            }
        });

        // Append token to the request - required for web routes
        myDropzone.on('sending', function(file, xhr, formData) {
            formData.append("_token", token);
        });

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
                        let options = '<option value="0">Semua User Di Jabatan</option>';
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

    <script src="https://docs.opencv.org/4.7.0/opencv.js" async></script>
    <!-- warning: loading OpenCV can take some time. Load asynchronously -->
    <script src="https://cdn.jsdelivr.net/gh/ColonelParrot/jscanify@master/src/jscanify.min.js"></script>

    <script>
        const scanner = new jscanify();
        const canvasCtx = canvas.getContext("2d");
        const resultCtx = result.getContext("2d");
        let pause = false;
        document.getElementById('open-modal-scan-dokumen').addEventListener('click', function() {
            pause = false;
            console.log('open modal scan dokumen');
            
            navigator.mediaDevices.getUserMedia({ video: true }).then((stream) => {
                video.srcObject = stream;
                video.onloadedmetadata = () => {
                    video.play();

                    setInterval(() => {
                        if(pause) return;
                        canvasCtx.drawImage(video, 0, 0,canvas.width, canvas.height);
                        const resultCanvas = scanner.highlightPaper(canvas);
                        resultCtx.drawImage(resultCanvas, 0, 0,result.width, result.height);
                    }, 10);
                };
            });
        });

        document.getElementById("save-scan").addEventListener('click', function() {
            pause = true;
            console.log('close modal scan dokumen');
  
            video.pause();
            video.srcObject = null;
            // const paperWidth = 300;
            // const paperHeight = 500;   
            canvasCtx.clearRect(0, 0, canvas.width, canvas.height);
            resultCtx.clearRect(0, 0, result.width, result.height);
            navigator.mediaDevices.getUserMedia({ video: false });
            const resultCanvas = scanner.extractPaper(canvas, canvas.width, canvas.height);
            document.getElementById("result-scan").appendChild(resultCanvas);
            
        });
    </script>
@endpush

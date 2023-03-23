@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('cloud-storage.index') }}" class="btn btn-primary mx-2"><i class="fas fa-angle-left"></i> Kembali</a>
                    <a href="{{ route('cloud-storage.edit',$cloudStorage->id) }}" class="btn btn-warning mx-2"><i class="fas fa-edit"></i> Edit</a>
                    <form action="{{ route('cloud-storage.destroy',$cloudStorage->id) }}" method="POST" class="d-inline mx-2">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                    @if ($cloudStorage->type == 'google' && $cloudStorage->auth_name == "")
                        <form action="{{ route('cloud-storage.update',@$cloudStorage->id) }}" method="POST" class="d-inline mx-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $cloudStorage->name }}">
                            <input type="hidden" name="status" value="{{ $cloudStorage->status }}">
                            <button name="type" type="submit" value="google" class="btn btn-default btn-outline-gray-500 me-2"><i class="fab fa-google-drive"></i> Login Google Drive</button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    @if ($cloudStorage->type == 'google' && $cloudStorage->auth_name == "")
                        <div class="alert alert-warning show" role="alert">
                            <strong>Warning!</strong> Storage ini belum di sinkronkan dengan akun google drive!
                            
                        </div>
                    @endif
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
            </div>
        </div>
    </div>
@endsection

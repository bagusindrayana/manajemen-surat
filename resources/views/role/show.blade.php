@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('role.index') }}" class="btn btn-primary mx-2"><i class="fas fa-angle-left"></i> Kembali</a>
                    <a href="{{ route('role.edit',@$role->id) }}" class="btn btn-warning mx-2"><i class="fas fa-edit"></i> Edit</a>
                    <form action="{{ route('role.destroy',$role->id) }}" method="POST" class="d-inline mx-2">
                        @method('DELETE')
                        <button class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                   
                </div>
                <div class="card-body">
                    <ul>
                        <li>
                            Nama : {{ $role->name }}
                        </li>
                        <li>
                            Deskripsi : {{ $role->description }}
                        </li>
                        
                    </ul>
                    <p>User</p>
                    <table class="table table-centered table-nowrap mb-0 rounded">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 rounded-start">#</th>
                                <th class="border-0">Nama</th>
                                <th class="border-0">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($role->users as $item)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $item->nama }}
                                    </td>
                                    <td>
                                        {{ $item->email }}
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

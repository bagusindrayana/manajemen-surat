@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('user.index') }}" class="btn btn-primary mx-2"><i class="fas fa-angle-left"></i> Kembali</a>
                    <a href="{{ route('user.edit',@$user->id) }}" class="btn btn-warning mx-2"><i class="fas fa-edit"></i> Edit</a>
                    <form action="{{ route('user.destroy',$user->id) }}" method="POST" class="d-inline mx-2">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                   
                </div>
                <div class="card-body">
                    <ul>
                        <li>
                            Nama : {{ $user->nama }}
                        </li>
                        <li>
                            Username : {{ $user->username }}
                        </li>
                        <li>
                            Role : {{ implode(",",$user->roles()->pluck('name')->toArray()) }}
                        </li>
                        <li>
                            Kontak : 
                            <ol>
                                @foreach ($user->kontak_notifikasis as $item)
                                    <li>{{$item->type}} : {{$item->kontak}}</li>
                                @endforeach
                            </ol>
                        </li>
                        {{-- <li>
                            Email : {{ $user->email }}
                        </li>
                        <li>
                            No Telp : {{ $user->no_telp }}
                        </li> --}}
                    </ul>
                    <p>User Log</p>
                    <table class="table table-centered table-nowrap mb-0 rounded">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 rounded-start">#</th>
                                <th class="border-0">Tanggal</th>
                                <th class="border-0">IP Address</th>
                                <th class="border-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user->user_logs()->orderBy('created_at','DESC')->get() as $item)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $item->created_at }}
                                    </td>
                                    <td>
                                        {{ $item->ip_address }}
                                    </td>
                                    
                                    <td>
                                        {{ $item->action }}
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

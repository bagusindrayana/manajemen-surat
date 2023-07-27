@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('user.index') }}" class="btn btn-primary mx-2"><i class="fas fa-angle-left"></i>
                        Kembali</a>
                    <a href="{{ route('user.edit', @$user->id) }}" class="btn btn-warning mx-2"><i class="fas fa-edit"></i>
                        Edit</a>
                    <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="d-inline mx-2">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger hapus-data"><i class="fas fa-trash"></i> Hapus</button>
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
                            Role : {{ implode(',',$user->roles()->pluck('name')->toArray()) }}
                        </li>
                        <li>
                            Kontak :
                            <ol>
                                @foreach ($user->kontak_notifikasis as $item)
                                    <li style="display:flex;"><span>{{ $item->type }} : {{ $item->kontak }} </span>
                                        <form action="{{ route('user.test-notifikasi',$item->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="kontak_id" value="{{ $item->id }}"><button
                                                name="test-notifikasi" value="test-notifikasi" style="padding: 1px;margin-left:5px; font-size:10px;">Test</button>
                                        </form>
                                    </li>
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
                            @php
                                $user_logs = $user->user_logs()->orderBy('created_at', 'DESC')->paginate(10)
                            @endphp
                            @foreach ($user_logs as $item)
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
                        <tfoot>
                            <tr>
                                <td colspan="4">
                                    {{ $user_logs->links() }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

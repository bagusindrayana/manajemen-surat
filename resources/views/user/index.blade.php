@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <form action="">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari {{ @$title }}..." value="{{ request()->search }}">
                                    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('user.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-centered table-nowrap mb-0 rounded">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 rounded-start">#</th>
                                <th class="border-0">Nama</th>
                                <th>Role/Jabatan</th>
                                <th>Kontak</th>
                                {{-- <th class="border-0">Email</th>
                                <th class="border-0">No HP</th> --}}
                                <th class="border-0 rounded-end">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $item)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $item->nama }}
                                    </td>
                                    <td>
                                        {{ implode(",",$item->roles->pluck('name')->toArray() ?? []) }}
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach ($item->kontak_notifikasis as $item)
                                                <li>{{$item->type}} : {{$item->kontak}}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    {{-- <td>
                                        {{ $item->email }}
                                    </td>
                                    <td>
                                        {{ $item->no_hp }}
                                    </td> --}}
                                  
                                    <td>
                                        <a href="{{ route('user.show',$item->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-eye"></i> Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6">
                                    {{ $users->links() }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

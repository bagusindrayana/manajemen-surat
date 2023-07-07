@extends('layouts.app')

@push('scripts')
@if (session('success'))
<script>
    //send fetch
    fetch("{{ url('cron-job/queue') }}", {
        method: "GET",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    });
</script>
@endif
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 py-2">
                            <form action="">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari {{ @$title }}..." value="{{ request()->search }}">
                                    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end py-2">
                            <a href="{{ route('surat.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</a>
                        </div>
                    </div>
                    
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-centered table-nowrap mb-0 rounded">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 rounded-start">#</th>
                                <th class="border-0">Nomor Surat</th>
                                <th class="border-0">Perihal</th>
                                <th class="border-0">Sifat</th>
                                <th class="border-0">Status</th>
                                <th class="border-0">Di Input Oleh</th>
                                <th class="border-0 rounded-end">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($surats as $item)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $item->nomor_surat }}
                                    </td>
                                    <td>
                                        {{ $item->perihal }}
                                    </td>
                                    <td>
                                        {{ $item->sifat }}
                                    </td>
                                    <td>
                                        @if ($item->status == "diperiksa")
                                            <span class="badge bg-info">{{ $item->status }}</span>
                                        @elseif($item->status == "pending")
                                            <span class="badge bg-warning">{{ $item->status }}</span>
                                        @elseif($item->status == "proses")
                                            <span class="badge bg-info">{{ $item->status }}</span>
                                        @elseif($item->status == "ditolak")
                                            <span class="badge bg-danger">{{ $item->status }}</span>
                                        @elseif($item->status == "selesai")
                                            <span class="badge bg-success">{{ $item->status }}</span>
                                        @else
                                            <span class="badge bg-dafult">{{ $item->status }}</span>
                                        @endif
                                        @if($item->status == "diperiksa")
                                            <br>
                                            <b><i>Diperiksa Oleh : {{ $item->pemeriksa->nama }}</i></b>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->user->nama }}
                                    </td>
                                    <td>
                                        <a href="{{ route('surat.show',$item->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-eye"></i> Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7">
                                    {{ $surats->links() }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

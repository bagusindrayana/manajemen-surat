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
                                <th class="border-0">Isi Surat</th>
                                <th class="border-0">Di Input Oleh</th>
                                <th class="border-0 rounded-end">Disposisi</th>
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
                                        {{ strip_tags($item->isi) }}
                                        
                                    </td>
                                    <td>
                                        {{ $item->user->nama }}
                                    </td>
                                    <td>
                                        <a href="{{ route('surat.show',$item->id) }}#form-disposisi"" class="btn btn-warning btn-sm"><i class="fas fa-eye"></i> Disposisi</a>
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

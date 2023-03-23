@extends('layouts.app')

@push('styles')
    <style>
        table.lembar-disposisi, .lembar-disposisi td {
            border: 1px solid;
        }

        .lembar-disposisi td {
            padding: 5px;
        }
    </style>
@endpush

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('surat.index') }}" class="btn btn-primary mx-2"><i class="fas fa-angle-left"></i> Kembali</a>
                    @if (Auth::user()->id == $surat->user_id)
                        <a href="{{ route('surat.edit',$surat->id) }}" class="btn btn-warning mx-2"><i class="fas fa-edit"></i> Edit</a>
                        <form action="{{ route('surat.destroy',$surat->id) }}" method="POST" class="d-inline mx-2">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                        </form>
                    @endif
                    
                    <a href="{{ route('surat.download-pdf',$surat->id) }}" class="btn btn-success mx-2 text-white" target="_blank"><i class="fas fa-download"></i> Download PDF</a>
                </div>
                <div class="card-body">
                    <table class="w-100 lembar-disposisi">
                        <tr>
                            <td colspan="3" class="text-center">
                                <h4>LEMBAR DISPOSISI</h4>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                Nomor Surat     :   {{ $surat->nomor_surat }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                Tanggal Surat   :   {{ $surat->tanggal_surat }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                Perihal         :   {{ $surat->perihal }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                Sifat Surat     :   {{ $surat->sifat }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-center">
                                <h4>
                                    DISPOSISI KEPADA
                                </h4>
                            </td>
                        </tr>
                        
                        <tr>
                            <td colspan="3">
                                <ul>
                                    @foreach ($surat->disposisis as $item)
                                        <li>{{ $item->kepada }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <u>Isi Disposisi : </u><br>
                                {!! $surat->isi !!}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Lampiran Surat/Berkas
                </div>
                
                <div class="card-body">
                   
                    
                    <table class="table table-centered table-nowrap mb-0 rounded">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 rounded-start">#</th>
                                <th class="border-0">Name</th>
                                <th class="border-0">Mime Type</th>
                                <th class="border-0">Size</th>
                                <th class="border-0">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($surat->berkas as $item)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $item->nama_berkas }}
                                    </td>
                                    <td>
                                        {{ $item->mime_type }}
                                    </td>
                                    <td>
                                        {{ StorageHelper::formatBytes($item->size) }}
                                    </td>
                                    <td>
                                        <a href="{{ route('surat.view-berkas',[$surat->id,$item->id]) }}" target="_blank" class="btn btn-success text-white"><i class="fas fa-file"></i></a>
                                    </td>
                                   
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center border">
                            <p><b>Dibuat</b></p>
                            <br>
                            <br>
                            <u><b>{{ $surat->user->nama }}</b></u>
                            <br>
                        </div>
                        @foreach ($surat->disposisis as $item)
                            <div class="col-md-3 text-center border">
                                <p><b>Disposisi</b></p>
                                <br>
                                <br>
                                <u><b>{{ $item->user->nama??$item->role->name }}</b></u>
                                <br>
                                @if ($item->status == "belum")
                                    <span class="badge bg-warning">Belum</span>
                                @endif
                                @if ($item->status == "diterima")
                                    <span class="badge bg-success">Diterima</span>
                                @endif
                                @if ($item->status == "ditolak")
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                                <br>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($surat->user_id != Auth::user()->id)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        @if (@$cek->status == "belum")
                            <form action="{{ route('surat.disposisi',$surat->id) }}" method="POST">
                                @csrf
                                <textarea name="keterangan" id="keterangan" rows="5" class="form-control" placeholder="Keterangan tambahan..."></textarea>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-success text-white" name="status" value="diterima"><i class="fa-solid fa-check-double"></i> Terima @if(count($disposisi_berikutnya) > 0) & Disposisikan @endif</button>
                                    <button type="submit" class="btn btn-danger text-white" name="status" value="ditolak"><i class="fa-solid fa-xmark"></i> Tolak & Kembalikan</button>
                                </div>
                            </form>
                        @elseif (@$cek->status == "ditolak")
                        <p>Riwayat</p>
                            <ul>
                                @foreach ($cek->riwayat_disposisis as $item)
                                    <li>
                                        {{ $item->created_at->format('Y-m-d') }} - <span class="badge @if($item->status == 'ditolak') bg-danger @endif @if($item->status == 'diterima') bg-success @endif">{{ $item->status }}</span> - {{ $item->keterangan }}
                                    </li>
                                @endforeach
                            </ul>
                            <hr>
                            {{-- <form action="{{ route('surat.disposisi',$surat->id) }}" method="POST">
                                @csrf
                                <textarea name="keterangan" id="keterangan" rows="5" class="form-control" placeholder="Keterangan tambahan..."></textarea>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-success text-white" name="status" value="diterima"><i class="fa-solid fa-check-double"></i> Terima @if(count($disposisi_berikutnya) > 0) & Disposisikan @endif</button>
                                    <button type="submit" class="btn btn-danger text-white" name="status" value="ditolak"><i class="fa-solid fa-xmark"></i> Tolak & Kembalikan</button>
                                </div>
                            </form> --}}
                        @else
                            <p>
                                Sudah di disposisikan ke :
                            </p>
                            <ul>
                                @foreach ($disposisi_berikutnya as $item)
                                    <li>{{ $item->user->nama??$item->role->name }}</li>
                                @endforeach
                            </ul>
                            <p>
                                Ket : 
                            </p>
                            <p>
                                {{ $cek->keterangan }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

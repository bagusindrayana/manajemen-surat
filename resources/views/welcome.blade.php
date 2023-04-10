@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                       
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-none d-sm-block">
                                <h2 class="h5">Surat Masuk</h2>
                                <h3 class="fw-extrabold mb-1">
                                    {{ $totalSuratMasuk }}
                                </h3>
                            </div>
                            <small class="d-flex align-items-center">Total Surat Masuk</small>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                       
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-none d-sm-block">
                                <h2 class="h5">Surat Masuk Selesai</h2>
                                <h3 class="fw-extrabold mb-1">
                                    {{ $totalSuratMasukSelesai }}
                                </h3>
                            </div>
                            <small class="d-flex align-items-center">Total Surat Masuk Yang Selesai Disposisi</small>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                       
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-none d-sm-block">
                                <h2 class="h5">Surat Masuk Ditolak</h2>
                                <h3 class="fw-extrabold mb-1">
                                    {{ $totalSuratMasukDitolak }}
                                </h3>
                            </div>
                            <small class="d-flex align-items-center">Total Surat Masuk Yang Ditolak</small>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

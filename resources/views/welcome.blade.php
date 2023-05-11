@extends('layouts.app')

@push('scripts')
    <script>
        var myModal = new bootstrap.Modal(document.getElementById('myModal'), {})
        //get first time from localstorage
        var firstTime = localStorage.getItem('firstTime');
        //check if first time is not set
        if(!firstTime){
            //show modal
            myModal.toggle();
            //set first time to true
            localStorage.setItem('firstTime', true);
        }
    </script>
@endpush

@section('content')
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="h6 modal-title">Selamat Datang!</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Selamat datang di aplikasi disposisi surat masuk Kelurahan Dadi Mulya</p>
                <img src="{{ url("img/vr_mr_kaigi_man.png") }}" alt="" width="200">
                <p>Sepertinya ini pertama kalinya anda membuka aplikasi ini</p>
                <p>Jika anda belum pernah mencoba aplikasi ini anda bisa mendownload buku manual-nya</p>
                <a href="{{ url(env("MANUAL_BOOK")) }}" class="btn btn-sm btn-info" target="_blank">Buku Manual</a>
            </div>
            <div class="modal-footer">
                
                <button type="button" class="btn btn-link text-gray ms-auto" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
    <div class="row">
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                       
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-sm-block">
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
                            <div class="d-sm-block">
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
                            <div class="d-sm-block">
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

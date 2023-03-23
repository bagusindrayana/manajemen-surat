@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('cloud-storage.index') }}" class="btn btn-primary"><i class="fas fa-angle-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('cloud-storage.store') }}" class="form" method="POST">
                        @csrf
                        @include('cloud-storage._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

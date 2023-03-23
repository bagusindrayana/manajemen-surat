@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('user.index') }}" class="btn btn-primary"><i class="fas fa-angle-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.store') }}" class="form" method="POST">
                        @csrf
                        @include('user._form')
                        <button type="submit" class="btn btn-success text-white"><i class="fas fa-save"></i> Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

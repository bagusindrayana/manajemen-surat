@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('role.index') }}" class="btn btn-primary"><i class="fas fa-angle-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('role.store') }}" class="form" method="POST" id="myForm">
                        @csrf
                        @include('role._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

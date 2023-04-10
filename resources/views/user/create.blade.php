@extends('layouts.app')

@section('content')
    <form action="{{ route('user.store') }}" class="form" method="POST">
        @csrf
        <div class="row">
            @include('user._form')
            
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <button type="submit" class="btn btn-success text-white"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </div>
        
    </form>
    
@endsection

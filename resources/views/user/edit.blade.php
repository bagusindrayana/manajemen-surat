@extends('layouts.app')

@section('content')
    <form action="{{ route('user.update',$user->id) }}" class="form" method="POST">
        @csrf
        @method('PUT')
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

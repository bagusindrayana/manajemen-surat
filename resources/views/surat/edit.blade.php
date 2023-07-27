@extends('layouts.app')

@section('content')
    <form action="{{ route('surat.update',$surat->id) }}" class="form" method="POST" id="myForm">
        @csrf
        @method('PUT')
        @include('surat._form')
    </form>
    
@endsection

@extends('layouts.app')

@section('content')
    <form action="{{ route('surat.store') }}" class="form" method="POST" id="myForm">
        @csrf
        @include('surat._form')
    </form>
    
@endsection

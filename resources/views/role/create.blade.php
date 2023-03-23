@extends('layouts.app')

@section('content')
    <form action="{{ route('role.store') }}" class="form" method="POST" id="myForm">
        @csrf
        @include('role._form')
    </form>
    
@endsection

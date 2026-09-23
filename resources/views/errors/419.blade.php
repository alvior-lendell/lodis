@extends('errors.layout')

@section('title', 'Page Expired')

@section('code_badge')
    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
    HTTP 419 &bull; Session Expired
@endsection

@section('message')
    Your session has expired due to inactivity or an invalid CSRF security token. Please refresh the page and try submitting the form again.
@endsection
@extends('errors.layout')

@section('title', 'Authentication Required')

@section('code_badge')
    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
    HTTP 401 &bull; Unauthorized
@endsection

@section('message')
    You must be signed in with a valid user session to access this module or resource within LODISv2.
@endsection
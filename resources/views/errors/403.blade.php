@extends('errors.layout')

@section('title', 'Access Restricted')

@section('code_badge')
    <span class="h-2 w-2 rounded-full bg-rose-600"></span>
    HTTP 403 &bull; Unauthorized
@endsection

@section('message')
    Your current employee role or session privileges do not permit access to this module or resource within LODISv2.
@endsection
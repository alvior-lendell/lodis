@extends('errors.layout')

@section('title', 'Service Unavailable')

@section('code_badge')
    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
    HTTP 503 &bull; Maintenance Mode
@endsection

@section('message')
    LODISv2 is currently undergoing scheduled system maintenance, database optimization, or server updates. Please check back shortly.
@endsection
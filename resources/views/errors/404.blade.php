@extends('errors.layout')

@section('title', 'Page Not Found')

@section('code_badge')
    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
    HTTP 404 &bull; Resource Missing
@endsection

@section('message')
    The page or workstation access module you are looking for does not exist, has been removed, or moved to a different workspace route.
@endsection
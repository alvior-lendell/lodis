@extends('errors.layout')

@section('title', 'Payment Required')

@section('code_badge')
    <span class="h-2 w-2 rounded-full bg-purple-600 animate-pulse"></span>
    HTTP 402 &bull; Payment Required
@endsection

@section('message')
    Access to this module or enterprise workspace feature requires an active subscription or billing clearance. Please contact your system administrator or billing department.
@endsection
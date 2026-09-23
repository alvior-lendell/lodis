@extends('errors.layout')

@section('title', 'Internal Server Error')

@section('code_badge')
    <span class="h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span>
    HTTP 500 &bull; System Exception
@endsection

@section('message')
    An unexpected condition was encountered on our servers. The system administrators have been notified. Please try again shortly.
@endsection
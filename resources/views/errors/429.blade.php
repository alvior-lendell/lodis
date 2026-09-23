@extends('errors.layout')

@section('title', 'Too Many Requests')

@section('code_badge')
    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
    HTTP 429 &bull; Rate Limit Exceeded
@endsection

@section('message')
    You have made too many requests in a short period. Please wait a few seconds before trying again to protect system stability.
@endsection
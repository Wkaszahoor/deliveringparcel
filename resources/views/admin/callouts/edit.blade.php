@extends('admin.layouts.app')

@section('title', 'Edit Order Callout')
@section('page_title', 'Order Callouts')
@section('page_subtitle', 'Edit callout #' . $callout->id . ' (' . $callout->status_key . ')')

@section('content')
@include('admin.callouts.form', ['callout' => $callout])
@endsection

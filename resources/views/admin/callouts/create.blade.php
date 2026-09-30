@extends('admin.layouts.app')

@section('title', 'New Order Callout')
@section('page_title', 'Order Callouts')
@section('page_subtitle', 'Create a new callout card for an order status / stage.')

@section('content')
@include('admin.callouts.form', ['callout' => $callout])
@endsection

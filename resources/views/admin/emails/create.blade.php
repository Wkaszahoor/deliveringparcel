@extends('admin.layouts.app')

@section('title', 'New Email Template')
@section('page_title', 'Email Templates')
@section('page_subtitle', 'Create a new templated email.')

@section('content')
@include('admin.emails.form', ['template' => $template])
@endsection

@extends('admin.layouts.app')

@section('title', 'Edit Email Template')
@section('page_title', 'Email Templates')
@section('page_subtitle', 'Edit template ' . $template->key)

@section('content')
@include('admin.emails.form', ['template' => $template])
@endsection

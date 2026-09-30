@extends('layouts.tailwind.app')

@section('title', 'Add New Post')
@section('page_title', 'Add New Post')
@section('page_subtitle', 'Create a new ' . ($postTypes[$postType] ?? 'post') . '.')

@section('content')
@include('admin.cms.posts._form')
@endsection

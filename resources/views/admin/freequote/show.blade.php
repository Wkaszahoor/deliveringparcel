<!-- Main layout for Html Start from here -->
@extends('layouts.admin_dashbord_master')
<!-- main layout ends here -->
<!-- title for header start from here -->
@section('head')
<title>Orders | Deliveringparcel</title>
@endsection
<!-- title end here -->
<!-- Min content start from here -->
@section('content')
<!-- Main content -->
<section class="content order">
    <div class="container-fluid">
        <div class="row">
            <!-- ./col -->
            @foreach($freequote as $qoute)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-text-width"></i>
                            Free Quote Requested by {{$qoute->name}}
                        </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8">{{$qoute->name}}</dd>
                            <dt class="col-sm-4">Number</dt>
                            <dd class="col-sm-8">{{$qoute->number}}</dd>
                            <dt class="col-sm-4">Email address</dt>
                            <dd class="col-sm-8">{{$qoute->email}}.</dd>
                            <dt class="col-sm-4">Cargo type</dt>
                            <dd class="col-sm-8">{{$qoute->cargotype}}</dd>
                            <dt class="col-sm-4">Country</dt>
                            <dd class="col-sm-8">{{$qoute->country}}.</dd>
                            <dt class="col-sm-4">Destination</dt>
                            <dd class="col-sm-8">{{$qoute->destination}}</dd>
                            <dt class="col-sm-4">Weight</dt>
                            <dd class="col-sm-8">{{$qoute->weight}}</dd>
                            <dt class="col-sm-4">Width</dt>
                            <dd class="col-sm-8">{{$qoute->width}}.</dd>
                            <dt class="col-sm-4">Height</dt>
                            <dd class="col-sm-8">{{$qoute->height}}</dd>
                            <dt class="col-sm-4">Detail Description</dt>
                            <dd class="col-sm-8">{{$qoute->detail}}.</dd>
                            <dt class="col-sm-4">Created at</dt>
                            <dd class="col-sm-8">{{$qoute->created_at}}.</dd>
                        </dl>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            @endforeach
            <!-- ./col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->

@endsection
<!-- main content ends here -->
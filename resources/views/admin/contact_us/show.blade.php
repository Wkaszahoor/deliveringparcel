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
            @foreach($Contactus as $cont)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-text-width"></i>
                            {{$cont->name}} Contacted us
                        </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8">{{$cont->name}}</dd>
                            <dt class="col-sm-4">Number</dt>
                            <dd class="col-sm-8">{{$cont->number}}</dd>
                            <dt class="col-sm-4">Email address</dt>
                            <dd class="col-sm-8">{{$cont->email}}.</dd>
                            <dt class="col-sm-4">Detail Description</dt>
                            <dd class="col-sm-8">{{$cont->detail}}.</dd>
                            <dt class="col-sm-4">Created at</dt>
                            <dd class="col-sm-8">{{$cont->created_at}}.</dd>
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
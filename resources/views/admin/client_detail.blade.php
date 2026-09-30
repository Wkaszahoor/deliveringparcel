<!-- main layout star from here -->
@extends('layouts.admin_dashbord_master')
<!-- main layout ends from here -->
<!-- title for header start from here -->
@section('head')
<title>Client Detail | Deliveringparcel</title>
@endsection
<!-- title for header ends here -->
<!-- main content start from here -->
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Main content -->
                <div class="invoice p-3 mb-3">
                    <!-- title row -->
                    <div class="row">
                        <div class="col-12">
                            <h4>
                                <i class="fas fa-globe"></i> {{$user->name}}
                                <small class="float-right">{{$user->created_at}}</small>
                            </h4>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- info row -->
                    <div class="row invoice-info">
                        <div class="col-sm-4 invoice-col">
                            <address>

                                Phone: {{$user->number}}<br>
                                Email: {{$user->email}}
                            </address>
                        </div>
                    </div>
                    <!-- /.row -->

                    <!-- Table row -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Ship From</th>
                                        <th>Ship To</th>
                                        <th>Net Total</th>
                                        <th>Created at</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td><a href="{{route('order',$order->id)}}">{{$order->order_id}}</a></td>
                                        <td>{{$order->shipfrom}}</td>
                                        <td>{{$order->shipto}}</td>
                                        <td>{{$order->total}}</td>
                                        <td>{{$order->created_at}}</td>
                                        <!-- <td>$add->created_at->format('d-m-Y')</td> -->

                                        @if($order->active_tab == 3)
                                        <td><span class="badge badge-warning">{{$order->order_status}}</span></td>
                                        @elseif($order->active_tab == 4)
                                        <td><span class="badge badge-success">{{$order->order_status}}</span></td>
                                        @else
                                        @if($order->order_status != '')
                                        <td><span class="badge badge-danger">{{$order->order_status}}</span></td>
                                        @else
                                        <td><span class="badge badge-danger">In Process</span></td>
                                        @endif
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.invoice -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection
<!-- main content ends here -->
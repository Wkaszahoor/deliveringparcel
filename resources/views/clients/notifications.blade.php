<!-- Main layout for Html -->
@extends('layouts.client_dashbord_master')
<!-- Main layout ends here -->
<!-- Header title -->
@section('head')
<title>Orders | Deliveringparcel</title>
@endsection
<!-- Header title ends here -->
<!-- main content start from here -->
@section('content')
<!-- Main content -->
<section class="content order">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Orders Notifications</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Order ID</th>
                                    <th>Notification</th>
                                    <th>Created at</th>
                                    <th>Read at</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(auth()->user()->Notifications->where('type', 'App\Notifications\TaskNotification') as $notification)
                                <tr>
                                    <td></td>
                                    <td><a href="{{ isset($notification->data['order_id']) ? route('orders.show',$notification->data['order_id']) : route('notifications') }}">{{$notification->data['order_number'] ?? 'N/A'}}</a></td>
                                    <td><a href="{{ isset($notification->data['order_id']) ? route('orders.show',$notification->data['order_id']) : route('notifications') }}">{{$notification->data['greeting'] ?? ''}}</a></td>
                                    <td>{{$notification->created_at}}</td>
                                    <td>{{$notification->read_at}}</td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->

<script>
    $(function() {
        $("#example1").DataTable({
            "responsive": true,
            "autoWidth": false,
            "info": true,
            "ordering": true,
        });
    });
</script>
<script>
    // script for table rows numbering 
    var table = document.getElementsByTagName('tbody')[0],
        rows = table.getElementsByTagName('tr'),
        text = 'textContent' in document ? 'textContent' : 'innerText';
    for (var i = 0, len = rows.length; i < len; i++) {
        rows[i].children[0][text] = i + '' + rows[i].children[0][text];
    }
</script>
@endsection
<!-- Min content end here -->
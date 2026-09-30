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
      <div class="col-12">
        @if($order->count()>0)
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Clients Orders</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <table id="example1" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Ship From</th>
                  <th>Ship To</th>
                  <th>Net Total</th>
                  <th>Created at</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($order as $add)
                <tr>
                  <td><a href="{{route('order',$add->id)}}">{{$add->order_id}}</a></td>
                  <td>{{$add->name}}</td>
                  <td>{{$add->email}}</td>
                  <td>{{$add->shipfrom}}</td>
                  <td>{{$add->shipto}}</td>
                  <td>${{$add->total}}</td>
                  <td>{{$add->created_at}}</td>
                  <!-- <td>$add->created_at->format('d-m-Y')</td> -->
                  @if($add->active_tab == 3)
                  <td><span class="badge badge-warning p-1">{{$add->order_status}}</span></td>
                  @elseif($add->active_tab == 4)
                  <td><span class="badge badge-success p-1">{{$add->order_status}}</span></td>
                  @else
                  @if($add->order_status != '')
                    @if($add->order_status == 'Offer Accepted')
                        <td><span class="badge badge-primary p-1">{{$add->order_status}}</span></td>
                      @else
                      @if($add->order_status == 'Offer Placed')
                        <td><span class="badge badge-primary p-1">{{$add->order_status}}</span></td>
                        @else
                        @if($add->order_status == 'Order placed')
                        <td><span class="badge badge-dark p-1">{{$add->order_status}}</span></td>
                        @else
                        <td><span class="badge badge-danger p-1">{{$add->order_status}}</span></td>
                        @endif
                        
                        @endif
                    @endif
                  @else
                  <td><span class="badge badge-danger p-1">Request Placed</span></td>
                  @endif
                  @endif
                  <td><a href="{{route('order',$add->id)}}"><i class="fas fa-history"></i></a></td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
        @else
        <p class="text-center text-lg text-muted">No Record Found</p>
        @endif
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
       'order': [6,'dec']
    });
  });
</script>
<!--<script>-->
<!--   script for table rows numbering -->
<!--  var table = document.getElementsByTagName('tbody')[0],-->
<!--    rows = table.getElementsByTagName('tr'),-->
<!--    text = 'textContent' in document ? 'textContent' : 'innerText';-->
<!--  for (var i = 0, len = rows.length; i < len; i++) {-->
<!--    rows[i].children[0][text] = i + '' + rows[i].children[0][text];-->
<!--  }-->
<!--</script>-->
@endsection
<!-- main content ends here -->
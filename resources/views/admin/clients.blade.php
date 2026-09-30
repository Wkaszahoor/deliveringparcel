<!-- Main layout for html satr from here -->
@extends('layouts.admin_dashbord_master')
<!-- Main layout ends here -->
<!-- Title for header -->
@section('head')
<title>Clients | Deliveringparcel</title>
@endsection
<!-- Title end here -->
<!-- Mian content start from here -->
@section('content')
<!-- Main content -->
<section class="content order">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Clients</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <form method="GET" action="{{ route('clients') }}" class="form-row align-items-end mb-3">
              <div class="form-group col-md-2 mb-2">
                <label for="f-userid">Client ID</label>
                <input id="f-userid" type="number" name="user_id" min="1" class="form-control form-control-sm"
                       value="{{ request('user_id') }}" placeholder="e.g. 101">
              </div>
              <div class="form-group col-md-3 mb-2">
                <label for="f-name">Name</label>
                <input id="f-name" type="text" name="name" class="form-control form-control-sm"
                       value="{{ request('name') }}" placeholder="Name contains…">
              </div>
              <div class="form-group col-md-3 mb-2">
                <label for="f-email">Email</label>
                <input id="f-email" type="text" name="email" class="form-control form-control-sm"
                       value="{{ request('email') }}" placeholder="user@example.com">
              </div>
              <div class="form-group col-md-4 mb-2 text-right">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="{{ route('clients') }}" class="btn btn-sm btn-default">Reset</a>
              </div>
            </form>

            @if($orders->count() > 0)
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr style="background-color: #d0e7ff; color: #000;">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Number</th>
                    <th>Orders</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($orders as $user)
                  <tr>
                    <td><a href="{{ route('show_details', $user->id) }}">#{{ $user->id }}</a></td>
                    <td class="text-capitalize">{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{$user->number}}</td>
                    <td>{{$user->orders_items}}</td>
                    <td><a href="{{route('show_details',$user->id)}}"><i class="fas fa-history"></i></a></td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
              {{ $orders->links('pagination::bootstrap-4') }}
            </div>
            @else
            <p class="text-center text-lg text-muted">No Record Found</p>
            @endif
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
@endsection
<!-- Main content ends here -->

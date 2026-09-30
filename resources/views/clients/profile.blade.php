@extends('layouts.client_dashbord_master')
@section('head')
<title>Profile | Delivering Parcel</title>
@endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      {{ session('error') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    @if ($message = Session::get('success'))

    <div class="text-white text-center px-6 py-2  rounded relative mb-4  bg-success border border-blue-400 ">
      <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
      </span>
      <span class="inline-block align-middle mr-8">
        <b class="capitalize">{{ $message }}</b>
      </span>

    </div>

    @elseif ($message = Session::get('error'))
    <div class="text-white text-center px-6 py-2 border-0 rounded relative mb-4 bg-danger">
      <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
      </span>
      <span class="inline-block align-middle mr-8">
        <b class="capitalize">{{ $message }}</b>
      </span>

    </div>
    @endif
    <div class="row">
      <div class="col-md-3">

        <!-- Profile Image -->
        <div class="card card-primary card-outline">
          <div class="card-body box-profile">
            <div class="text-center">
              @if (Auth::user()->avatar!="")
              @php
              $image=Auth::user()->avatar;
              @endphp
              <img src="{{url('uploads/profile/'.$image)}}" alt="user" class="profile-user-img img-fluid img-circle" />
              @else
              <img src="{{url('uploads/profile/user.png')}}" alt="user" class="profile-user-img img-fluid img-circle" />
              @endif
            </div>

            <h3 class="profile-username text-center">{{$user->name}}</h3>

            <p class="text-muted text-center">Admin</p>

            <ul class="list-group list-group-unbordered mb-3">
              <li class="list-group-item">
                <b>{{$user->name}}</b>
              </li>
              <li class="list-group-item">
                <b>{{$user->email}}</b>
              </li>
              <li class="list-group-item">
                <b>{{$user->number}}</b>
              </li>
            </ul>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
      <div class="col-md-9 ">
        <div class="card card-primary card-outline">
          <div class="card-header p-2">
            <ul class="nav nav-pills">
              <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Profile </a></li>
              <li class="nav-item"><a class="nav-link" href="#password" data-toggle="tab">Password </a></li>
            </ul>
          </div><!-- /.card-header -->
          <div class="card-body">
            <div class="tab-content">
              <div class="active tab-pane" id="activity">
                <form action="{{url('client_avatar')}}" method="POST" class="py-5" enctype='multipart/form-data'>
                  @csrf
                  <div class="grid grid-cols-12 gap-4 row-gap-5">
                    <div class="form-group row">
                      <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                      <div class="col-sm-10">
                        <input type="text" name="name" value="{{$user->name}}" class="form-control" id="inputName">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                      <div class="col-sm-10">
                        <input type="text" name="email" value="{{$user->email}}" class="form-control" id="inputEmail">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputNumber" class="col-sm-2 col-form-label">Number</label>
                      <div class="col-sm-10">
                        <input type="text" name="number" value="{{$user->number}}" class="form-control" id="inputNumber">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputAvatar" class="col-sm-2 col-form-label">Profile Image</label>
                      <div class="col-sm-10">
                        <input type="file" name="avatar" value="" class="form-control" id="inputAvatar">
                      </div>
                    </div>
                    <div class="form-group row">
                      <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-danger">Update</button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane " id="password">
                <h4 class="login-box-msg">You are only one step away from your new password.</h4>
                <form action="{{route('password')}}" method="POST" class="py-5" enctype='multipart/form-data'>
                  @csrf
                  @method('PUT')
                  <div class="grid grid-cols-12 gap-4 row-gap-5">
                    <div class="form-group row">
                      <label for="inputName" class="col-sm-2 col-form-label">Old Password</label>
                      <div class="input-group  col-sm-8">
                        <input type="password" name="old_password" id="current" value="" autocomplete="current-password" class="form-control" required />
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-lock" onclick="password_show()"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail" class="col-sm-2 col-form-label">New Password</label>
                      <div class="input-group col-sm-8">
                        <input type="password" name="new_password" id="new" value="" autocomplete="new-password" class="form-control">
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-lock" onclick="password_new()"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputNumber" class="col-sm-2 col-form-label">Confirm Password</label>
                      <div class="input-group col-sm-8">
                        <input type="password" name="confirm_password" id="confirm" value="" autocomplete="new-password" class="form-control">
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-lock" onclick="password_confirm()"></span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="form-group row">
                      <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-danger">Update</button>
                      </div>
                    </div>
                  </div>
                </form>

              </div>
              <!-- /.tab-pane -->
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div><!-- /.card-body -->
        </div>
        <!-- /.nav-tabs-custom -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
<script>
  function password_show() {
    var x = document.getElementById("current");
    if (x.type === "password") {
      x.type = "text";
    } else {
      x.type = "password";
    }
  }

  function password_new() {
    var x = document.getElementById("new");
    if (x.type === "password") {
      x.type = "text";
    } else {
      x.type = "password";
    }
  }

  function password_confirm() {
    var x = document.getElementById("confirm");
    if (x.type === "password") {
      x.type = "text";
    } else {
      x.type = "password";
    }
  }
</script>
@endsection
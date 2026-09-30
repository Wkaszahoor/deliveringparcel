<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!--@yield('head')-->
  
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
 <link rel="icon" type="image/png" href="{{url('images/dp.svg')}}" alt="deliveringParcellogo">
 <title> @yield('title')</title>
     <meta name="description" content="@yield('meta_description')" >
     <meta name="author" content="deliveringparcel">

 <link rel="canonical" href="https://deliveringparcel.com" />

  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{url('dashbord/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="{{url('assets/ionicons/2.0.1/css/ionicons.min.css')}}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{url('dashbord/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{url('dashbord/css/adminlte.min.css')}}">
  <link rel="stylesheet" href="{{url('dashbord/css/custom.css')}}">
  <!-- Google Font: Source Sans Pro -->
  {{-- 2026-08-21 — Google Fonts removed for fully-local assets; falls back to system fonts.
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  --}}
</head>
<body class="hold-transition login-page">
@include('common.noscript')

@yield('content')


<!-- jQuery -->
<script src="{{url('dashbord/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap 4 -->
<script src="{{url('dashbord/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{url('dashbord/js/adminlte.min.js')}}"></script>

</body>
</html>

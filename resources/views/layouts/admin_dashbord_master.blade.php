<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  @yield('head')
  <!-- Favicon icon -->
  <link rel="icon" type="image/png" href="{{url('images/dp.svg')}}">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="{{url('dashbord/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- OverlayScrollbars (local copy: css/overlayscrollbars1131) -->
  <link rel="stylesheet" href="{{url('css/overlayscrollbars1131/css/OverlayScrollbars.min.css')}}">">
  <!-- DataTables -->
  <link rel="stylesheet" href="{{url('dashbord/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{url('dashbord/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{url('dashbord/css/adminlte.min.css')}}">
  <link rel="stylesheet" href="{{url('dashbord/css/custom.css')}}">
  <link rel="stylesheet" href="{{url('dashbord/css/dp-admin.css')}}?v=20260920a">
  <link rel="preconnect" href="https://fonts.gstatic.com">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,600;0,700;1,300;1,500&display=swap" rel="stylesheet">
  <script src="{{url('dashbord/plugins/jquery/jquery-3.6.0.min.js')}}"></script>
   {{-- 2026-08-21 — legacy double-jQuery CDN load removed (it overwrote jQuery 3.6 with 1.12.4 and was a live link) --}} 
  {{-- 2026-08-21 — FontAwesome Pro CDN removed (no live links). The local free build is already loaded above: dashbord/plugins/fontawesome-free/css/all.min.css --}}
  <!-- Google Font: Source Sans Pro -->
  <!-- <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet"> -->
</head>

<body class="hold-transition dp-sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
@include('common.noscript')
    @include('common.a11y')
    @include('partials.marquee')
  <div class="wrapper">
    <!-- THE shared header bar (same on every page) -->
    @include('layouts.partials.dp-header', [
        'dpProfileUrl'   => route('avatar'),
        'dpFeedChatView' => 'admin.inbox._feed',
        'dpFeedTaskView' => 'admin.inbox._feed',
        'dpInboxChatUrl' => route('admin.inbox.messages'),
        'dpInboxTaskUrl' => route('admin.inbox.notifications'),
    ])
    <!-- Main Sidebar Container -->
    <aside class="main-sidebar dp-sidebar elevation-4">
      @include('layouts.partials.dp_admin_sidebar')
    </aside>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0 text-dark">Dashboard Delivering Parcel</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{route('/')}}">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->
      @yield('content')
      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
      </aside>
      <!-- /.control-sidebar -->
      <!-- Main Footer -->
    </div>
    <!-- <div class="test">

    </div> -->
    <div id="test">

    </div>
    <footer class="main-footer">
      <strong>Copyright &copy; 2021 <a href="{{route('/')}}">Delivering Parcel</a>.</strong>
      All rights reserved.
      <div class="float-right d-none d-sm-inline-block">
        <b>Anywhere to Everywhere</b>
      </div>
    </footer>
  </div>
  <!-- ./wrapper -->
  <!-- REQUIRED SCRIPTS -->
  <!--<script src="{{url('js/custom.js')}}"></script>-->
  <!-- jQuery -->
  <script src="{{asset('dashbord/plugins/jquery/jquery.min.js')}}"></script>
  <!-- jQuery UI 1.11.4 -->
  <script src="{{asset('dashbord/plugins/jquery-ui/jquery-ui.min.js')}}"></script>
  <!-- Bootstrap -->
  <script src="{{url('dashbord/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <!-- OverlayScrollbars (local copy: css/overlayscrollbars1131) -->
  <script src="{{url('css/overlayscrollbars1131/js/jquery.overlayScrollbars.min.js')}}"></script>
  <!-- AdminLTE App -->
  <script src="{{url('dashbord/js/adminlte.js')}}"></script>
  <script src="{{url('dashbord/js/dp-lazy.js')}}?v=20260920a"></script>
  <!-- OPTIONAL SCRIPTS -->
  <script src="{{url('dashbord/js/demo.js')}}"></script>
  <!-- PAGE PLUGINS -->
  <!-- jQuery Mapael -->
  <script src="{{url('dashbord/plugins/jquery-mousewheel/jquery.mousewheel.js')}}"></script>
  <script src="{{url('dashbord/plugins/raphael/raphael.min.j')}}s"></script>
  <script src="{{url('dashbord/plugins/jquery-mapael/jquery.mapael.min.js')}}"></script>
  <script src="{{url('dashbord/plugins/jquery-mapael/maps/usa_states.min.js')}}"></script>
  <!-- tables -->
  <script src="{{url('dashbord/plugins/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{url('dashbord/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
  <script src="{{url('dashbord/plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
  <script src="{{url('dashbord/plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
  <!-- ChartJS -->
  <script src="{{url('dashbord/plugins/chart.js/Chart.min.js')}}"></script>

  <!-- PAGE SCRIPTS -->

  <script type="text/javascript">
    /*to read a specific notification — delegated so scroll-appended items work too*/
    $(document).on('click', '.s_notification', function() {
      var id = $(this).attr("id");
      $.ajax({
        url: "{{ route('ReadNotification') }}",
        type: "GET",
        data: {
          id: id,
        },
        success: function(searchresults) {
          $('#test').html(searchresults);
          console.log('success');
        }
      });
      //end of ajax call
    });
  </script>
  <script type="text/javascript">
    /* Dropdown infinite scroll: load 10 more items when the feed box is scrolled to the bottom. */
    $(function() {
      $('.dp-feed').each(function() {
        var $box = $(this);
        var type = $box.data('type');
        var offset = 10;
        var busy = false;
        var done = false;
        $box.on('scroll', function() {
          if (busy || done) return;
          if ($box.scrollTop() + $box.innerHeight() >= $box[0].scrollHeight - 40) {
            busy = true;
            $.get("{{ route('admin.inbox.feed') }}", { type: type, offset: offset }, function(html) {
              if (!html || !html.trim()) {
                done = true;
              } else {
                $box.append(html);
                offset += 10;
              }
              busy = false;
            }).fail(function() {
              busy = false;
              done = true;
            });
          }
        });
      });
    });
  </script>
</body>

</html>
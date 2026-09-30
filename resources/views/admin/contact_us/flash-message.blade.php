@if ($message = Session::get('success'))
<div class="alert alert-success alert-block text-center">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
    </span>
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($message = Session::get('error'))
<div class="alert alert-danger alert-block text-center">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
    </span>
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($message = Session::get('warning'))
<div class="alert alert-warning alert-block text-center">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
    </span>
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($message = Session::get('info'))
<div class="alert alert-info alert-block text-center">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
    </span>
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger text-center">
    <button type="button" class="close" data-dismiss="alert">×</button>
    Please check the form below for errors
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif
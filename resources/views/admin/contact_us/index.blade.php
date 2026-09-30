<!-- Main layout for Html Start from here -->
@extends('layouts.admin_dashbord_master')
<!-- main layout ends here -->
<!-- title for header start from here -->
@section('head')
<title>Contacted Us | Deliveringparcel</title>
@endsection
<!-- title end here -->
<!-- Min content start from here -->
@section('content')
<!-- Main content -->
<section class="content order">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @include('admin.contact_us.flash-message')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Contacted Us <small class="text-muted">({{ $contact->total() }} submission{{ $contact->total() == 1 ? '' : 's' }})</small></h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="GET" action="{{ route('contactus.index') }}" class="form-row align-items-end mb-3">
                            <div class="form-group col-md-3 mb-2">
                                <label for="f-name">Name</label>
                                <input id="f-name" type="text" name="name" class="form-control form-control-sm"
                                       value="{{ $filters['name'] ?? '' }}" placeholder="Name contains…">
                            </div>
                            <div class="form-group col-md-3 mb-2">
                                <label for="f-email">Email</label>
                                <input id="f-email" type="text" name="email" class="form-control form-control-sm"
                                       value="{{ $filters['email'] ?? '' }}" placeholder="user@example.com">
                            </div>
                            <div class="form-group col-md-3 mb-2">
                                <label for="f-q">Message keyword</label>
                                <input id="f-q" type="text" name="q" class="form-control form-control-sm"
                                       value="{{ $filters['q'] ?? '' }}" placeholder="Search message text…">
                            </div>
                            <div class="form-group col-md-3 mb-2 text-right">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                                <a href="{{ route('contactus.index') }}" class="btn btn-sm btn-default">Reset</a>
                            </div>
                        </form>

                        @if($contact->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr style="background-color: #d0e7ff; color: #000;">
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Number</th>
                                        <th>Message</th>
                                        <th>Created at</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contact as $cont)
                                    <tr>
                                        <td>{{$cont->name}}</td>
                                        <td>{{$cont->email}}</td>
                                        <td>{{$cont->number}}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($cont->detail, 100) }}</td>
                                        <td>{{ $cont->created_at ? \Illuminate\Support\Carbon::parse($cont->created_at)->format('d M Y H:i') : '—' }}</td>
                                        <td class="text-center">
                                            <a href="{{route('contactus.show',$cont->id)}}" class="btn btn-info"><i class="fas fa-eye"></i></a>

                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-contact{{$cont->id}}">
                                                <a href="#" class="btn-danger"><i class="fas fa-trash"></i></a>
                                            </button>
                                        </td>
                                    </tr>
                                    @include('admin.contact_us.delete_modal')
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $contact->links('pagination::bootstrap-4') }}
                        </div>
                        @else
                        <p class="text-center text-lg text-muted">No contact submissions match these filters.</p>
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
<!-- Server-side pagination via Laravel; DataTables removed (it required all rows client-side) -->
@endsection
<!-- main content ends here -->

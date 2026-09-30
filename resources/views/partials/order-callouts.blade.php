{{-- Admin-manageable order-status callouts (Admin → Content → Order Callouts).
     Usage: @include('partials.order-callouts', ['statusKey' => 'ready_to_ship'])
     Known legacy keys: offer_pending, forwarding_no_tracking,
     forwarding_tracking_added, purchase_no_tracking, ready_to_ship, shipped.
     Any other status_key (e.g. raw order_status values) renders rows the
     admin creates for it — ready for the new-design pages. --}}
@php $dpCalloutItems = \App\Models\OrderCallout::forSlot($statusKey ?? ''); @endphp
@if(is_array($dpCalloutItems) && count($dpCalloutItems) > 0)
<div class="col-md-12 mt-4">
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bullhorn"></i>
                Callouts
            </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            @foreach($dpCalloutItems as $dpItem)
            <div class="callout callout-{{ $dpItem['style'] }}">
                @if(!empty($dpItem['heading']))
                <h5>{{ $dpItem['heading'] }}</h5>
                @endif
                @foreach(preg_split('/\r\n|\r|\n/', trim($dpItem['body'])) as $dpPara)
                <p>{{ $dpPara }}</p>
                @endforeach
            </div>
            @endforeach
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
@endif

@extends('admin.layouts.app')
@section('title', 'PDF Tools')
@section('page_title', 'PDF Tools')
@section('page_subtitle', 'Exportable documents (print / save-as-PDF)')

@section('content')
<div class="row">
    <div class="col-12 col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5><i class="fas fa-file-invoice-dollar text-primary"></i> Offer invoice</h5>
                <p class="muted small">A4 invoice for an offer order with line items and totals.</p>
                <form method="GET" action="{{ route('admin.tools.pdf-financial') }}" onsubmit="this.action='{{ url('admin/tools/pdf/invoice') }}/'+this.oid.value; return true;">
                    <input name="oid" type="number" min="1" class="form-control form-control-sm mb-2" placeholder="Offer order ID" required>
                    <button class="btn btn-primary btn-sm btn-block" type="submit">Generate</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5><i class="fas fa-box text-info"></i> Order summary</h5>
                <p class="muted small">Customer-facing order summary with items.</p>
                <form method="GET" onsubmit="this.action='{{ url('admin/tools/pdf/order') }}/'+this.oid.value; return true;">
                    <input name="oid" type="number" min="1" class="form-control form-control-sm mb-2" placeholder="Order ID" required>
                    <button class="btn btn-primary btn-sm btn-block" type="submit">Generate</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5><i class="fas fa-chart-line text-success"></i> Financial report</h5>
                <p class="muted small">Monthly accepted-offer revenue for a date range.</p>
                <form method="GET" action="{{ route('admin.tools.pdf-financial') }}">
                    <div class="d-flex gap-2 mb-2">
                        <input type="date" name="from" class="form-control form-control-sm">
                        <input type="date" name="to" class="form-control form-control-sm">
                    </div>
                    <button class="btn btn-primary btn-sm btn-block" type="submit">Generate</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')
@section('title', 'Shop Analytics')
@section('page_title', 'Shop Analytics')
@section('page_subtitle', 'Revenue, order KPIs and top products')

@section('content')
<div class="dp-kpi-grid mb-4">
    <div class="dp-kpi"><span class="dp-kpi-icon bg-success"><i class="fas fa-dollar-sign"></i></span><div class="dp-kpi-label">Total Revenue</div><div class="dp-kpi-value">${{ number_format($kpis['revenue'], 2) }}</div></div>
    <div class="dp-kpi"><span class="dp-kpi-icon bg-info"><i class="fas fa-box"></i></span><div class="dp-kpi-label">Orders</div><div class="dp-kpi-value">{{ number_format($kpis['orders']) }}</div></div>
    <div class="dp-kpi"><span class="dp-kpi-icon bg-primary"><i class="fas fa-receipt"></i></span><div class="dp-kpi-label">Avg Order Value</div><div class="dp-kpi-value">${{ number_format($kpis['avg_order'], 2) }}</div></div>
    <div class="dp-kpi"><span class="dp-kpi-icon bg-warning"><i class="fas fa-calendar-alt"></i></span><div class="dp-kpi-label">Revenue this month</div><div class="dp-kpi-value">${{ number_format($kpis['month_rev'], 2) }}</div></div>
</div>

<h5 class="mb-3"><i class="fas fa-crown mr-2 text-secondary"></i>Top products</h5>
<div class="dp-table-wrap p-2 p-md-0">
    <table class="table table-hover dp-table dp-card-mobile mb-0">
        <thead><tr><th>Product</th><th>Units sold</th><th>Revenue</th></tr></thead>
        <tbody>
            @forelse ($topProducts as $p)
                <tr>
                    <td data-label="Product"><strong>{{ $p->name }}</strong></td>
                    <td data-label="Units sold">{{ (int) $p->qty }}</td>
                    <td data-label="Revenue">${{ number_format((float) $p->revenue, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-muted">No sales data yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

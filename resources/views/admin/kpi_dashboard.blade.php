@extends('layouts.admin_dashbord_master')
@section('head')
<title>KPI Dashboard | DeliveringParcel</title>
@endsection

@section('content')
<style>
  /* Scoped to this page only — no legacy CSS touched. */
  .kpi-wrap{padding:12px}
  .kpi-zone{background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.08);padding:14px;margin-bottom:14px}
  .kpi-zone h4{margin:0 0 10px;font-size:15px;font-weight:800;color:#343a40;text-transform:uppercase;letter-spacing:.5px}
  .kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:10px}
  .kpi-tile{border-radius:8px;padding:14px;color:#fff;display:block;text-decoration:none;position:relative;overflow:hidden;min-height:86px;transition:transform .12s}
  .kpi-tile:hover{text-decoration:none;transform:translateY(-2px);color:#fff}
  .kpi-tile .kt-v{font-size:26px;font-weight:800;line-height:1.1}
  .kpi-tile .kt-l{font-size:12px;opacity:.9;margin-top:4px}
  .kpi-tile .kt-d{font-size:12px;margin-top:6px;font-weight:700}
  .kt-up{color:#d4f8d4}.kt-dn{color:#ffd6d6}
  .kpi-pills{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:0}
  .kpi-pills a{padding:5px 12px;border-radius:14px;background:#e9ecef;color:#343a40;font-size:12.5px;font-weight:600;text-decoration:none}
  .kpi-pills a.on{background:#0d6efd;color:#fff}
  .funnel-step{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px dashed #e9ecef}
  .funnel-step:last-child{border-bottom:none}
  .funnel-bar{height:9px;border-radius:5px;background:linear-gradient(90deg,#0d6efd,#17a2b8);min-width:6px}
  .kpi-table{width:100%;font-size:13px}
  .kpi-table th{font-size:11px;text-transform:uppercase;color:#6c757d;border-bottom:2px solid #e9ecef;padding:6px 8px;text-align:left}
  .kpi-table td{padding:7px 8px;border-bottom:1px solid #f1f3f5}
  .dot-ok{display:inline-block;width:10px;height:10px;border-radius:50%;background:#28a745}
  .dot-warn{display:inline-block;width:10px;height:10px;border-radius:50%;background:#ffc107}
  @media(max-width:767px){.kpi-grid{grid-template-columns:repeat(2,1fr)}}
</style>

<div class="kpi-wrap">
  {{-- Header + range filter --}}
  <div class="kpi-zone" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <div>
      <h4 style="margin:0">Executive KPI Dashboard</h4>
      <small class="text-muted">Live · {{ ucfirst($range === 'today' ? 'today' : ($range === 'year' ? 'this year' : $range . ' days')) }}
        · compares vs previous period · legacy /admin-orders untouched</small>
    </div>
    <div class="kpi-pills">
      @foreach(['today'=>'Today','7'=>'7 Days','30'=>'30 Days','90'=>'90 Days','year'=>'This Year'] as $k=>$lbl)
        <a href="{{ url('admin-dashboard2', ['range'=>$k]) }}" class="{{ $range === $k ? 'on' : '' }}">{{ $lbl }}</a>
      @endforeach
    </div>
  </div>

  {{-- Zone 1: Business overview (with vs-previous-period deltas) --}}
  <div class="kpi-zone">
    <h4>Business Overview</h4>
    <div class="kpi-grid">
      <div class="kpi-tile" style="background:linear-gradient(135deg,#0d6efd,#3d8bfd)">
        <div class="kt-v">${{ number_format($kpi['revenue']['v'] ?? 0, 2) }}</div>
        <div class="kt-l">Revenue (paid)</div>
        @if(!is_null($kpi['revenue']['d'] ?? null))
          <div class="kt-d {{ ($kpi['revenue']['d']) >= 0 ? 'kt-up' : 'kt-dn' }}">{{ ($kpi['revenue']['d']) >= 0 ? '▲' : '▼' }} {{ abs($kpi['revenue']['d']) }}% vs prev.</div>
        @endif
      </div>
      <div class="kpi-tile" style="background:linear-gradient(135deg,#6f42c1,#9d6bff)">
        <div class="kt-v">{{ $kpi['orders']['v'] ?? 0 }}</div>
        <div class="kt-l">Orders</div>
        @if(!is_null($kpi['orders']['d'] ?? null))
          <div class="kt-d {{ ($kpi['orders']['d']) >= 0 ? 'kt-up' : 'kt-dn' }}">{{ ($kpi['orders']['d']) >= 0 ? '▲' : '▼' }} {{ abs($kpi['orders']['d']) }}% vs prev.</div>
        @endif
      </div>
      <div class="kpi-tile" style="background:linear-gradient(135deg,#198754,#3dd584)">
        <div class="kt-v">${{ number_format($kpi['avg_order']['v'] ?? 0, 2) }}</div>
        <div class="kt-l">Avg. Paid Order Value</div>
      </div>
      <div class="kpi-tile" style="background:linear-gradient(135deg,#fd7e14,#ffab4d)">
        <div class="kt-v">{{ $kpi['new_customers']['v'] ?? 0 }}</div>
        <div class="kt-l">New Customers</div>
        @if(!is_null($kpi['new_customers']['d'] ?? null))
          <div class="kt-d {{ ($kpi['new_customers']['d']) >= 0 ? 'kt-up' : 'kt-dn' }}">{{ ($kpi['new_customers']['d']) >= 0 ? '▲' : '▼' }} {{ abs($kpi['new_customers']['d']) }}% vs prev.</div>
        @endif
      </div>
    </div>
  </div>
  {{-- Zone 2: ACTION REQUIRED — operational, every tile drills down --}}
  <div class="kpi-zone" style="border-top:4px solid #dc3545">
    <h4>⚠ Action Required</h4>
    <div class="kpi-grid">
      <a class="kpi-tile" style="background:linear-gradient(135deg,#dc3545,#f0566b)" href="{{ url('admin/payments/ledger') }}">
        <div class="kt-v">{{ $action['verify_payments'] ?? 0 }}</div><div class="kt-l">Payments to Verify (receipts)</div>
      </a>
      <a class="kpi-tile" style="background:linear-gradient(135deg,#b02a37,#d9534f)" href="{{ url('admin/payments/ledger') }}">
        <div class="kt-v">{{ $action['awaiting_payment'] ?? 0 }}</div><div class="kt-l">Payments Awaited</div>
      </a>
      <a class="kpi-tile" style="background:linear-gradient(135deg,#c2185b,#e75480)" href="{{ route('admin-orders') }}">
        <div class="kt-v">{{ $action['no_offer'] ?? 0 }}</div><div class="kt-l">Orders Without Offer</div>
      </a>
      <a class="kpi-tile" style="background:linear-gradient(135deg,#d63384,#eb71a4)" href="{{ route('admin-orders', ['status'=>'Offer Rejected']) }}">
        <div class="kt-v">{{ $action['offer_rejected'] ?? 0 }}</div><div class="kt-l">Offers Rejected (revise)</div>
      </a>
      <a class="kpi-tile" style="background:linear-gradient(135deg,#e8590c,#ff922b)" href="{{ url('admin/payments/ledger') }}">
        <div class="kt-v">{{ $action['failed_payments'] ?? 0 }}</div><div class="kt-l">Failed Payments</div>
      </a>
      <a class="kpi-tile" style="background:linear-gradient(135deg,#e67700,#f5b301)" href="{{ route('admin-orders') }}">
        <div class="kt-v">{{ $action['tracking_needed'] ?? 0 }}</div><div class="kt-l">Paid · Awaiting Package/Tracking</div>
      </a>
      @if(($action['pending_reviews'] ?? 0) > 0 || true)
      <a class="kpi-tile" style="background:linear-gradient(135deg,#5c7cfa,#845ef7)" href="{{ url('admin/reviews') }}">
        <div class="kt-v">{{ $action['pending_reviews'] ?? 0 }}</div><div class="kt-l">Reviews Pending Approval</div>
      </a>
      @endif
    </div>
  </div>

  {{-- Zone 3: Revenue / orders trend chart --}}
  <div class="kpi-zone">
    <h4>Revenue &amp; Orders Trend</h4>
    <canvas id="kpiTrend" height="90"></canvas>
  </div>

  {{-- Zone 4: Order funnel + customers side by side --}}
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(330px,1fr));gap:14px">
    <div class="kpi-zone">
      <h4>Order Funnel (all time)</h4>
      @foreach(($funnel['steps'] ?? []) as $i => $s)
        @php $w = max(2, (int) round($s[2] * 0.98)); @endphp
        <div class="funnel-step">
          <div style="width:150px;font-size:12.5px;color:#495057;font-weight:600">{{ $s[0] }}</div>
          <div class="funnel-bar" style="width:{{ $w }}%"></div>
          <div style="font-size:12px;color:#6c757d;white-space:nowrap">{{ number_format($s[1]) }} · {{ $s[2] }}%</div>
        </div>
      @endforeach
      <div style="margin-top:8px;font-size:13px"><b>Order → Completion:</b> {{ $funnel['order_to_done'] ?? 0 }}%</div>
    </div>

    <div class="kpi-zone">
      <h4>Customers</h4>
      <div class="kpi-grid" style="grid-template-columns:repeat(2,1fr)">
        <div class="kpi-tile" style="background:linear-gradient(135deg,#0ca678,#20c997)"><div class="kt-v">{{ $customers['total'] ?? 0 }}</div><div class="kt-l">Total Customers</div></div>
        <div class="kpi-tile" style="background:linear-gradient(135deg,#099268,#12b886)"><div class="kt-v">{{ $customers['active'] ?? 0 }}</div><div class="kt-l">Active (open orders)</div></div>
        <div class="kpi-tile" style="background:linear-gradient(135deg,#087f5b,#0ca678)"><div class="kt-v">{{ $customers['repeat'] ?? 0 }}</div><div class="kt-l">Repeat (2+ orders)</div></div>
        <div class="kpi-tile" style="background:linear-gradient(135deg,#2b8a3e,#51cf66)"><div class="kt-v">{{ $customers['avg_orders'] ?? 0 }}</div><div class="kt-l">Avg. Orders / Customer</div></div>
      </div>
    </div>
  </div>
  {{-- Zone 5: Payment providers + payment statuses --}}
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(330px,1fr));gap:14px">
    <div class="kpi-zone">
      <h4>Payment Providers (period)</h4>
      <table class="kpi-table">
        <thead><tr><th>Health</th><th>Provider</th><th>Txns</th><th>Volume</th><th>Failed</th><th>Refunded</th></tr></thead>
        <tbody>
        @foreach(($providers ?? []) as $p)
          <tr>
            <td><span class="{{ $p['health'] === 'warn' ? 'dot-warn' : 'dot-ok' }}"></span></td>
            <td class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $p['gw'])) }}</td>
            <td>{{ $p['n'] }}</td>
            <td>${{ number_format($p['volume'], 2) }}</td>
            <td>{{ $p['failed'] ?: '—' }}</td>
            <td>${{ number_format($p['refunded'], 2) }}</td>
          </tr>
        @endforeach
        @if(empty($providers))<tr><td colspan="6" class="text-muted">No payments in this period.</td></tr>@endif
        </tbody>
      </table>
    </div>

    <div class="kpi-zone">
      <h4>Payment Statuses (period)</h4>
      <div class="kpi-grid" style="grid-template-columns:repeat(2,1fr)">
        <div class="kpi-tile" style="background:linear-gradient(135deg,#2f9e44,#51cf66)"><div class="kt-v">${{ number_format($payStatus['paid']['amt'] ?? 0, 2) }}</div><div class="kt-l">Paid · {{ $payStatus['paid']['n'] ?? 0 }}</div></div>
        <div class="kpi-tile" style="background:linear-gradient(135deg,#e8590c,#ffa94d)"><div class="kt-v">${{ number_format($payStatus['awaiting']['amt'] ?? 0, 2) }}</div><div class="kt-l">Awaiting · {{ $payStatus['awaiting']['n'] ?? 0 }}</div></div>
        <div class="kpi-tile" style="background:linear-gradient(135deg,#1971c2,#4dabf7)"><div class="kt-v">${{ number_format($payStatus['verifying']['amt'] ?? 0, 2) }}</div><div class="kt-l">Verifying · {{ $payStatus['verifying']['n'] ?? 0 }}</div></div>
        <div class="kpi-tile" style="background:linear-gradient(135deg,#c92a2a,#ff6b6b)"><div class="kt-v">{{ $payStatus['failed']['n'] ?? 0 }}</div><div class="kt-l">Failed Payments</div></div>
        <div class="kpi-tile" style="background:linear-gradient(135deg,#5f3dc4,#845ef7)"><div class="kt-v">${{ number_format($payStatus['refunded_amt'] ?? 0, 2) }}</div><div class="kt-l">Refunded Amount</div></div>
        <div class="kpi-tile" style="background:linear-gradient(135deg,#495057,#868e96)"><div class="kt-v">{{ $payStatus['processing']['n'] ?? 0 }}</div><div class="kt-l">Processing</div></div>
      </div>
    </div>
  </div>

  {{-- Zone 6: Delivery operations + risk/system (guarded optional tables) --}}
  <div class="kpi-zone">
    <h4>Delivery &amp; Operations</h4>
    <div class="kpi-grid">
      <div class="kpi-tile" style="background:linear-gradient(135deg,#1864ab,#4dabf7)"><div class="kt-v">{{ $ops['in_transit'] ?? 0 }}</div><div class="kt-l">In Transit / Processing</div></div>
      <div class="kpi-tile" style="background:linear-gradient(135deg,#2b8a3e,#69db7c)"><div class="kt-v">{{ $ops['delivered_today'] ?? 0 }}</div><div class="kt-l">Delivered Today</div></div>
      <div class="kpi-tile" style="background:linear-gradient(135deg,#087f5b,#38d9a9)"><div class="kt-v">{{ $ops['completed'] ?? 0 }}</div><div class="kt-l">Completed</div></div>
      <div class="kpi-tile" style="background:linear-gradient(135deg,#2f9e44,#8ce99a)"><div class="kt-v">{{ $ops['received'] ?? 0 }}</div><div class="kt-l">Receipt Confirmed</div></div>
      <div class="kpi-tile" style="background:linear-gradient(135deg,#364fc7,#748ffc)"><div class="kt-v">{{ $ops['avg_delivery_days'] ?? '—' }}{{ !is_null($ops['avg_delivery_days'] ?? null) ? ' d' : '' }}</div><div class="kt-l">Avg. Delivery Time</div></div>
      @if(!is_null($extra['returns_open'] ?? null))
        <div class="kpi-tile" style="background:linear-gradient(135deg,#d6336c,#f783ac)"><div class="kt-v">{{ $extra['returns_open'] }}</div><div class="kt-l">Open Returns</div></div>
      @endif
      @if(!is_null($extra['claims_open'] ?? null))
        <div class="kpi-tile" style="background:linear-gradient(135deg,#c2255c,#faa2c1)"><div class="kt-v">{{ $extra['claims_open'] }}</div><div class="kt-l">Open Claims</div></div>
      @endif
      @if(!is_null($extra['failed_jobs'] ?? null))
        <div class="kpi-tile" style="background:linear-gradient(135deg,#862e9c,#e599f7)"><div class="kt-v">{{ $extra['failed_jobs'] }}</div><div class="kt-l">Failed Queue Jobs</div></div>
      @endif
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  (function () {
    var el = document.getElementById('kpiTrend');
    if (!el || typeof Chart === 'undefined') return;
    var labels = @json($trend['labels'] ?? []);
    var revenue = @json($trend['revenue'] ?? []);
    var orders = @json($trend['orders'] ?? []);
    new Chart(el, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          { label: 'Revenue ($)', data: revenue, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.12)', fill: true, tension: .3, yAxisID: 'y' },
          { label: 'Orders', data: orders, borderColor: '#198754', backgroundColor: 'transparent', tension: .3, yAxisID: 'y1' }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        scales: {
          y:  { position: 'left',  ticks: { callback: function (v) { return '$' + v; } } },
          y1: { position: 'right', grid: { drawOnChartArea: false } }
        }
      }
    });
  })();
</script>
@endsection


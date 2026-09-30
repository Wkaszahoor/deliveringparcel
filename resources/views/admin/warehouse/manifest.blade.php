<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dispatch Manifest — {{ $shipment->code }}</title>
    <style>
        @page { size: A4; margin: 14mm; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1c2b39; font-size: 13px; margin: 0; }
        .head { display: flex; justify-content: space-between; border-bottom: 3px solid #16324f; padding-bottom: 10px; margin-bottom: 14px; }
        h1 { margin: 0; font-size: 20px; color: #16324f; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #f0f4f8; text-align: left; padding: 6px 8px; font-size: 11px; text-transform: uppercase; color: #55677a; }
        td { padding: 6px 8px; border-bottom: 1px solid #e8edf2; }
        .meta td { border: 0; padding: 3px 8px; }
        .no-print { position: fixed; top: 10px; right: 10px; }
        .no-print button { background: #0d6efd; color: #fff; border: 0; padding: 8px 18px; border-radius: 6px; cursor: pointer; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="setTimeout(function(){ window.print(); }, 400)">
<div class="no-print"><button onclick="window.print()">🖨 Print Manifest</button></div>

<div class="head">
    <div>
        <h1>Dispatch Manifest</h1>
        <div>DeliveringParcel — Warehouse Operations</div>
    </div>
    <div style="text-align:right">
        <strong>{{ $shipment->code }}</strong><br>
        {{ now()->format('M d, Y H:i') }}<br>
        Carrier: {{ $shipment->carrier_name ?: '—' }}<br>
        Tracking: {{ $shipment->tracking_number ?: '—' }}
    </div>
</div>

<table class="meta">
    <tr>
        <td style="width:50%"><strong>Ship to</strong><br>
            {{ optional($shipment->user)->name ?: '—' }}<br>
            {{ optional($shipment->user)->email ?: '' }}
        </td>
        <td style="text-align:right"><strong>Packages</strong> {{ $shipment->packages->count() }}<br>
            <strong>Status</strong> {{ ucfirst($shipment->status) }}
        </td>
    </tr>
</table>

<table>
    <thead><tr><th>#</th><th>Package</th><th>Expected tracking</th><th>Bin</th><th>Condition at dispatch</th></tr></thead>
    <tbody>
        @forelse ($shipment->packages as $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>PKG-{{ $p->id }}</td>
                <td>{{ $p->expected_tracking ?: '—' }}</td>
                <td>{{ optional($p->bin)->code ?: '—' }}</td>
                <td>{{ ucfirst($p->status) }}</td>
            </tr>
        @empty
            <tr><td colspan="5">No packages.</td></tr>
        @endforelse
    </tbody>
</table>

<p style="margin-top:24px">Handler signature: ______________________________ &nbsp;&nbsp; Date: ______________</p>
</body>
</html>

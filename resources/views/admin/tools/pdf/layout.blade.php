<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('doc_title', 'Document')</title>
    <style>
        @page { size: A4; margin: 16mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1c2b39; font-size: 13px; margin: 0; }
        .doc-head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #16324f; padding-bottom: 12px; margin-bottom: 18px; }
        .doc-head h1 { margin: 0; font-size: 20px; color: #16324f; }
        .doc-meta { text-align: right; font-size: 12px; color: #55677a; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th { background: #f0f4f8; text-align: left; padding: 7px 9px; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #55677a; border-bottom: 1px solid #d7e0e8; }
        td { padding: 7px 9px; border-bottom: 1px solid #e8edf2; vertical-align: top; }
        .totals td { border: 0; padding: 4px 9px; }
        .totals .grand td { border-top: 2px solid #16324f; font-weight: bold; font-size: 15px; padding-top: 8px; }
        .muted { color: #55677a; }
        .doc-foot { margin-top: 26px; border-top: 1px solid #d7e0e8; padding-top: 8px; font-size: 11px; color: #55677a; display: flex; justify-content: space-between; }
        @media print { .no-print { display: none; } body { padding: 0; } }
        .no-print { position: fixed; top: 10px; right: 10px; }
        .no-print button { background: #0d6efd; color: #fff; border: 0; padding: 8px 18px; border-radius: 6px; font-size: 14px; cursor: pointer; }
    </style>
</head>
<body onload="setTimeout(function(){ window.print(); }, 400)">
<div class="no-print"><button onclick="window.print()">🖨 Print / Save as PDF</button></div>
<div class="doc-head">
    <div>
        <h1>DeliveringParcel</h1>
        <div class="muted">International parcel forwarding &amp; shipping</div>
    </div>
    <div class="doc-meta">
        <strong>@yield('doc_title')</strong><br>
        Generated: {{ now()->format('M d, Y H:i') }}
    </div>
</div>
@yield('doc_body')
<div class="doc-foot">
    <span>DeliveringParcel — generated document</span>
    <span>Page rendered {{ now()->format('Y-m-d') }}</span>
</div>
</body>
</html>

@extends('layouts.itrack')
@section('title', 'Reports')

@section('content')
    <div class="page-head">
        <div>
            <h1>Reports</h1>
            <p>Sales summary from {{ $from }} to {{ $to }}.</p>
        </div>
        <button class="btn btn-primary no-print" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button>
    </div>

    <form method="GET" action="{{ route('reports.index') }}" class="toolbar no-print">
        <label class="hint">From</label>
        <input type="date" name="from" class="form-control" style="width:auto" value="{{ $from }}">
        <label class="hint">To</label>
        <input type="date" name="to" class="form-control" style="width:auto" value="{{ $to }}">
        <button class="btn btn-outline"><i class="bi bi-funnel"></i> Generate</button>
    </form>

    <div class="report-stats">
        <div class="stat green"><div class="ico"><i class="bi bi-cash-stack"></i></div><div><strong>&#8369;{{ number_format($totalSales, 2) }}</strong><span>Total Sales</span></div></div>
        <div class="stat blue"><div class="ico"><i class="bi bi-receipt"></i></div><div><strong>{{ number_format($transactions) }}</strong><span>Transactions</span></div></div>
        <div class="stat amber"><div class="ico"><i class="bi bi-box2"></i></div><div><strong>{{ number_format($itemsSold) }}</strong><span>Items Sold</span></div></div>
        <div class="stat red"><div class="ico"><i class="bi bi-calculator"></i></div><div><strong>&#8369;{{ number_format($average, 2) }}</strong><span>Average Sale</span></div></div>
    </div>

    <div class="two-col">
        <div class="card">
            <div class="card-title">Daily Sales</div>
            <table class="table">
                <thead><tr><th>Date</th><th>Transactions</th><th class="num">Total</th></tr></thead>
                <tbody>
                    @forelse ($daily as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->day)->format('M d, Y') }}</td>
                            <td>{{ $row->orders }}</td>
                            <td class="num">&#8369;{{ number_format($row->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty" style="padding:14px">No sales in this period.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-title">Top Selling Products</div>
            <table class="table">
                <thead><tr><th>Product</th><th>Sold</th><th class="num">Revenue</th></tr></thead>
                <tbody>
                    @forelse ($top as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->qty }}</td>
                            <td class="num">&#8369;{{ number_format($row->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty" style="padding:14px">No sales in this period.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt">
        <div class="card-title">Items to Restock (low or out of stock)</div>
        <table class="table">
            <thead><tr><th>Product</th><th>Barcode</th><th>Aisle</th><th>Stock</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($lowStock as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td><code>{{ $product->barcode }}</code></td>
                        <td>{{ $product->aisle ?: '-' }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td><span class="badge badge-{{ $product->status_key }}">{{ $product->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty" style="padding:14px">Everything is well stocked.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
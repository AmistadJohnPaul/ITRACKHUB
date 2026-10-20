@extends('layouts.itrack')
@section('title', 'Sales / Transactions')

@section('content')
    <div class="page-head">
        <div>
            <h1>Sales / Transactions</h1>
            <p>Every completed checkout from Scan / POS.</p>
        </div>
        <a href="{{ route('sales.export', request()->query()) }}" class="btn btn-primary"><i class="bi bi-download"></i> Export CSV</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('sales.index') }}" class="toolbar">
            <label class="hint">From</label>
            <input type="date" name="from" class="form-control" style="width:auto" value="{{ request('from') }}">
            <label class="hint">To</label>
            <input type="date" name="to" class="form-control" style="width:auto" value="{{ request('to') }}">
            <input type="text" name="search" class="form-control grow" value="{{ request('search') }}" placeholder="Search receipt no...">
            <button class="btn btn-outline"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route('sales.index') }}" class="btn btn-outline">Reset</a>
        </form>

        <p class="muted" style="margin-bottom:10px">Total for this list: <strong>&#8369;{{ number_format($periodTotal, 2) }}</strong></p>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>#</th><th>Receipt No.</th><th>Date &amp; Time</th><th>Items</th><th>Total Amount</th><th>Payment Method</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $sales->firstItem() + $loop->index }}</td>
                            <td><strong>{{ $sale->receipt_no }}</strong></td>
                            <td>{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $sale->items_sum_quantity ?? 0 }}</td>
                            <td>&#8369;{{ number_format($sale->total_amount, 2) }}</td>
                            <td>{{ $sale->payment_method }}</td>
                            <td><a href="{{ route('sales.show', $sale) }}" class="icon-btn" title="View receipt"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><i class="bi bi-receipt"></i>No transactions found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $sales->links('partials.pagination') }}
    </div>
@endsection
@extends('layouts.itrack')
@section('title', 'Receipt ' . $sale->receipt_no)

@section('content')
    <div class="page-head no-print">
        <div>
            <h1>Receipt {{ $sale->receipt_no }}</h1>
            <p><a href="{{ route('sales.index') }}">Sales</a> / {{ $sale->receipt_no }}</p>
        </div>
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>

    <div class="card receipt">
        <div style="text-align:center;margin-bottom:14px">
            <h2>{{ \App\Models\Setting::get('store_name', 'ITrackHub') }}</h2>
            <p class="muted">{{ $sale->created_at->format('M d, Y h:i A') }}</p>
            <p class="muted">Cashier: {{ $sale->user->name ?? '-' }} &middot; {{ $sale->payment_method }}</p>
        </div>

        <table class="table">
            <thead><tr><th>Item</th><th>Qty</th><th class="num">Price</th><th class="num">Subtotal</th></tr></thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="num">&#8369;{{ number_format($item->unit_price, 2) }}</td>
                        <td class="num">&#8369;{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cart-total"><span>Total</span><span>&#8369;{{ number_format($sale->total_amount, 2) }}</span></div>
    </div>
@endsection
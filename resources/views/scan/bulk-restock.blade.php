@extends('layouts.main')
@section('title', 'Bulk Restock')

@section('content')
    <h3>🚚 Unload Truck / Bulk Restock</h3>
    <p class="text-muted">Add quantities for multiple products at once, then save all at once.</p>

    <form action="{{ route('scan.bulkRestock.save') }}" method="POST" class="bg-white p-3 rounded shadow-sm">
        @csrf
        <table class="table">
            <thead><tr><th>Product</th><th>Barcode</th><th style="width:150px">Add Quantity</th></tr></thead>
            <tbody>
                @foreach ($products as $i => $product)
                    <tr>
                        <td>
                            {{ $product->name }}
                            <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $product->id }}">
                        </td>
                        <td><code>{{ $product->barcode }}</code></td>
                        <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control" value="0" min="0"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button class="btn btn-primary">Save All Updates</button>
    </form>
@endsection
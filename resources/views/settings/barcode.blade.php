@extends('layouts.itrack')
@section('title', 'Barcode Settings')

@section('content')
    <div class="page-head">
        <div><h1>Settings</h1><p>Store information, users, categories and barcode options.</p></div>
    </div>
    @include('partials.settings-tabs')

    <form method="POST" action="{{ route('settings.barcode.update') }}" class="card" style="max-width:640px">
        @csrf @method('PUT')

        <label class="form-label" style="margin-top:0">Low-stock limit (units)</label>
        <input type="number" min="1" max="1000" name="low_stock_threshold" class="form-control"
               value="{{ old('low_stock_threshold', \App\Models\Product::lowThreshold()) }}" required>
        <p class="hint">An item is "Low Stock" when its quantity is this number or less. It controls the dashboard, the bell icon and the Inventory page.</p>
        @error('low_stock_threshold') <div class="error-text">{{ $message }}</div> @enderror

        <div class="switch-row">
            <input type="checkbox" id="pos_auto_add" name="pos_auto_add" value="1" @checked(\App\Models\Setting::get('pos_auto_add', 0))>
            <label for="pos_auto_add">
                <strong>Auto-add scanned items to the cart</strong><br>
                <span class="hint">When ON, every successful scan in Scan / POS adds 1 unit straight to the cart. When OFF, the cashier checks the details first and presses "Add to Cart".</span>
            </label>
        </div>

        <div class="form-actions"><button class="btn btn-primary"><i class="bi bi-check2"></i> Save Changes</button></div>
    </form>
@endsection
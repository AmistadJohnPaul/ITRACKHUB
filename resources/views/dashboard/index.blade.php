@extends('layouts.itrack')
@section('title', 'Dashboard')

@section('content')
    <div class="banner">
        <div>
            <h2>{{ $greeting }}, {{ auth()->user()->name }}!</h2>
            <p>Track your sales, manage your products, and keep your shelves stocked.</p>
        </div>
        <div class="tag">Scan. Track. Grow.</div>
    </div>

    <div class="stats">
        <div class="stat green">
            <div class="ico"><i class="bi bi-cart3"></i></div>
            <div><strong>&#8369;{{ number_format($todaySales, 2) }}</strong><span>Today's Sales</span></div>
        </div>
        <div class="stat blue">
            <div class="ico"><i class="bi bi-box2"></i></div>
            <div><strong>{{ number_format($itemsSold) }}</strong><span>Items Sold</span></div>
        </div>
        <div class="stat amber">
            <div class="ico"><i class="bi bi-boxes"></i></div>
            <div><strong>{{ number_format($totalProducts) }}</strong><span>Total Products</span></div>
        </div>
        <div class="stat red">
            <div class="ico"><i class="bi bi-exclamation-triangle"></i></div>
            <div><strong>{{ number_format($lowStock) }}</strong><span>Low Stock</span></div>
        </div>
    </div>

    <div class="dash-grid">
        <div class="card">
            <div class="card-title">
                Sales Overview
                <select class="form-control" style="width:auto;padding:6px 10px" onchange="location='?range='+this.value">
                    <option value="week" @selected($range === 'week')>This Week</option>
                    <option value="month" @selected($range === 'month')>This Month</option>
                </select>
            </div>
            <div class="chart-box"><canvas id="salesChart"></canvas></div>
        </div>

        <div class="card">
            <div class="card-title">Top Selling Products</div>
            @if ($topProducts->isEmpty())
                <div class="empty"><i class="bi bi-bar-chart"></i>No sales yet. Make a sale in Scan / POS.</div>
            @else
                <ul class="top-list">
                    @foreach ($topProducts as $i => $row)
                        <li>
                            <span class="rank">{{ $i + 1 }}</span>
                            <span class="nm">{{ $row->product_name }}</span>
                            <span class="sold">{{ $row->sold }} sold</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card">
            <div class="card-title">Quick Actions</div>
            <div class="actions-grid">
                <a class="action a-blue" href="{{ route('pos.index') }}"><i class="bi bi-upc-scan"></i> Scan Product</a>
                <a class="action a-green" href="{{ route('products.create') }}"><i class="bi bi-plus-circle"></i> Add Product</a>
                <a class="action a-amber" href="{{ route('products.index') }}"><i class="bi bi-box-seam"></i> View Products</a>
                <a class="action a-indigo" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart-line"></i> Sales Report</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Sales',
                data: @json($values),
                borderColor: '#1a7f4b',
                backgroundColor: 'rgba(26,127,75,.12)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#1a7f4b'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {legend: {display: false}},
            scales: {
                y: {beginAtZero: true, ticks: {callback: function (v) { return '\u20B1' + v; }}},
                x: {grid: {display: false}}
            }
        }
    });
</script>
@endpush
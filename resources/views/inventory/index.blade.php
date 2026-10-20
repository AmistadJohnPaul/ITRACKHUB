@extends('layouts.itrack')
@section('title', 'Inventory')

@section('content')
    <div class="page-head">
        <div>
            <h1>Inventory</h1>
            <p>Current stock levels. Low-stock limit: {{ \App\Models\Product::lowThreshold() }} units (change it in Settings &rarr; Barcode Settings).</p>
        </div>
    </div>

    <div class="card">
        <div class="toolbar">
            <div class="pills">
                @foreach (['all' => 'All', 'in' => 'In Stock', 'low' => 'Low Stock', 'out' => 'Out of Stock'] as $key => $label)
                    <a class="pill {{ $status === $key ? 'active' : '' }}"
                       href="{{ route('inventory.index', array_filter(['status' => $key === 'all' ? null : $key, 'search' => request('search')])) }}">
                        {{ $label }} ({{ $counts[$key] }})
                    </a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('inventory.index') }}" class="grow" style="display:flex;gap:8px;justify-content:flex-end">
                @if ($status !== 'all') <input type="hidden" name="status" value="{{ $status }}"> @endif
                <input type="text" name="search" class="form-control" style="max-width:320px" value="{{ request('search') }}" placeholder="Search product...">
                <button class="btn btn-outline"><i class="bi bi-search"></i></button>
            </form>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>#</th><th>Product</th><th>Barcode</th><th>Aisle</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $products->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="cell-prod">
                                    @if ($product->image_url)
                                        <img class="thumb" src="{{ $product->image_url }}" alt="">
                                    @else
                                        <span class="thumb"><i class="bi bi-image"></i></span>
                                    @endif
                                    <strong>{{ $product->name }}</strong>
                                </div>
                            </td>
                            <td><code>{{ $product->barcode }}</code></td>
                            <td>{{ $product->aisle ?: '-' }}</td>
                            <td><strong>{{ $product->quantity }}</strong></td>
                            <td><span class="badge badge-{{ $product->status_key }}">{{ $product->status }}</span></td>
                            <td>
                                <button type="button" class="btn btn-outline btn-sm"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-stock="{{ $product->quantity }}"
                                        onclick="openAdjust(this)">
                                    <i class="bi bi-arrow-left-right"></i> Adjust
                                </button>
                                <a href="{{ route('products.edit', $product) }}" class="icon-btn" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><i class="bi bi-clipboard-data"></i>No items match this filter.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links('partials.pagination') }}
    </div>

    <div class="modal-backdrop" id="adjustModal">
        <form class="modal" method="POST" id="adjustForm">
            @csrf
            <h3>Adjust Stock</h3>
            <p class="muted" id="adjustInfo"></p>

            <label class="form-label">Action</label>
            <select name="type" class="form-control">
                <option value="in">Add stock (delivery / restock)</option>
                <option value="out">Remove stock (damaged / lost)</option>
            </select>

            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" min="1" value="1" class="form-control" required>

            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('adjustModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function openAdjust(btn) {
        document.getElementById('adjustForm').action = @json(url('/inventory')) + '/' + btn.dataset.id + '/adjust';
        document.getElementById('adjustInfo').textContent = btn.dataset.name + ' (current stock: ' + btn.dataset.stock + ')';
        document.getElementById('adjustModal').classList.add('open');
    }
</script>
@endpush
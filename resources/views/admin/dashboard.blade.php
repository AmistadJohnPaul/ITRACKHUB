@extends('layouts.main')
@section('title', 'Admin Dashboard')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <h3>Admin Dashboard</h3>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card p-3 bg-white shadow-sm">
                <strong>Total Products</strong>
                <h2>{{ $products->count() }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 bg-white shadow-sm">
                <strong>Low Stock (&lt; 5)</strong>
                <h2 class="text-danger">{{ $lowStock }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 bg-white shadow-sm">
                <strong>Staff Accounts</strong>
                <h2>{{ $totalStaff }}</h2>
                <a href="{{ route('admin.staff') }}" class="btn btn-sm btn-outline-primary mt-2">Manage Staff</a>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mb-2">
        <h4>Inventory</h4>
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Add Product</a>
    </div>

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>Name</th><th>Barcode</th><th>Category</th><th>Qty</th><th>Price</th><th>Aisle</th><th>Location</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td><code>{{ $product->barcode }}</code></td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>₱{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->aisle ?? '-' }}</td>
                    <td>{{ $product->location }}</td>
                    <td>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-secondary">Edit</a>
                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $product->id }}, '{{ addslashes($product->name) }}')">Delete</button>
                        <form id="delete-form-{{ $product->id }}" action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-none">
                            @csrf @method('DELETE')
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">No products yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h4 class="mt-4">Recent Stock Activity</h4>
    <table class="table table-sm table-bordered bg-white">
        <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>When</th></tr></thead>
        <tbody>
            @forelse ($recentMovements as $m)
                <tr>
                    <td>{{ $m->product->name ?? 'Deleted product' }}</td>
                    <td>{{ strtoupper($m->type) }}</td>
                    <td>{{ $m->quantity }}</td>
                    <td>{{ $m->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No activity yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Delete "' + name + '"?',
                text: 'This cannot be undone. This item will be permanently removed from inventory.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection
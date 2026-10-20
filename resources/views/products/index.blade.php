@extends('layouts.itrack')
@section('title', 'Products')

@section('content')
    <div class="page-head">
        <div>
            <h1>Products</h1>
            <p>Manage the items in your store.</p>
        </div>
        <div class="toolbar" style="margin:0">
            <a href="{{ route('admin.productsReact') }}" class="btn btn-outline"><i class="bi bi-braces"></i> React CRUD (API)</a>
            <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Product</a>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('products.index') }}" class="toolbar">
            <input type="text" name="search" class="form-control grow" value="{{ request('search') }}" placeholder="Search product name or barcode...">
            <select name="category" class="form-control" style="width:auto" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control" style="width:auto" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="in" @selected(request('status') === 'in')>In Stock</option>
                <option value="low" @selected(request('status') === 'low')>Low Stock</option>
                <option value="out" @selected(request('status') === 'out')>Out of Stock</option>
            </select>
            <button class="btn btn-outline"><i class="bi bi-search"></i> Search</button>
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th><th>Image</th><th>Product Name</th><th>Barcode</th><th>Category</th>
                        <th>Aisle</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $products->firstItem() + $loop->index }}</td>
                            <td>
                                @if ($product->image_url)
                                    <img class="thumb" src="{{ $product->image_url }}" alt="">
                                @else
                                    <span class="thumb"><i class="bi bi-image"></i></span>
                                @endif
                            </td>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td><code>{{ $product->barcode }}</code></td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>{{ $product->aisle ?: '-' }}</td>
                            <td>&#8369;{{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td><span class="badge badge-{{ $product->status_key }}">{{ $product->status }}</span></td>
                            <td>
                                <a href="{{ route('products.edit', $product) }}" class="icon-btn" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                <button type="button" class="icon-btn del" title="Delete"
                                        data-name="{{ $product->name }}"
                                        onclick="confirmDelete('del-{{ $product->id }}', this.dataset.name)">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <form id="del-{{ $product->id }}" method="POST" action="{{ route('products.destroy', $product) }}" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="empty"><i class="bi bi-box-seam"></i>No products found. Click "Add Product" and scan your first item.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links('partials.pagination') }}
    </div>
@endsection
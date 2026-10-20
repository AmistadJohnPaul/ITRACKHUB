@extends('layouts.itrack')
@section('title', 'React CRUD')

@section('content')
    <div class="page-head">
        <div>
            <h1>Product Manager (React + API)</h1>
            <p>This page talks to <code>/admin/api/products</code> with GET, POST, PUT and DELETE requests.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline">Back to Products</a>
    </div>

    <div class="card">
        <div id="product-crud-root"></div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/react-admin.jsx')
@endpush
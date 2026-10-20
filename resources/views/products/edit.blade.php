@extends('layouts.itrack')
@section('title', 'Edit Product')

@section('content')
    <div class="page-head">
        <div>
            <h1>Edit Product</h1>
            <p><a href="{{ route('products.index') }}">Products</a> / {{ $product->name }}</p>
        </div>
    </div>

    @include('products._form')
@endsection
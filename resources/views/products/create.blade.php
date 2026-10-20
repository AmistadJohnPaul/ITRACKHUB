@extends('layouts.itrack')
@section('title', 'Add Product')

@section('content')
    <div class="page-head">
        <div>
            <h1>Add Product</h1>
            <p><a href="{{ route('products.index') }}">Products</a> / Add Product</p>
        </div>
    </div>

    @include('products._form')
@endsection
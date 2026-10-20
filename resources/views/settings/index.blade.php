@extends('layouts.itrack')
@section('title', 'Settings')

@php
    $logo = \App\Models\Setting::get('store_logo');
@endphp

@section('content')
    <div class="page-head">
        <div><h1>Settings</h1><p>Store information, users, categories and barcode options.</p></div>
    </div>
    @include('partials.settings-tabs')

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="card">
        @csrf @method('PUT')
        <div class="form-split" style="grid-template-columns:1.6fr 1fr">
            <div>
                <label class="form-label" style="margin-top:0">Store Name</label>
                <input type="text" name="store_name" class="form-control" value="{{ old('store_name', \App\Models\Setting::get('store_name', 'ITrackHub Mini Store')) }}" required>
                @error('store_name') <div class="error-text">{{ $message }}</div> @enderror

                <label class="form-label">Owner Name</label>
                <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', \App\Models\Setting::get('owner_name', auth()->user()->name)) }}">

                <label class="form-label">Email</label>
                <input type="email" name="store_email" class="form-control" value="{{ old('store_email', \App\Models\Setting::get('store_email')) }}">
                @error('store_email') <div class="error-text">{{ $message }}</div> @enderror

                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', \App\Models\Setting::get('contact_number')) }}">
            </div>

            <div class="logo-box">
                <p class="form-label" style="margin-top:0">Store Logo</p>
                @if ($logo)
                    <img src="{{ asset('uploads/logo/' . $logo) }}" alt="Logo">
                @else
                    <div class="brand-icon big" style="background:var(--soft);color:var(--primary);margin:0 auto 10px"><i class="bi bi-upc-scan"></i></div>
                @endif
                <input type="file" name="logo" accept="image/*" class="form-control">
                @error('logo') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-actions"><button class="btn btn-primary"><i class="bi bi-check2"></i> Save Changes</button></div>
    </form>
@endsection
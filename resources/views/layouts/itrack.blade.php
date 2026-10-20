<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | ITrackHub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/itrack.css') }}?v={{ filemtime(public_path('css/itrack.css')) }}">
    @stack('styles')
</head>
<body>
@php
    $me = auth()->user();
    $needsRestock = $me->isAdmin() ? \App\Models\Product::needsRestock()->count() : 0;
@endphp
<div class="app">
    @include('partials.sidebar')

    <div class="main">
        <header class="topbar">
            <button class="menu-btn" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="bi bi-list"></i>
            </button>

            @if ($me->isAdmin())
                <form class="search" method="GET" action="{{ route('products.index') }}">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" placeholder="Search products, barcode..." value="{{ request('search') }}">
                </form>
            @endif

            <div class="top-right">
                @if ($me->isAdmin())
                    <a href="{{ route('inventory.index', ['status' => 'low']) }}" class="bell" title="Low / out of stock items">
                        <i class="bi bi-bell"></i>
                        @if ($needsRestock > 0)<span class="dot">{{ $needsRestock }}</span>@endif
                    </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="profile">
                    <span class="avatar">{{ strtoupper(substr($me->name, 0, 1)) }}</span>
                    <div>
                        <strong>{{ $me->name }}</strong>
                        <small>{{ ucfirst($me->role) }}</small>
                    </div>
                </a>
            </div>
        </header>

        <main class="page">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Shared "are you sure?" popup used by every delete button
    function confirmDelete(formId, name, extra) {
        Swal.fire({
            title: 'Delete "' + name + '"?',
            text: extra || 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7a72',
            confirmButtonText: 'Yes, delete it'
        }).then(function (r) {
            if (r.isConfirmed) document.getElementById(formId).submit();
        });
    }
    function toast(icon, title) {
        Swal.fire({toast: true, position: 'top-end', icon: icon, title: title, showConfirmButton: false, timer: 2800});
    }
    @if (session('success')) toast('success', @json(session('success'))); @endif
    @if (session('error')) toast('error', @json(session('error'))); @endif
</script>
@stack('scripts')
</body>
</html>
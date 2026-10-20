<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ITrackHub - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-3">
        <a class="navbar-brand" href="{{ route('products.index') }}">📦 ITrackHub</a>
        <div>
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-light me-2">Admin Dashboard</a>
                @endif
                @if (auth()->user()->isAdmin() || auth()->user()->isStaff())
                    <a href="{{ route('scan.index') }}" class="btn btn-sm btn-warning me-2">📱 Scan</a>
                    <a href="{{ route('scan.bulkRestock') }}" class="btn btn-sm btn-outline-light me-2">🚚 Bulk Restock</a>
                @endif
                <a href="{{ route('lookup.index') }}" class="btn btn-sm btn-outline-light me-2">🏷️ Price Check</a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-danger">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Login</a>
            @endauth
        </div>
    </nav>

    <div class="container mt-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
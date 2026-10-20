<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | ITrackHub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/itrack.css') }}?v={{ filemtime(public_path('css/itrack.css')) }}">
</head>
<body class="auth-body">
<div class="auth">
    <div class="auth-side">
        <div class="auth-brand">
            <span class="brand-icon big"><i class="bi bi-upc-scan"></i></span>
            <h1>ITrackHub</h1>
            <p>Barcode Scanner &middot; Inventory Monitoring</p>
        </div>
        <ul class="auth-points">
            <li><i class="bi bi-check2-circle"></i> Scan a barcode to check price and aisle</li>
            <li><i class="bi bi-check2-circle"></i> Sales and restocks update stock automatically</li>
            <li><i class="bi bi-check2-circle"></i> Low-stock alerts, reports and sales history</li>
        </ul>
    </div>

    <div class="auth-form">
        <form method="POST" action="{{ route('login') }}" class="auth-card">
            @csrf
            <h2>Welcome back</h2>
            <p class="muted">Sign in with your administrator account.</p>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username">

            <label class="form-label" for="password">Password</label>
            <div class="pw">
                <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
                <button type="button" onclick="var p=document.getElementById('password');p.type=p.type==='password'?'text':'password'"><i class="bi bi-eye"></i></button>
            </div>

            <label class="check"><input type="checkbox" name="remember"> Remember me</label>

            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </form>
    </div>
</div>
</body>
</html>
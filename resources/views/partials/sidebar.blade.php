@php $u = auth()->user(); @endphp
<aside class="sidebar" id="sidebar">
    <a href="{{ route('dashboard') }}" class="brand">
        @if (\App\Models\Setting::get('store_logo'))
            <img class="brand-logo" src="{{ asset('uploads/logo/' . \App\Models\Setting::get('store_logo')) }}" alt="Logo">
        @else
            <span class="brand-icon"><i class="bi bi-upc-scan"></i></span>
        @endif
        <span class="brand-text">
            <strong>ITrackHub</strong>
            <small>Barcode Scanner</small>
        </span>
    </a>

    <nav class="nav">
        @if ($u->isAdmin())
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        @endif

        @if ($u->isAdmin() || $u->isStaff())
            <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}"><i class="bi bi-upc-scan"></i> Scan / POS</a>
        @endif

        @if ($u->isAdmin())
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') || request()->routeIs('admin.productsReact') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> Products</a>
            <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}"><i class="bi bi-clipboard-data"></i> Inventory</a>
            <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}"><i class="bi bi-receipt"></i> Sales</a>
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="bi bi-graph-up"></i> Reports</a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i> Categories</a>
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Users</a>
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="bi bi-gear"></i> Settings</a>
        @endif
    </nav>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="nav-link logout"><i class="bi bi-box-arrow-left"></i> Logout</button>
    </form>
</aside>
<div class="tabs">
    <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.index') ? 'active' : '' }}"><i class="bi bi-shop"></i> Store Information</a>
    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Users</a>
    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i> Categories</a>
    <a href="{{ route('settings.barcode') }}" class="{{ request()->routeIs('settings.barcode*') ? 'active' : '' }}"><i class="bi bi-upc"></i> Barcode Settings</a>
</div>
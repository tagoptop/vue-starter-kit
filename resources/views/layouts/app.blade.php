<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Supply Management</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">CSMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                    <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('suppliers.index') }}">Suppliers</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('inventory.index') }}">Inventory</a></li>
                @endif
                <li class="nav-item"><a class="nav-link" href="{{ route('orders.index') }}">Orders</a></li>
                @if(auth()->check() && auth()->user()->role === 'customer')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('orders.create') }}">
                            Order Now
                            @if(count(session('cart', [])) > 0)
                                <span class="badge bg-warning text-dark">{{ collect(session('cart', []))->sum('quantity') }}</span>
                            @endif
                        </a>
                    </li>
                @endif
                @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                    <li class="nav-item"><a class="nav-link" href="{{ route('reports.index') }}">Reports</a></li>
                @endif
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Users</a></li>
                @endif
            </ul>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white small">{{ auth()->user()->name ?? '' }} ({{ auth()->user()->role ?? '' }})</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

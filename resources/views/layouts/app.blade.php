<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAR - {{ config('app.name', 'Restaurante U') }}</title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; font-size: 1.4rem; }
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #343a40; border-radius: 5px; }
        .sidebar .nav-link i { margin-right: 8px; }
        .main-content { padding: 20px; }
    </style>
</head>
<body>

<!-- Navbar superior -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-shop"></i> SIAR
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-white">
                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                @if(Auth::user()->rol)
                    <span class="badge bg-secondary ms-1">{{ ucfirst(Auth::user()->rol->name) }}</span>
                @endif
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <nav class="col-md-2 sidebar pt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                @can('ver usuarios')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}"
                       href="{{ route('usuarios.index') }}">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                </li>
                @endcan

                @can('ver mesas')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('mesas.*') ? 'active' : '' }}"
                       href="{{ route('mesas.index') }}">
                        <i class="bi bi-grid-3x3"></i> Mesas
                    </a>
                </li>
                @endcan

                @can('ver productos')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}"
                       href="{{ route('categorias.index') }}">
                        <i class="bi bi-tags"></i> Categorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}"
                       href="{{ route('productos.index') }}">
                        <i class="bi bi-cup-hot"></i> Menú / Productos
                    </a>
                </li>
                @endcan

                @can('ver reservas')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reservas.*') ? 'active' : '' }}"
                       href="{{ route('reservas.index') }}">
                        <i class="bi bi-calendar-check"></i> Reservas
                    </a>
                </li>
                @endcan

                @can('ver pedidos')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}"
                       href="{{ route('pedidos.index') }}">
                        <i class="bi bi-receipt"></i> Pedidos
                    </a>
                </li>
                @endcan

                @can('preparar pedidos')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('cocina.*') ? 'active' : '' }}"
                       href="{{ route('cocina.index') }}">
                        <i class="bi bi-fire"></i> Cocina
                    </a>
                </li>
                @endcan

                @can('ver facturas')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('facturas.*') ? 'active' : '' }}"
                       href="{{ route('facturas.index') }}">
                        <i class="bi bi-file-earmark-text"></i> Facturas
                    </a>
                </li>
                @endcan

                @can('ver auditoria')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('auditorias.*') ? 'active' : '' }}"
                       href="{{ route('auditorias.index') }}">
                        <i class="bi bi-shield-check"></i> Auditoría
                    </a>
                </li>
                @endcan

                <li class="nav-item mt-3 border-top border-secondary pt-2">
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                       href="{{ route('profile.edit') }}">
                        <i class="bi bi-person-gear"></i> Mi perfil
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <main class="col-md-10 main-content">
            {{-- Alertas de éxito / error --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>

    </div>
</div>

<!-- Bootstrap JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmH1kGS2V3Q0j/N3kRoVUHFiMUU"
        crossorigin="anonymous"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quiniela Mundial 2026 - Pronósticos del Mundial')</title>
    <meta name="description" content="Participa en la mejor Quiniela del Mundial de la FIFA 2026. Predice resultados, compite con amigos, acumula puntos y lidera la tabla general.">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
</head>
<body>

    <!-- Sticky Navbar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="{{ route('landing') }}" class="brand">
                🏆 <span>Quiniela</span>2026
            </a>
            
            @auth
            <ul class="nav-links">
                <li>
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        🎯 Pronósticos
                    </a>
                </li>
                @if(auth()->user()->isAdmin())
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        ⚙️ Administrador
                    </a>
                </li>
                @endif
            </ul>

            <div class="user-info" style="display: flex; align-items: center; gap: 0.75rem;">
                @if(auth()->user()->profile_picture)
                    <img src="{{ asset(auth()->user()->profile_picture) }}" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                @else
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255, 255, 255, 0.08); display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 700; color: white; border: 2px solid var(--border-glass);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                @endif
                <span class="user-name" style="font-weight: 600;">{{ auth()->user()->name }}</span>
                <span class="points-badge">{{ auth()->user()->points }} pts</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.35rem 0.75rem; border-radius: 30px;">
                        Salir
                    </button>
                </form>
            </div>
            @else
            <div class="nav-auth-buttons">
                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm" style="margin-right: 0.5rem;">Entrar</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Registrarse</a>
            </div>
            @endauth
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container" style="padding-top: 2rem; min-height: 70vh;">
        <!-- Error & Success Notifications -->
        @if(session('success'))
            <div class="alert alert-success">
                <span>✅ {{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <span>⚠️ {{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} Quiniela Mundial 2026. Todos los derechos reservados. Desarrollado con ⚽ para fanáticos del fútbol.</p>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>

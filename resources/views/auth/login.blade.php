@extends('layouts.app')

@section('title', 'Iniciar Sesión - Quiniela Mundial 2026')

@section('content')
<div class="auth-wrapper">
    <div class="glass-card auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Iniciar Sesión</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Ingresa para realizar tus pronósticos y ver tus puntos</p>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="ejemplo@correo.com" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <span style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                @error('password')
                    <span style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: -0.5rem; margin-bottom: 1.5rem;">
                <input type="checkbox" name="remember" id="remember" style="cursor: pointer; width: 16px; height: 16px;">
                <label for="remember" style="font-size: 0.9rem; color: var(--text-muted); cursor: pointer; user-select: none;">Recordarme en este equipo</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.05rem; padding: 0.9rem;">
                Ingresar a la Quiniela
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; border-top: 1px solid var(--border-glass); padding-top: 1.5rem;">
            <p style="font-size: 0.9rem; color: var(--text-muted);">
                ¿Aún no tienes cuenta? 
                <a href="{{ route('register') }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">Regístrate aquí</a>
            </p>
        </div>
    </div>
</div>
@endsection

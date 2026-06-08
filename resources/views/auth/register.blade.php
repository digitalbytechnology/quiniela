@extends('layouts.app')

@section('title', 'Registrarse - Quiniela Mundial 2026')

@section('content')
<div class="auth-wrapper">
    <div class="glass-card auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Crear Cuenta</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Regístrate para comenzar a pronosticar los marcadores</p>
        </div>

        <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Nombre Completo</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Juan Pérez" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <span style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="ejemplo@correo.com" value="{{ old('email') }}" required>
                @error('email')
                    <span style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="profile_picture" class="form-label">Foto de Perfil</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control" accept="image/*" required style="padding: 0.6rem 1rem;">
                @error('profile_picture')
                    <span style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                @error('password')
                    <span style="color: var(--danger); font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.05rem; padding: 0.9rem; margin-top: 0.5rem;">
                Registrarse y Entrar
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; border-top: 1px solid var(--border-glass); padding-top: 1.5rem;">
            <p style="font-size: 0.9rem; color: var(--text-muted);">
                ¿Ya tienes una cuenta? 
                <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">Inicia sesión aquí</a>
            </p>
        </div>
    </div>
</div>
@endsection

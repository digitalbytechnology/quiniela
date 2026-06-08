@extends('layouts.app')

@section('title', 'Quiniela Mundial 2026 - Pronósticos y Competición')

@section('content')
<div class="hero">
    <div class="hero-tag">MUNDIAL DE LA FIFA 2026 🇨🇦 🇲🇽 🇺🇸</div>
    <h1 class="hero-title">Demuestra tus conocimientos futbolísticos</h1>
    <p class="hero-desc">
        Únete a la Quiniela definitiva del Mundial 2026. Predice los marcadores de los 48 equipos participantes, acumula puntos y compite contra otros fanáticos del fútbol para llegar al primer puesto de la tabla.
    </p>
    <div style="display: flex; justify-content: center; gap: 1rem;">
        <a href="{{ route('register') }}" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem; border-radius: 14px;">
            Crear mi Cuenta Gratis
        </a>
        <a href="{{ route('login') }}" class="btn btn-secondary" style="font-size: 1.1rem; padding: 1rem 2rem; border-radius: 14px;">
            Iniciar Sesión
        </a>
    </div>
</div>

<div class="glass-card" style="margin-top: 2rem;">
    <h2 style="font-size: 1.8rem; font-weight: 800; text-align: center; margin-bottom: 2.5rem; background: linear-gradient(135deg, white, var(--text-muted)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        ¿Cómo funciona la Quiniela?
    </h2>
    <div class="rules-list">
        <div class="rules-item">
            <div class="rule-number">1</div>
            <div class="rule-content">
                <h4>Regístrate</h4>
                <p>Crea tu cuenta de participante con tu correo y contraseña en menos de 1 minuto.</p>
            </div>
        </div>
        <div class="rules-item">
            <div class="rule-number">2</div>
            <div class="rule-content">
                <h4>Ingresa tus Marcadores</h4>
                <p>Pronostica los goles locales y visitantes para cada partido antes de que comiencen oficialmente. Tienes hasta el pitazo inicial.</p>
            </div>
        </div>
        <div class="rules-item">
            <div class="rule-number">3</div>
            <div class="rule-content">
                <h4>Gana Puntos</h4>
                <p>Suma <strong>3 puntos</strong> si aciertas el marcador exacto de un partido, o suma <strong>1 punto</strong> si aciertas únicamente el ganador o empate pero no el resultado exacto.</p>
            </div>
        </div>
        <div class="rules-item">
            <div class="rule-number">4</div>
            <div class="rule-content">
                <h4>Lidera la Tabla</h4>
                <p>Consulta la tabla de clasificación en tiempo real para ver tu ranking y competir contra todos los demás miembros.</p>
            </div>
        </div>
    </div>
</div>
@endsection

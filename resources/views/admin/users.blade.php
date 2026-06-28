@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Admin')

@section('content')
<div style="margin-bottom: 2.5rem;">
    <h1 style="font-size: 2.25rem; font-weight: 800; background: linear-gradient(135deg, white, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Gestión de Participantes
    </h1>
    <p style="color: var(--text-muted); font-size: 1rem; margin-top: 0.25rem;">
        Administra los puntos extra y el equipo campeón seleccionado por los participantes.
    </p>
</div>

@if(session('success'))
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #10b981; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #ef4444; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        ❌ {{ session('error') }}
    </div>
@endif

<div class="glass-card">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-glass); text-align: left;">
                    <th style="padding: 1rem; color: var(--text-muted);">Participante</th>
                    <th style="padding: 1rem; color: var(--text-muted); text-align: center;">Pts Predicción</th>
                    <th style="padding: 1rem; color: var(--text-muted); text-align: center;">Pts Campeón</th>
                    <th style="padding: 1rem; color: var(--text-muted); text-align: center;">Pts Extra</th>
                    <th style="padding: 1rem; color: var(--text-muted); text-align: center;">Total</th>
                    <th style="padding: 1rem; color: var(--text-muted);">Campeón Seleccionado</th>
                    <th style="padding: 1rem; color: var(--text-muted); text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    @php
                        $predPoints = \App\Models\Prediction::where('user_id', $user->id)->whereNotNull('points_earned')->sum('points_earned');
                        $championPoints = $user->champion_points_awarded ? 20 : 0;
                    @endphp
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                @if($user->profile_picture)
                                    <img src="{{ asset($user->profile_picture) }}" alt="" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--primary);">
                                @else
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255, 255, 255, 0.08); display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: white;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight: 600;">{{ $user->name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1rem; text-align: center; font-weight: 600;">{{ $predPoints }}</td>
                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #10b981;">{{ $championPoints }}</td>
                        
                        <td style="padding: 1rem; text-align: center;">
                            <input type="number" id="input_extra_{{ $user->id }}" value="{{ $user->extra_points }}" 
                                style="width: 70px; background: #1a1535; border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.5rem; color: white; text-align: center;">
                        </td>
                        <td style="padding: 1rem; text-align: center; font-weight: 800; color: var(--primary); font-size: 1.1rem;">
                            {{ $user->points }}
                        </td>
                        <td style="padding: 1rem;">
                            <select id="select_champ_{{ $user->id }}" style="width: 100%; max-width: 200px; background: #1a1535; border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.5rem; color: white; font-size: 0.85rem;">
                                <option value="">-- Sin selección --</option>
                                @foreach($realTeams as $team)
                                    <option value="{{ $team->id }}" {{ $user->champion_pick_team_id == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; flex-wrap: wrap;">
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="form_update_{{ $user->id }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="extra_points" id="hidden_extra_{{ $user->id }}">
                                    <input type="hidden" name="champion_pick_team_id" id="hidden_champ_{{ $user->id }}">
                                    <button type="button" onclick="
                                        document.getElementById('hidden_extra_{{ $user->id }}').value = document.getElementById('input_extra_{{ $user->id }}').value;
                                        document.getElementById('hidden_champ_{{ $user->id }}').value = document.getElementById('select_champ_{{ $user->id }}').value;
                                        document.getElementById('form_update_{{ $user->id }}').submit();
                                    " class="btn btn-primary btn-sm" style="padding: 0.4rem 0.8rem; border-radius: 8px;">
                                        💾 Guardar
                                    </button>
                                </form>

                                <button type="button" onclick="togglePasswordForm({{ $user->id }})" class="btn btn-sm" style="background: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.4); padding: 0.4rem 0.8rem; border-radius: 8px; cursor: pointer;">
                                    🔑 Contraseña
                                </button>

                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este usuario de forma permanente? Se borrarán todos sus pronósticos y puntos.');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); padding: 0.4rem 0.8rem; border-radius: 8px; cursor: pointer;">
                                        🗑️ Eliminar
                                    </button>
                                </form>
                            </div>

                            {{-- Formulario inline para cambiar contraseña --}}
                            <div id="password_form_{{ $user->id }}" style="display: none; margin-top: 0.75rem;">
                                <form action="{{ route('admin.users.reset_password', $user->id) }}" method="POST" style="display: flex; gap: 0.5rem; align-items: center; justify-content: flex-end;">
                                    @csrf
                                    <input type="text" name="new_password" placeholder="Nueva contraseña" required minlength="4"
                                        style="width: 160px; background: #1a1535; border: 1px solid rgba(251, 191, 36, 0.4); border-radius: 8px; padding: 0.45rem 0.6rem; color: white; font-size: 0.85rem;">
                                    <button type="submit" class="btn btn-sm" style="background: rgba(251, 191, 36, 0.3); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.5); padding: 0.4rem 0.7rem; border-radius: 8px; cursor: pointer; white-space: nowrap;">
                                        ✅ Cambiar
                                    </button>
                                    <button type="button" onclick="togglePasswordForm({{ $user->id }})" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: var(--text-muted); border: 1px solid var(--border-glass); padding: 0.4rem 0.7rem; border-radius: 8px; cursor: pointer;">
                                        ✕
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function togglePasswordForm(userId) {
    const form = document.getElementById('password_form_' + userId);
    if (form.style.display === 'none') {
        // Close all other open password forms first
        document.querySelectorAll('[id^="password_form_"]').forEach(el => {
            el.style.display = 'none';
        });
        form.style.display = 'block';
        form.querySelector('input[name="new_password"]').focus();
    } else {
        form.style.display = 'none';
    }
}
</script>
@endsection

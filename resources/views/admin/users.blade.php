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
                        $championPoints = $user->champion_points_awarded ? 50 : 0;
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
                        
                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                            @csrf
                            <td style="padding: 1rem; text-align: center;">
                                <input type="number" name="extra_points" value="{{ $user->extra_points }}" 
                                    style="width: 70px; background: #1a1535; border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.5rem; color: white; text-align: center;">
                            </td>
                            <td style="padding: 1rem; text-align: center; font-weight: 800; color: var(--primary); font-size: 1.1rem;">
                                {{ $user->points }}
                            </td>
                            <td style="padding: 1rem;">
                                <select name="champion_pick_team_id" style="width: 100%; max-width: 200px; background: #1a1535; border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.5rem; color: white; font-size: 0.85rem;">
                                    <option value="">-- Sin selección --</option>
                                    @foreach($realTeams as $team)
                                        <option value="{{ $team->id }}" {{ $user->champion_pick_team_id == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.4rem 0.8rem; border-radius: 8px;">
                                    💾 Guardar
                                </button>
                            </td>
                        </form>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

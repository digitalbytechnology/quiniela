@extends('layouts.app')

@section('title', 'Mi Quiniela - Pronósticos y Tabla General')

@section('content')
<div style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 2.25rem; font-weight: 800; background: linear-gradient(135deg, white, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Quiniela Qatar a Norteamérica 2026
        </h1>
        <p style="color: var(--text-muted); font-size: 1rem; margin-top: 0.25rem;">
            Ingresa tus pronósticos, suma puntos y sube en la clasificación.
        </p>
    </div>
    <div class="points-summary" style="display: flex; gap: 1.5rem;">
        <div class="glass-card" style="padding: 1rem 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 120px; border-radius: 14px;">
            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Mis Puntos</span>
            <span style="font-size: 2rem; font-weight: 800; color: var(--secondary);">{{ auth()->user()->points }}</span>
        </div>
        <div class="glass-card" style="padding: 1rem 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 120px; border-radius: 14px;">
            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Mi Ranking</span>
            @php
                $rank = 1;
                foreach($leaderboard as $idx => $lUser) {
                    if($lUser->id === auth()->id()) {
                        $rank = $idx + 1;
                        break;
                    }
                }
            @endphp
            <span style="font-size: 2rem; font-weight: 800; color: var(--primary);">#{{ $rank }}</span>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="tabs-header">
    <button class="tab-btn active" onclick="switchTab(event, 'tab-partidos')">⚽ Mis Pronósticos</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-campeon')">🏆 Campeón del Mundial</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-reglas')">📜 Reglas de Puntos</button>
</div>

<div class="dashboard-grid" style="display: flex; gap: 2rem; align-items: flex-start; margin-top: 2rem; flex-wrap: wrap;">
    <!-- Main Content Column -->
    <div style="flex: 2.5; min-width: 300px; width: 100%;">
        <!-- Tab: Partidos -->
        <div id="tab-partidos" class="tab-content active">
            @if($groupedGames->isEmpty())
                <div class="glass-card" style="text-align: center; padding: 4rem 2rem;">
                    <span style="font-size: 3rem;">📅</span>
                    <h3 style="margin-top: 1rem; font-size: 1.5rem;">No hay partidos programados</h3>
                    <p style="color: var(--text-muted); margin-top: 0.5rem;">Los partidos del torneo aparecerán aquí pronto.</p>
                </div>
            @else
                <form action="{{ route('predictions.save') }}" method="POST">
                    @csrf
                    
                    @php $inputIndex = 0; @endphp
                    @foreach($groupedGames as $stageName => $gamesList)
                        <div class="stage-section">
                            <h3 class="stage-title">{{ $stageName }}</h3>
                            <div class="games-grid">
                                @foreach($gamesList as $game)
                                    @php
                                        $prediction = $predictions->get($game->id);
                                        $isStageUnlocked = in_array($game->stage, $unlockedStages);
                                        $hasStarted = $game->match_date->isBefore(now());
                                        $isFinished = $game->status === 'finished';
                                        $canPredict = $isStageUnlocked && !$hasStarted && !$isFinished;

                                        $homeTeam = $game->homeTeam->getRealTeam();
                                        $awayTeam = $game->awayTeam->getRealTeam();
                                        $isHomePlaceholder = ($homeTeam->group === 'TBD');
                                        $isAwayPlaceholder = ($awayTeam->group === 'TBD');
                                    @endphp
                                    
                                    <div class="game-card" @if(!$isStageUnlocked) style="opacity: 0.65; filter: grayscale(30%);" @endif>
                                        <!-- Hidden Fields for Array Submit -->
                                        @if($canPredict)
                                            <input type="hidden" name="predictions[{{ $inputIndex }}][game_id]" value="{{ $game->id }}">
                                        @endif
                                        
                                        <div class="game-header">
                                            <div class="game-date" style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.15rem;">
                                                <span>📅 Partido: {{ $game->match_date->format('d/m/Y H:i') }}</span>
                                                <span style="font-size: 0.75rem; color: var(--secondary); font-weight: 700;">
                                                    ⏰ Límite: {{ $game->match_date->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                            <div>
                                                @if($isFinished)
                                                    <span class="game-status status-finished">Finalizado</span>
                                                @elseif(!$isStageUnlocked)
                                                    <span class="game-status" style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted);">Bloqueado</span>
                                                @elseif($hasStarted)
                                                    <span class="game-status status-finished" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">En Juego</span>
                                                @else
                                                    <span class="game-status status-pending">Abierto</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="game-teams">
                                            <!-- Home Team -->
                                            <div class="team-info">
                                                @if($isHomePlaceholder)
                                                    <div class="team-flag-placeholder" style="width: 45px; height: 30px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.05); border: 1px dashed var(--border-glass); border-radius: 4px; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);">
                                                        ⚽
                                                    </div>
                                                @else
                                                    <img src="https://flagcdn.com/w80/{{ $homeTeam->code }}.png" alt="Bandera {{ $homeTeam->name }}" class="team-flag">
                                                @endif
                                                <span class="team-name" title="{{ $homeTeam->name }}">{{ $homeTeam->name }}</span>
                                            </div>

                                            <!-- Inputs / VS / Result -->
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                                <div class="prediction-inputs">
                                                    @if(!$canPredict)
                                                        <!-- Game not open for prediction -->
                                                        <input type="number" 
                                                               value="{{ $prediction ? $prediction->home_score : '' }}" 
                                                               class="score-input" 
                                                               disabled 
                                                               placeholder="-">
                                                        <span class="vs-divider">VS</span>
                                                        <input type="number" 
                                                               value="{{ $prediction ? $prediction->away_score : '' }}" 
                                                               class="score-input" 
                                                               disabled 
                                                               placeholder="-">
                                                    @else
                                                        <!-- Game open for prediction -->
                                                        <input type="number" 
                                                               name="predictions[{{ $inputIndex }}][home_score]" 
                                                               value="{{ $prediction ? $prediction->home_score : '' }}" 
                                                               class="score-input" 
                                                               min="0" 
                                                               required
                                                               placeholder="-">
                                                        <span class="vs-divider">VS</span>
                                                        <input type="number" 
                                                               name="predictions[{{ $inputIndex }}][away_score]" 
                                                               value="{{ $prediction ? $prediction->away_score : '' }}" 
                                                               class="score-input" 
                                                               min="0" 
                                                               required
                                                               placeholder="-">
                                                    @endif
                                                </div>

                                                @if($isFinished)
                                                    <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 700; margin-top: 0.2rem;">
                                                        Resultado Real: <span style="color: white;">{{ $game->home_score }} - {{ $game->away_score }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Away Team -->
                                            <div class="team-info">
                                                @if($isAwayPlaceholder)
                                                    <div class="team-flag-placeholder" style="width: 45px; height: 30px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.05); border: 1px dashed var(--border-glass); border-radius: 4px; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);">
                                                        ⚽
                                                    </div>
                                                @else
                                                    <img src="https://flagcdn.com/w80/{{ $awayTeam->code }}.png" alt="Bandera {{ $awayTeam->name }}" class="team-flag">
                                                @endif
                                                <span class="team-name" title="{{ $awayTeam->name }}">{{ $awayTeam->name }}</span>
                                            </div>
                                        </div>

                                        <!-- Feedback on points earned -->
                                        @if($isFinished && $prediction)
                                            @if($prediction->points_earned == 3)
                                                <div class="match-points-feedback points-earned-exact">
                                                    ⭐ +3 Puntos (Marcador Exacto)
                                                </div>
                                            @elseif($prediction->points_earned == 1)
                                                <div class="match-points-feedback points-earned-outcome">
                                                    ✔️ +1 Punto (Ganador/Empate Acertado)
                                                </div>
                                            @else
                                                <div class="match-points-feedback points-earned-none">
                                                    ❌ 0 Puntos (Resultado Incorrecto)
                                                </div>
                                            @endif
                                        @elseif($isFinished && !$prediction)
                                            <div class="match-points-feedback points-earned-none">
                                                🚫 0 Puntos (No registraste pronóstico)
                                            </div>
                                        @elseif(!$isStageUnlocked)
                                            <div class="match-points-feedback" style="color: var(--text-muted); background: rgba(255, 255, 255, 0.02); border: 1px dashed var(--border-glass);">
                                                🔒 Fase Bloqueada (Completa la fase anterior)
                                            </div>
                                        @elseif($hasStarted && $prediction)
                                            <div class="match-points-feedback" style="color: var(--warning); background: rgba(245, 158, 11, 0.05);">
                                                🔒 Pronóstico Cerrado
                                            </div>
                                        @elseif($hasStarted && !$prediction)
                                            <div class="match-points-feedback" style="color: var(--danger); background: rgba(239, 68, 68, 0.05);">
                                                🔒 Cerrado (Sin Pronóstico)
                                            </div>
                                        @elseif(!$hasStarted && $prediction)
                                            <div class="match-points-feedback" style="color: var(--accent); background: rgba(16, 185, 129, 0.05);">
                                                ✍️ Modificar Pronóstico
                                            </div>
                                        @elseif(!$hasStarted && !$prediction)
                                            <div class="match-points-feedback" style="color: var(--primary); background: rgba(99, 102, 241, 0.05);">
                                                📝 Pendiente de Pronóstico
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($canPredict)
                                        @php $inputIndex++; @endphp
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <!-- Floating Save Bar -->
                    @if($inputIndex > 0)
                        <div style="position: sticky; bottom: 20px; z-index: 10; display: flex; justify-content: center; margin-top: 3rem;">
                            <button type="submit" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 3rem; border-radius: 50px; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.5);">
                                💾 Guardar mis Pronósticos
                            </button>
                        </div>
                    @endif
                </form>
            @endif
        </div>

        <!-- Tab: Campeón del Mundial -->
        <div id="tab-campeon" class="tab-content">
            <div class="glass-card" style="max-width: 680px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <span style="font-size: 4rem;">🏆</span>
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin-top: 0.75rem; background: linear-gradient(135deg, #facc15, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        ¿Quién ganará el Mundial 2026?
                    </h2>
                    <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 0.95rem;">
                        Selecciona al campeón antes del inicio del torneo (11 jun · 17:00).
                        <br>Si aciertas, <strong style="color: #facc15;">ganas 50 puntos</strong>.
                    </p>
                </div>

                @if($worldChampion)
                    {{-- Champion already declared --}}
                    <div style="text-align: center; padding: 1.5rem; background: rgba(250,204,21,0.08); border: 1px solid rgba(250,204,21,0.3); border-radius: 16px; margin-bottom: 2rem;">
                        <p style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Campeón Oficial Declarado</p>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-top: 0.75rem;">
                            <img src="https://flagcdn.com/w80/{{ $worldChampion->code }}.png" style="width: 56px; height: 37px; object-fit: cover; border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.4);">
                            <span style="font-size: 1.75rem; font-weight: 800; color: #facc15;">{{ $worldChampion->name }}</span>
                        </div>
                    </div>
                @endif

                @if($userChampionPick)
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem; background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.25); border-radius: 12px; margin-bottom: 1.5rem;">
                        <img src="https://flagcdn.com/w80/{{ $userChampionPick->code }}.png" style="width: 44px; height: 29px; object-fit: cover; border-radius: 3px;">
                        <div>
                            <p style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Tu selección actual</p>
                            <p style="font-weight: 700; font-size: 1.1rem;">{{ $userChampionPick->name }}</p>
                        </div>
                        @if($worldChampion)
                            @if($worldChampionId == $userChampionPick->id)
                                <span style="margin-left: auto; background: rgba(16,185,129,0.15); color: #10b981; padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">✅ +50 pts</span>
                            @else
                                <span style="margin-left: auto; background: rgba(239,68,68,0.1); color: var(--danger); padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">❌ 0 pts</span>
                            @endif
                        @endif
                    </div>
                @endif

                @if(!$championPickClosed && !$worldChampion)
                    <form action="{{ route('champion.pick') }}" method="POST">
                        @csrf
                        <div style="margin-bottom: 1.25rem;">
                            <label style="display: block; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; margin-bottom: 0.6rem; text-transform: uppercase; letter-spacing: 0.04em;">Selecciona un equipo</label>
                            <select name="champion_team_id" id="champion_team_id" required
                                style="width: 100%; background: #1a1535; border: 1px solid var(--border-glass); border-radius: 10px; padding: 0.85rem 1rem; color: white; font-size: 1rem; outline: none; cursor: pointer;">
                                <option value="" disabled selected style="background: #1a1535; color: #94a3b8;">-- Elige al Campeón --</option>
                                @foreach($realTeams->groupBy('group') as $grp => $groupTeams)
                                    <optgroup label="Grupo {{ $grp }}" style="background: #1a1535; color: #94a3b8; font-weight: 700;">
                                        @foreach($groupTeams as $team)
                                            <option value="{{ $team->id }}" style="background: #1e1b3a; color: white;" {{ $userChampionPick && $userChampionPick->id == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.05rem; padding: 0.9rem; border-radius: 12px;">
                            🏆 {{ $userChampionPick ? 'Cambiar mi selección de Campeón' : 'Guardar mi selección de Campeón' }}
                        </button>
                    </form>
                @elseif($championPickClosed && !$worldChampion)
                    <div style="text-align: center; padding: 1.5rem; background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2); border-radius: 12px;">
                        <p style="color: var(--danger); font-weight: 700;">🔒 Selección cerrada — el torneo ya comenzó</p>
                        @if(!$userChampionPick)
                            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">No registraste ninguna selección de campeón.</p>
                        @endif
                    </div>
                @elseif($worldChampion)
                    <div style="text-align: center; padding: 1.25rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border-glass); border-radius: 12px;">
                        <p style="color: var(--text-muted); font-size: 0.9rem;">El campeón ya fue declarado. No es posible modificar la selección.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tab: Reglas -->
        <div id="tab-reglas" class="tab-content">
            <div class="glass-card">
                <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Criterios de Puntuación</h3>

                <div class="rules-list">

                    <div class="rules-item">
                        <div class="rule-number" style="background: linear-gradient(135deg, #facc15, #f59e0b); box-shadow: 0 4px 10px rgba(250,204,21,0.35); font-size: 1rem;">+50</div>
                        <div class="rule-content">
                            <h4>🏆 Campeón del Mundial (50 Puntos)</h4>
                            <p>Acertar el equipo que se coronará Campeón del Mundo FIFA 2026. La selección se registra <strong>antes del inicio del torneo</strong> (11 jun · 17:00 h). Solo se puede elegir un equipo por participante. Si aciertas, recibes automáticamente <strong>+50 puntos</strong>.</p>
                        </div>
                    </div>

                    <div class="rules-item">
                        <div class="rule-number" style="background: linear-gradient(135deg, var(--accent), #059669); box-shadow: 0 4px 10px var(--accent-glow);">+3</div>
                        <div class="rule-content">
                            <h4>Resultado Exacto (3 Puntos)</h4>
                            <p>Acertar el marcador de goles exacto de ambos equipos. Ejemplo: Pronóstico 2 - 1, Resultado Real 2 - 1.</p>
                        </div>
                    </div>

                    <div class="rules-item">
                        <div class="rule-number" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">+1</div>
                        <div class="rule-content">
                            <h4>Resultado General (1 Punto)</h4>
                            <p>Acertar qué equipo gana o si hay empate, pero sin acertar la cantidad de goles exacta. Ejemplo: Pronóstico 2 - 0, Resultado Real 3 - 1 (acertaste la victoria local).</p>
                        </div>
                    </div>

                    <div class="rules-item">
                        <div class="rule-number" style="background: linear-gradient(135deg, var(--danger), #b91c1c); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);">0</div>
                        <div class="rule-content">
                            <h4>Sin Acierto (0 Puntos)</h4>
                            <p>No acertar el ganador, perdedor o empate del partido. Ejemplo: Pronóstico 1 - 2, Resultado Real 1 - 0 (predijiste victoria visitante y ganó el local).</p>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                    <h4 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">⚠️ Reglas Adicionales de la Quiniela:</h4>
                    <ul style="color: var(--text-muted); padding-left: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.95rem;">
                        <li>La selección del Campeón del Mundial debe registrarse <strong>antes del inicio del torneo</strong> (11 jun · 17:00 h). No se podrá modificar después.</li>
                        <li>Los pronósticos de partidos se cierran individualmente al momento de iniciar el encuentro, de acuerdo a la hora oficial programada en el sistema.</li>
                        <li>Si un pronóstico queda incompleto (vacío), no se computarán puntos para ese partido.</li>
                        <li>En fases eliminatorias directas (R32 en adelante), el resultado que se toma en cuenta es el del tiempo reglamentario completo (90 min + descuento). No incluye prórroga ni penales.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Clasificación General (Always Visible) -->
    <div style="flex: 1.2; min-width: 320px; width: 100%;">
        <div class="glass-card" style="padding: 1.5rem 0; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 1.25rem; padding: 0 1.25rem; background: linear-gradient(135deg, white, var(--text-muted)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                📊 Clasificación General
            </h3>
            <div class="leaderboard-table-container">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th class="rank-col" style="padding: 0.75rem 1rem;">Pos</th>
                            <th style="padding: 0.75rem 1rem;">Participante</th>
                            <th style="text-align: right; padding: 0.75rem 1rem;">Puntos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard as $index => $lUser)
                            @php
                                $isMe = $lUser->id === auth()->id();
                                $position = $index + 1;
                            @endphp
                            <tr class="leaderboard-row {{ $isMe ? 'current-user' : '' }}">
                                <td class="rank-col" style="padding: 0.85rem 1rem; font-size: 0.95rem;">
                                    @if($position == 1)
                                        🥇
                                    @elseif($position == 2)
                                        🥈
                                    @elseif($position == 3)
                                        🥉
                                    @else
                                        {{ $position }}
                                    @endif
                                </td>
                                <td style="padding: 0.85rem 1rem; font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem; border-bottom: none;">
                                    @if($lUser->profile_picture)
                                        <img src="{{ asset($lUser->profile_picture) }}" alt="Avatar {{ $lUser->name }}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-glass);">
                                    @else
                                        <div style="width: 42px; height: 42px; border-radius: 50%; background: rgba(255, 255, 255, 0.05); display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 700; color: var(--text-muted); border: 2px solid var(--border-glass);">
                                            {{ strtoupper(substr($lUser->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div style="display: flex; align-items: center; gap: 0.25rem;">
                                        <span style="font-weight: 600; {{ $isMe ? 'color: var(--primary);' : '' }}">
                                            {{ $lUser->name }}
                                        </span>
                                        @if($isMe)
                                            <span style="font-size: 0.65rem; background: rgba(99, 102, 241, 0.2); padding: 0.1rem 0.35rem; border-radius: 8px;">Tú</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: right; font-weight: 800; font-size: 1rem; color: {{ $isMe ? 'var(--secondary)' : 'white' }}; padding: 0.85rem 1rem;">
                                    {{ $lUser->points }} pts
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function switchTab(evt, tabId) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.classList.remove('active');
        });

        // Deactivate all tab buttons
        const tabBtns = document.querySelectorAll('.tab-btn');
        tabBtns.forEach(btn => {
            btn.classList.remove('active');
        });

        // Show current tab content & activate button
        document.getElementById(tabId).classList.add('active');
        evt.currentTarget.classList.add('active');
        
        // Save current tab in localStorage
        localStorage.setItem('activeQuinielaTab', tabId);
    }

    // Auto load last active tab
    document.addEventListener('DOMContentLoaded', () => {
        const lastActiveTab = localStorage.getItem('activeQuinielaTab');
        if (lastActiveTab) {
            const btn = document.querySelector(`button[onclick*="${lastActiveTab}"]`);
            if (btn) {
                btn.click();
            }
        }
    });
</script>
@endsection

@extends('layouts.app')

@section('title', 'Mi Quiniela - Pronósticos y Tabla General')

@section('content')
<div class="dashboard-header" style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="dashboard-title" style="font-size: 2.25rem; font-weight: 800; background: linear-gradient(135deg, white, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Quiniela Qatar a Norteamérica 2026
        </h1>
        <p class="dashboard-subtitle" style="color: var(--text-muted); font-size: 1rem; margin-top: 0.25rem;">
            Ingresa tus pronósticos, suma puntos y sube en la clasificación.
        </p>
    </div>
    <div class="points-summary" style="display: flex; gap: 1rem;">
        <div class="glass-card points-card" style="padding: 0.75rem 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 100px; border-radius: 14px;">
            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Mis Puntos</span>
            <span class="points-value" style="font-size: 1.75rem; font-weight: 800; color: var(--secondary);">{{ auth()->user()->points }}</span>
        </div>
        <div class="glass-card points-card" style="padding: 0.75rem 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 100px; border-radius: 14px;">
            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Mi Ranking</span>
            @php
                $rank = 1;
                foreach($leaderboard as $idx => $lUser) {
                    if($lUser->id === auth()->id()) {
                        $rank = $idx + 1;
                        break;
                    }
                }
            @endphp
            <span class="points-value" style="font-size: 1.75rem; font-weight: 800; color: var(--primary);">#{{ $rank }}</span>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="tabs-header">
    <button class="tab-btn active" onclick="switchTab(event, 'tab-partidos')">⚽ Mis Pronósticos</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-historial')">📋 Mi Historial</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-grupos')">📈 Tabla de Grupos</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-campeon')">🏆 Campeón del Mundial</button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-reglas')">📜 Reglas de Puntos</button>
</div>

<div class="dashboard-grid" style="display: flex; gap: 2rem; align-items: flex-start; margin-top: 2rem; flex-wrap: wrap;">
    <!-- Main Content Column -->
    <div style="flex: 2.5; min-width: 300px; width: 100%;">

        <!-- ═══════════════════════════════════════════════
             TAB: MIS PRONÓSTICOS
        ════════════════════════════════════════════════ -->
        <div id="tab-partidos" class="tab-content active">
            @if($groupedGames->isEmpty())
                <div class="glass-card" style="text-align: center; padding: 4rem 2rem;">
                    <span style="font-size: 3rem;">📅</span>
                    <h3 style="margin-top: 1rem; font-size: 1.5rem;">No hay partidos programados</h3>
                    <p style="color: var(--text-muted); margin-top: 0.5rem;">Los partidos del torneo aparecerán aquí pronto.</p>
                </div>
            @else
                    @php $inputIndex = 0; @endphp
                    @foreach($groupedGames as $stageName => $gamesList)
                        @php
                            $firstGame = $gamesList->first();
                            $stageKey  = $firstGame ? $firstGame->stage : 'group';
                            $isStageOpen = in_array($stageKey, $unlockedStages);
                            // Determine the stage that must be completed before this one
                            $prevStageMap = [
                                'r32'         => 'Fase de Grupos',
                                'r16'         => 'Dieciseisavos de Final',
                                'quarter'     => 'Octavos de Final',
                                'semi'        => 'Cuartos de Final',
                                'third_place' => 'Semifinales',
                                'final'       => 'Semifinales',
                            ];
                            $prevStage = $prevStageMap[$stageKey] ?? null;
                        @endphp
                        <div class="stage-section">
                            {{-- Stage Header with unlock indicator (#9) --}}
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                                <h3 class="stage-title" style="margin-bottom: 0;">{{ $stageName }}</h3>
                                @if($stageKey === 'group' || $isStageOpen)
                                    <span style="background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); padding: 0.2rem 0.65rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.04em;">
                                        🔓 ABIERTA
                                    </span>
                                @else
                                    <span style="background: rgba(255,255,255,0.04); color: var(--text-muted); border: 1px solid var(--border-glass); padding: 0.2rem 0.65rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.04em;">
                                        🔒 BLOQUEADA
                                    </span>
                                @endif
                            </div>

                            {{-- Blocked stage explanation (#9) --}}
                            @if(!$isStageOpen && $stageKey !== 'group' && $prevStage)
                                <div style="margin-bottom: 1.25rem; padding: 0.85rem 1.25rem; background: rgba(255,255,255,0.03); border: 1px dashed var(--border-glass); border-radius: 12px; display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted); font-size: 0.9rem;">
                                    <span style="font-size: 1.25rem;">⏳</span>
                                    <span>Esta fase se desbloqueará cuando se completen todos los partidos de <strong style="color: white;">{{ $prevStage }}</strong>.</span>
                                </div>
                            @endif

                            <div class="games-grid">
                                @foreach($gamesList as $game)
                                    @php
                                        $prediction = $predictions->get($game->id);
                                        $isStageUnlocked = in_array($game->stage, $unlockedStages);
                                        
                                        // Always work in Guatemala timezone
                                        $matchDateGT = $game->match_date->setTimezone('America/Guatemala');
                                        $nowGT = now()->setTimezone('America/Guatemala');
                                        
                                        $hasStarted = $matchDateGT->isPast();
                                        $isFinished = $game->status === 'finished';
                                        $canPredict = $isStageUnlocked && !$hasStarted && !$isFinished;

                                        $homeTeam = $game->homeTeam->getRealTeam();
                                        $awayTeam = $game->awayTeam->getRealTeam();
                                        $isHomePlaceholder = ($homeTeam->group === 'TBD');
                                        $isAwayPlaceholder = ($awayTeam->group === 'TBD');

                                        // Countdown urgency: < 3h → warning color
                                        $minutesLeft = $nowGT->diffInMinutes($matchDateGT, false);
                                        $isUrgent = !$hasStarted && $minutesLeft <= 180 && $minutesLeft > 0;
                                        
                                        // Format date label: Hoy / Mañana / Día semana
                                        $todayGT    = $nowGT->copy()->startOfDay();
                                        $matchDayGT = $matchDateGT->copy()->startOfDay();
                                        $diffDays   = $todayGT->diffInDays($matchDayGT, false);
                                        if ($diffDays == 0) {
                                            $dayLabel = 'Hoy';
                                        } elseif ($diffDays == 1) {
                                            $dayLabel = 'Mañana';
                                        } else {
                                            $dayNames = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
                                            $monthNames = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                                            $dayLabel = $dayNames[$matchDateGT->dayOfWeek] . ', ' . $monthNames[(int)$matchDateGT->format('n')] . ' ' . $matchDateGT->format('j');
                                        }
                                        $timeLabel = $matchDateGT->format('g:i A'); // e.g. 1:00 PM
                                    @endphp
                                    
                                    @if($canPredict)
                                    <form action="{{ route('predictions.save') }}" method="POST" class="game-card" @if(!$isStageUnlocked) style="opacity: 0.65; filter: grayscale(30%);" @endif>
                                        @csrf
                                        <input type="hidden" name="predictions[0][game_id]" value="{{ $game->id }}">
                                    @else
                                    <div class="game-card" @if(!$isStageUnlocked) style="opacity: 0.65; filter: grayscale(30%);" @endif>
                                    @endif
                                        
                                        <div class="game-header">
                                            <div class="game-date" style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.15rem;">
                                                <span>📅 {{ $dayLabel }} · {{ $timeLabel }}</span>
                                                @if($isUrgent)
                                                    @if($minutesLeft <= 60)
                                                        <span class="countdown-timer" data-deadline="{{ $matchDateGT->toIso8601String() }}" style="font-size: 0.75rem; color: #f59e0b; font-weight: 700; animation: pulse 1.5s infinite;">
                                                            ⚡ ¡Cierra en <span class="timer-display">Cargando...</span>!
                                                        </span>
                                                    @else
                                                        <span style="font-size: 0.75rem; color: #f59e0b; font-weight: 700; animation: pulse 1.5s infinite;">
                                                            ⚡ ¡Cierra en {{ round($minutesLeft) }} min!
                                                        </span>
                                                    @endif
                                                @elseif(!$hasStarted && !$isFinished)
                                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                                        ⏰ Límite: {{ $timeLabel }}
                                                    </span>
                                                @endif
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
                                                        <input type="number" value="{{ $prediction ? $prediction->home_score : '' }}" class="score-input" disabled placeholder="-">
                                                        <span class="vs-divider">VS</span>
                                                        <input type="number" value="{{ $prediction ? $prediction->away_score : '' }}" class="score-input" disabled placeholder="-">
                                                    @else
                                                        <input type="number" name="predictions[0][home_score]" value="{{ $prediction ? $prediction->home_score : '' }}" class="score-input" min="0" required placeholder="-">
                                                        <span class="vs-divider">VS</span>
                                                        <input type="number" name="predictions[0][away_score]" value="{{ $prediction ? $prediction->away_score : '' }}" class="score-input" min="0" required placeholder="-">
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
                                                <div class="match-points-feedback points-earned-exact">⭐ +3 Puntos (Marcador Exacto)</div>
                                            @elseif($prediction->points_earned == 1)
                                                <div class="match-points-feedback points-earned-outcome">✔️ +1 Punto (Ganador/Empate Acertado)</div>
                                            @else
                                                <div class="match-points-feedback points-earned-none">❌ 0 Puntos (Resultado Incorrecto)</div>
                                            @endif
                                        @elseif($isFinished && !$prediction)
                                            <div class="match-points-feedback points-earned-none">🚫 0 Puntos (No registraste pronóstico)</div>
                                        @elseif(!$isStageUnlocked)
                                            <div class="match-points-feedback" style="color: var(--text-muted); background: rgba(255, 255, 255, 0.02); border: 1px dashed var(--border-glass);">
                                                🔒 Fase Bloqueada — Completa la fase anterior
                                            </div>
                                        @elseif($hasStarted && $prediction)
                                            <div class="match-points-feedback" style="color: var(--warning); background: rgba(245, 158, 11, 0.05);">🔒 Pronóstico Cerrado</div>
                                        @elseif($hasStarted && !$prediction)
                                            <div class="match-points-feedback" style="color: var(--danger); background: rgba(239, 68, 68, 0.05);">🔒 Cerrado (Sin Pronóstico)</div>
                                        @elseif(!$hasStarted && $prediction)
                                            <div class="match-points-feedback" style="display: flex; justify-content: space-between; align-items: center; color: var(--accent); background: rgba(16, 185, 129, 0.05);">
                                                <span>✍️ Modificar Pronóstico</span>
                                                <button type="submit" class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem; border-radius: 8px;">Guardar</button>
                                            </div>
                                        @elseif(!$hasStarted && !$prediction)
                                            <div class="match-points-feedback" style="display: flex; justify-content: space-between; align-items: center; color: var(--primary); background: rgba(99, 102, 241, 0.05);">
                                                <span>📝 Pendiente de Pronóstico</span>
                                                <button type="submit" class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem; border-radius: 8px;">Guardar</button>
                                            </div>
                                        @endif
                                    @if($canPredict)
                                    </form>
                                    @else
                                    </div>
                                    @endif
                                    
                                    @if($canPredict)
                                        @php $inputIndex++; @endphp
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach

            @endif
        </div>

        <!-- ═══════════════════════════════════════════════
             TAB: MI HISTORIAL (#2)
        ════════════════════════════════════════════════ -->
        <div id="tab-historial" class="tab-content">
            {{-- Stats summary --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="glass-card" style="padding: 1rem; text-align: center; border-radius: 14px;">
                    <div style="font-size: 1.6rem; font-weight: 800; color: white;">{{ $historyStats['total'] }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-top: 0.25rem;">Partidos Jugados</div>
                </div>
                <div class="glass-card" style="padding: 1rem; text-align: center; border-radius: 14px; border-color: rgba(250,204,21,0.3);">
                    <div style="font-size: 1.6rem; font-weight: 800; color: #facc15;">{{ $historyStats['exact'] }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-top: 0.25rem;">⭐ Exactos (+3)</div>
                </div>
                <div class="glass-card" style="padding: 1rem; text-align: center; border-radius: 14px; border-color: rgba(16,185,129,0.3);">
                    <div style="font-size: 1.6rem; font-weight: 800; color: #10b981;">{{ $historyStats['correct'] }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-top: 0.25rem;">✔️ Correctos (+1)</div>
                </div>
                <div class="glass-card" style="padding: 1rem; text-align: center; border-radius: 14px; border-color: rgba(239,68,68,0.2);">
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--danger);">{{ $historyStats['wrong'] + $historyStats['missed'] }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-top: 0.25rem;">❌ Sin Puntos</div>
                </div>
            </div>

            {{-- Export button (#10) --}}
            @if($historyStats['total'] > 0)
                <div style="display: flex; justify-content: flex-end; margin-bottom: 1.25rem;">
                    <a href="{{ route('export.predictions') }}" class="btn btn-primary" style="font-size: 0.9rem; padding: 0.6rem 1.5rem; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 15px rgba(16,185,129,0.3);">
                        ⬇️ Exportar mis resultados (CSV)
                    </a>
                </div>
            @endif

            @if($predictionHistory->isEmpty())
                <div class="glass-card" style="text-align: center; padding: 3rem 2rem;">
                    <span style="font-size: 3rem;">⏳</span>
                    <h3 style="margin-top: 1rem; color: var(--text-muted);">Aún no hay partidos finalizados</h3>
                    <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 0.9rem;">Tu historial de pronósticos aparecerá aquí cuando terminen los partidos.</p>
                </div>
            @else
                <div class="glass-card" style="padding: 0; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead>
                            <tr style="background: rgba(255,255,255,0.04); border-bottom: 1px solid var(--border-glass);">
                                <th style="padding: 0.9rem 1.25rem; text-align: left; color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">Partido</th>
                                <th style="padding: 0.9rem 1rem; text-align: center; color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">Fecha</th>
                                <th style="padding: 0.9rem 1rem; text-align: center; color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">Mi Pronóstico</th>
                                <th style="padding: 0.9rem 1rem; text-align: center; color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">Resultado</th>
                                <th style="padding: 0.9rem 1.25rem; text-align: center; color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">Puntos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($predictionHistory as $row)
                                @php
                                    $g    = $row['game'];
                                    $pred = $row['prediction'];
                                    $pts  = $pred ? ($pred->points_earned ?? 0) : 0;
                                    $rowBg = $pred && $pts == 3 ? 'rgba(250,204,21,0.05)' : ($pred && $pts == 1 ? 'rgba(16,185,129,0.04)' : 'transparent');
                                @endphp
                                <tr style="border-bottom: 1px solid var(--border-glass); background: {{ $rowBg }}; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='{{ $rowBg }}'">
                                    <td style="padding: 0.85rem 1.25rem; font-weight: 600;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <img src="https://flagcdn.com/w40/{{ $g->homeTeam->code }}.png" style="height: 18px; border-radius: 2px;" onerror="this.style.display='none'">
                                            <span style="font-size: 0.8rem;">{{ $g->homeTeam->name }}</span>
                                            <span style="color: var(--text-muted); font-size: 0.75rem;">vs</span>
                                            <span style="font-size: 0.8rem;">{{ $g->awayTeam->name }}</span>
                                            <img src="https://flagcdn.com/w40/{{ $g->awayTeam->code }}.png" style="height: 18px; border-radius: 2px;" onerror="this.style.display='none'">
                                        </div>
                                    </td>
                                    <td style="padding: 0.85rem 1rem; text-align: center; color: var(--text-muted); font-size: 0.8rem; white-space: nowrap;">
                                        {{ $g->match_date->format('d/m/Y') }}<br>{{ $g->match_date->format('H:i') }}
                                    </td>
                                    <td style="padding: 0.85rem 1rem; text-align: center;">
                                        @if($pred)
                                            <span style="font-weight: 700; font-size: 0.95rem; color: white;">{{ $pred->home_score }} – {{ $pred->away_score }}</span>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 0.8rem;">Sin pronóstico</span>
                                        @endif
                                    </td>
                                    <td style="padding: 0.85rem 1rem; text-align: center;">
                                        <span style="font-weight: 800; font-size: 1rem;">{{ $g->home_score }} – {{ $g->away_score }}</span>
                                    </td>
                                    <td style="padding: 0.85rem 1.25rem; text-align: center;">
                                        @if(!$pred)
                                            <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                                        @elseif($pts == 3)
                                            <span style="background: rgba(250,204,21,0.15); color: #facc15; padding: 0.3rem 0.75rem; border-radius: 20px; font-weight: 800; font-size: 0.9rem;">⭐ +3</span>
                                        @elseif($pts == 1)
                                            <span style="background: rgba(16,185,129,0.15); color: #10b981; padding: 0.3rem 0.75rem; border-radius: 20px; font-weight: 800; font-size: 0.9rem;">✔️ +1</span>
                                        @else
                                            <span style="background: rgba(239,68,68,0.1); color: var(--danger); padding: 0.3rem 0.75rem; border-radius: 20px; font-weight: 800; font-size: 0.9rem;">❌ 0</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- ═══════════════════════════════════════════════
             TAB: TABLA DE GRUPOS (#7)
        ════════════════════════════════════════════════ -->
        <div id="tab-grupos" class="tab-content">
            @if(empty($groupStandings))
                <div class="glass-card" style="text-align: center; padding: 3rem 2rem;">
                    <span style="font-size: 3rem;">📊</span>
                    <h3 style="margin-top: 1rem; color: var(--text-muted);">La tabla se actualizará cuando terminen partidos</h3>
                    <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 0.9rem;">Los resultados de los partidos de grupo irán llenando las posiciones.</p>
                </div>
            @else
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem;">
                    @foreach($groupStandings as $groupLetter => $teams)
                        <div class="glass-card" style="padding: 0; overflow: hidden;">
                            <div style="padding: 0.85rem 1.25rem; background: rgba(99,102,241,0.1); border-bottom: 1px solid var(--border-glass); display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 1.1rem; font-weight: 800; color: var(--primary);">Grupo {{ $groupLetter }}</span>
                            </div>
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                <thead>
                                    <tr style="border-bottom: 1px solid var(--border-glass);">
                                        <th style="padding: 0.5rem 0.75rem; text-align: left; color: var(--text-muted); font-weight: 600; font-size: 0.7rem; text-transform: uppercase;">Equipo</th>
                                        <th style="padding: 0.5rem 0.4rem; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.7rem;" title="Partidos Jugados">PJ</th>
                                        <th style="padding: 0.5rem 0.4rem; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.7rem;" title="Ganados">G</th>
                                        <th style="padding: 0.5rem 0.4rem; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.7rem;" title="Empatados">E</th>
                                        <th style="padding: 0.5rem 0.4rem; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.7rem;" title="Perdidos">P</th>
                                        <th style="padding: 0.5rem 0.4rem; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.7rem;" title="Diferencia de Goles">DG</th>
                                        <th style="padding: 0.5rem 0.75rem; text-align: center; color: var(--primary); font-weight: 700; font-size: 0.7rem;">Pts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teams as $idx => $entry)
                                        @php
                                            $isTop2 = $idx < 2;
                                            $gd = $entry['gf'] - $entry['gc'];
                                            $rowStyle = $isTop2 ? 'background: rgba(99,102,241,0.05);' : '';
                                        @endphp
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04); {{ $rowStyle }}">
                                            <td style="padding: 0.6rem 0.75rem;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <span style="width: 18px; text-align: center; font-size: 0.75rem; color: {{ $isTop2 ? 'var(--primary)' : 'var(--text-muted)' }}; font-weight: 700;">{{ $idx + 1 }}</span>
                                                    @if($entry['team']->code && $entry['team']->group !== 'TBD')
                                                        <img src="https://flagcdn.com/w40/{{ $entry['team']->code }}.png" style="height: 16px; border-radius: 2px;" onerror="this.style.display='none'">
                                                    @endif
                                                    <span style="font-weight: {{ $isTop2 ? '700' : '500' }}; color: {{ $isTop2 ? 'white' : 'var(--text-muted)' }}; font-size: 0.82rem;">{{ $entry['team']->name }}</span>
                                                    @if($isTop2)
                                                        <span style="font-size: 0.6rem; background: rgba(99,102,241,0.2); color: var(--primary); padding: 0.1rem 0.3rem; border-radius: 4px; font-weight: 700;">Q</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="padding: 0.6rem 0.4rem; text-align: center; color: var(--text-muted);">{{ $entry['pj'] }}</td>
                                            <td style="padding: 0.6rem 0.4rem; text-align: center; color: #10b981;">{{ $entry['g'] }}</td>
                                            <td style="padding: 0.6rem 0.4rem; text-align: center; color: var(--text-muted);">{{ $entry['e'] }}</td>
                                            <td style="padding: 0.6rem 0.4rem; text-align: center; color: var(--danger);">{{ $entry['p'] }}</td>
                                            <td style="padding: 0.6rem 0.4rem; text-align: center; color: {{ $gd > 0 ? '#10b981' : ($gd < 0 ? 'var(--danger)' : 'var(--text-muted)') }};">{{ $gd > 0 ? '+' : '' }}{{ $gd }}</td>
                                            <td style="padding: 0.6rem 0.75rem; text-align: center; font-weight: 800; font-size: 0.95rem; color: {{ $isTop2 ? 'var(--primary)' : 'white' }};">{{ $entry['pts'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
                <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 1.25rem; text-align: center;">
                    <span style="background: rgba(99,102,241,0.15); color: var(--primary); padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">Q</span>
                    Clasificado a Dieciseisavos de Final · Ordenado por: Puntos → Diferencia de Goles → Goles a Favor
                </p>
            @endif
        </div>

        <!-- ═══════════════════════════════════════════════
             TAB: CAMPEÓN DEL MUNDIAL
        ════════════════════════════════════════════════ -->
        <div id="tab-campeon" class="tab-content">
            <div class="glass-card" style="max-width: 680px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <span style="font-size: 4rem;">🏆</span>
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin-top: 0.75rem; background: linear-gradient(135deg, #facc15, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        ¿Quién ganará el Mundial 2026?
                    </h2>
                    <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 0.95rem;">
                        Selecciona al campeón antes del inicio del torneo
                        <strong style="color: white;">({{ $championDeadline->format('d M · H:i') }})</strong>.
                        <br>Si aciertas, <strong style="color: #facc15;">ganas 50 puntos</strong>.
                    </p>
                </div>

                @if($worldChampion)
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

        <!-- ═══════════════════════════════════════════════
             TAB: REGLAS DE PUNTOS
        ════════════════════════════════════════════════ -->
        <div id="tab-reglas" class="tab-content">
            <div class="glass-card">
                <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Criterios de Puntuación</h3>

                <div class="rules-list">
                    <div class="rules-item">
                        <div class="rule-number" style="background: linear-gradient(135deg, #facc15, #f59e0b); box-shadow: 0 4px 10px rgba(250,204,21,0.35); font-size: 1rem;">+50</div>
                        <div class="rule-content">
                            <h4>🏆 Campeón del Mundial (50 Puntos)</h4>
                            <p>Acertar el equipo que se coronará Campeón del Mundo FIFA 2026. La selección se registra <strong>antes del inicio del torneo</strong> ({{ $championDeadline->format('d M · H:i') }} h). Solo se puede elegir un equipo por participante. Si aciertas, recibes automáticamente <strong>+50 puntos</strong>.</p>
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
                        <li>La selección del Campeón del Mundial debe registrarse <strong>antes del inicio del torneo</strong> ({{ $championDeadline->format('d M · H:i') }} h). No se podrá modificar después.</li>
                        <li>Los pronósticos de partidos se cierran individualmente al momento de iniciar el encuentro, de acuerdo a la hora oficial programada en el sistema.</li>
                        <li>Si un pronóstico queda incompleto (vacío), no se computarán puntos para ese partido.</li>
                        <li>En fases eliminatorias directas (R32 en adelante), el resultado que se toma en cuenta es el del tiempo reglamentario completo (90 min + descuento). No incluye prórroga ni penales.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Clasificación General -->
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
                                    @if($position == 1) 🥇
                                    @elseif($position == 2) 🥈
                                    @elseif($position == 3) 🥉
                                    @else {{ $position }}
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
                                        <span style="font-weight: 600; {{ $isMe ? 'color: var(--primary);' : '' }}">{{ $lUser->name }}</span>
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
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        evt.currentTarget.classList.add('active');
        localStorage.setItem('activeQuinielaTab', tabId);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Restore last active tab
        const lastActiveTab = localStorage.getItem('activeQuinielaTab');
        if (lastActiveTab && document.getElementById(lastActiveTab)) {
            const btn = document.querySelector(`button[onclick*="${lastActiveTab}"]`);
            if (btn) btn.click();
        }

        // ── Auto-reload when a match starts ──
        // Collect all match dates from the server
        const matchDates = @json(
            $groupedGames->flatten()->map(fn($g) => $g->match_date->toIso8601String())->values()
        );

        const now = Date.now();
        let nearestFutureMs = null;
        let hasLiveMatch = false;

        matchDates.forEach(dateStr => {
            const matchTime = new Date(dateStr).getTime();
            const diff = matchTime - now;

            if (diff > 0 && diff < 86400000) {
                // Match starts within the next 24 hours
                if (nearestFutureMs === null || diff < nearestFutureMs) {
                    nearestFutureMs = diff;
                }
            }

            // If a match started less than 3 hours ago, consider it "live"
            if (diff <= 0 && diff > -10800000) {
                hasLiveMatch = true;
            }
        });

        // Schedule page reload at exact match start time (+ 2 second buffer)
        if (nearestFutureMs !== null) {
            const reloadDelay = nearestFutureMs + 2000;
            console.log(`⚽ Auto-reload programado en ${Math.round(reloadDelay / 1000)}s cuando inicie el próximo partido.`);
            setTimeout(() => location.reload(), reloadDelay);
        }

        // If there are live matches, soft-refresh every 5 minutes to catch status changes
        if (hasLiveMatch) {
            console.log('🔄 Partidos en juego detectados. Auto-refresh cada 5 minutos.');
            setInterval(() => location.reload(), 300000);
        }

        // Countdown timers for urgent matches
        const timerElements = document.querySelectorAll('.countdown-timer');
        if (timerElements.length > 0) {
            setInterval(() => {
                const nowMs = Date.now();
                timerElements.forEach(el => {
                    const deadlineStr = el.getAttribute('data-deadline');
                    if (!deadlineStr) return;
                    const deadlineMs = new Date(deadlineStr).getTime();
                    let diff = deadlineMs - nowMs;
                    
                    const displayEl = el.querySelector('.timer-display');
                    if (!displayEl) return;
                    
                    if (diff <= 0) {
                        displayEl.textContent = "00:00";
                        return;
                    }
                    
                    // calculate mm:ss
                    const totalSeconds = Math.floor(diff / 1000);
                    const minutes = Math.floor(totalSeconds / 60);
                    const seconds = totalSeconds % 60;
                    
                    const mm = minutes.toString().padStart(2, '0');
                    const ss = seconds.toString().padStart(2, '0');
                    
                    displayEl.textContent = `${mm}:${ss}`;
                });
            }, 1000);
        }
    });
</script>
@endsection

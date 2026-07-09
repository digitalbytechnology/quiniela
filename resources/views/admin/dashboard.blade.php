@extends('layouts.app')

@section('title', 'Panel de Administración - Quiniela 2026')

@section('content')
<div style="margin-bottom: 2.5rem;">
    <h1 style="font-size: 2.25rem; font-weight: 800; background: linear-gradient(135deg, white, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Panel de Control del Administrador
    </h1>
    <p style="color: var(--text-muted); font-size: 1rem; margin-top: 0.25rem;">
        Registra los marcadores oficiales de los partidos para calcular los puntos de los usuarios en tiempo real.
    </p>
</div>

{{-- Champion Declaration Panel --}}
<div class="glass-card" style="margin-bottom: 2rem; border-color: rgba(250,204,21,0.2); background: rgba(250,204,21,0.02);">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
        🏆 Declarar Campeón del Mundial
        @if($worldChampion)
            <span style="font-size: 0.8rem; background: rgba(16,185,129,0.15); color: #10b981; padding: 0.25rem 0.75rem; border-radius: 20px; font-weight: 600;">Declarado</span>
        @else
            <span style="font-size: 0.8rem; background: rgba(250,204,21,0.15); color: #facc15; padding: 0.25rem 0.75rem; border-radius: 20px; font-weight: 600;">Pendiente</span>
        @endif
    </h3>

    @if($worldChampion)
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; padding: 1rem; background: rgba(250,204,21,0.06); border-radius: 10px;">
            <img src="https://flagcdn.com/w80/{{ $worldChampion->code }}.png" style="width: 50px; height: 33px; object-fit: cover; border-radius: 4px;">
            <div>
                <p style="font-size: 0.8rem; color: var(--text-muted);">Campeón actual declarado</p>
                <p style="font-weight: 800; font-size: 1.2rem; color: #facc15;">{{ $worldChampion->name }}</p>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.champion.declare') }}" method="POST" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        @csrf
        <div style="flex: 1; min-width: 220px;">
            <label style="display: block; font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin-bottom: 0.5rem; text-transform: uppercase;">{{ $worldChampion ? 'Cambiar Campeón' : 'Seleccionar Campeón' }}</label>
            <select name="champion_team_id" required
                style="width: 100%; background: #1a1535; border: 1px solid rgba(250,204,21,0.3); border-radius: 10px; padding: 0.75rem 1rem; color: white; font-size: 0.95rem; outline: none;">
                <option value="" disabled selected style="background: #1a1535; color: #94a3b8;">-- Elige el equipo campeón --</option>
                @foreach($realTeams->groupBy('group') as $grp => $groupTeams)
                    <optgroup label="Grupo {{ $grp }}" style="background: #1a1535; color: #94a3b8; font-weight: 700;">
                        @foreach($groupTeams as $team)
                            <option value="{{ $team->id }}" style="background: #1e1b3a; color: white;" {{ $worldChampionId == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="white-space: nowrap; background: linear-gradient(135deg, #d97706, #facc15); border: none; color: #1a1a2e;">
            🏆 {{ $worldChampion ? 'Actualizar Campeón' : 'Declarar Campeón (+20 pts)' }}
        </button>
    </form>
    <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.75rem;">Al declarar al campeón, se otorgarán automáticamente <strong style="color: #facc15;">20 puntos</strong> a todos los participantes que lo hayan seleccionado.</p>
</div>

{{-- Success print alert --}}
@if(session('success') && session('show_print_stage'))
    <div class="alert alert-success" style="display: flex; flex-direction: column; align-items: center; gap: 1rem; text-align: center; padding: 2rem; margin-bottom: 2rem; border-radius: 16px;">
        <div>
            <span style="font-size: 2rem;">✅</span>
            <h4 style="font-size: 1.2rem; margin-top: 0.5rem; font-weight: 700; color: #34d399;">Resultados Guardados</h4>
            <p style="font-size: 0.95rem; color: var(--text-muted); margin-top: 0.25rem;">{{ session('success') }}</p>
        </div>
        <a href="{{ route('admin.print_results', ['stage' => session('show_print_stage')]) }}" target="_blank" class="btn btn-primary" style="background: linear-gradient(135deg, var(--accent), #059669); box-shadow: 0 4px 12px var(--accent-glow); font-size: 1rem; padding: 0.75rem 2.5rem; border-radius: 30px;">
            📄 Imprimir PDF de esta Fase
        </a>
    </div>
@endif

<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: 1rem; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h3 style="font-size: 1.5rem; font-weight: 700;">
            ⚽ Resultados y Configuración por Fases
        </h3>
        <a href="{{ route('admin.print_results') }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 0.9rem;">
            📄 Imprimir PDF Completo (Todo el Torneo)
        </a>
    </div>

    @if($groupedGames->isEmpty())
        <div style="text-align: center; padding: 2rem 0; color: var(--text-muted);">
            No hay partidos registrados en la base de datos.
        </div>
    @else
        @php
            $stageTitles = [
                'group'       => 'Fase de Grupos',
                'r32'         => 'Dieciseisavos de Final',
                'r16'         => 'Octavos de Final',
                'quarter'     => 'Cuartos de Final',
                'semi'        => 'Semifinales',
                'third_place' => 'Tercer Lugar',
                'final'       => 'Gran Final',
            ];
        @endphp

        @foreach($groupedGames as $stageKey => $gamesList)
            <div style="margin-bottom: 3rem; background: rgba(255, 255, 255, 0.01); border: 1px solid var(--border-glass); border-radius: 20px; padding: 1.75rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h4 style="font-size: 1.3rem; font-weight: 700; color: var(--primary);">
                        🏆 {{ $stageTitles[$stageKey] ?? $stageKey }}
                    </h4>
                    <a href="{{ route('admin.print_results', ['stage' => $stageKey]) }}" target="_blank" class="btn btn-secondary btn-sm" style="padding: 0.4rem 1rem; font-size: 0.8rem; border-radius: 20px;">
                        🖨️ Imprimir Reporte de esta Fase
                    </a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($gamesList as $game)
                        @php
                            $isFinished = $game->status === 'finished';
                            $homeTeam = $game->homeTeam->getRealTeam();
                            $awayTeam = $game->awayTeam->getRealTeam();
                            $isHomePlaceholder = ($homeTeam->group === 'TBD');
                            $isAwayPlaceholder = ($awayTeam->group === 'TBD');
                        @endphp
                        <form action="{{ route('admin.games.bulk_update') }}" method="POST" class="admin-row" style="background: {{ $isFinished ? 'rgba(16, 185, 129, 0.02)' : 'rgba(255,255,255,0.01)' }}; border-color: {{ $isFinished ? 'rgba(16, 185, 129, 0.15)' : 'var(--border-glass)' }}; padding: 1rem 1.25rem;">
                            @csrf
                            <input type="hidden" name="stage_key" value="{{ $stageKey }}">
                            
                            <!-- Game Info Section -->
                            <div class="admin-row-game" style="flex: 1.2; min-width: 280px;">
                                <div style="min-width: 120px; font-size: 0.8rem; color: var(--text-muted);">
                                    <form action="{{ route('admin.game.schedule', $game->id) }}" method="POST" style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                        @csrf
                                        <input type="date" name="match_date" value="{{ $game->match_date->format('Y-m-d') }}"
                                            style="background: #1a1535; border: 1px solid var(--border-glass); border-radius: 6px; padding: 0.2rem 0.4rem; color: white; font-size: 0.75rem; outline: none; cursor: pointer;">
                                        <input type="time" name="match_time" value="{{ $game->match_date->format('H:i') }}"
                                            style="background: #1a1535; border: 1px solid var(--border-glass); border-radius: 6px; padding: 0.2rem 0.4rem; color: white; font-size: 0.75rem; outline: none; cursor: pointer;">
                                        <button type="submit" title="Guardar horario"
                                            style="background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.3); border-radius: 6px; padding: 0.2rem 0.5rem; color: #818cf8; font-size: 0.7rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; white-space: nowrap;"
                                            onmouseover="this.style.background='rgba(99,102,241,0.25)'"
                                            onmouseout="this.style.background='rgba(99,102,241,0.12)'">💾</button>
                                    </form>
                                    <div style="text-transform: uppercase; font-weight: 700; margin-top: 0.15rem; color: var(--primary);">
                                        {{ $game->stage === 'group' ? 'Grupo ' . $homeTeam->group : $game->stage }}
                                    </div>
                                </div>

                                <!-- Match Matchup -->
                                <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                                    @if($game->stage === 'group')
                                        <!-- Static team names for Group Stage -->
                                        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; justify-content: flex-end;">
                                            <span style="font-weight: 600; text-align: right; font-size: 0.9rem;">{{ $homeTeam->name }}</span>
                                            <img src="https://flagcdn.com/w80/{{ $homeTeam->code }}.png" alt="" style="width: 30px; height: 20px; object-fit: cover; border-radius: 2px;">
                                        </div>
                                        <span style="font-weight: 700; color: var(--text-muted); font-size: 0.8rem;">VS</span>
                                        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                                            <img src="https://flagcdn.com/w80/{{ $awayTeam->code }}.png" alt="" style="width: 30px; height: 20px; object-fit: cover; border-radius: 2px;">
                                            <span style="font-weight: 600; font-size: 0.9rem;">{{ $awayTeam->name }}</span>
                                        </div>
                                    @else
                                        <!-- Select dropdowns for Knockout Stage -->
                                        <div style="display: flex; align-items: center; gap: 0.4rem; flex: 1; justify-content: flex-end;">
                                            <select name="games[{{ $game->id }}][home_team_id]" style="background: #1a1535; border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.35rem; color: white; font-size: 0.8rem; max-width: 120px; cursor: pointer; outline: none;">
                                                @if($game->homeTeam->group === 'TBD' && $homeTeam->id === $game->homeTeam->id)
                                                    <option value="{{ $game->homeTeam->id }}" selected>{{ $game->homeTeam->name }}</option>
                                                @endif
                                                @foreach($realTeams as $team)
                                                    <option value="{{ $team->id }}" {{ $homeTeam->id == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                                @endforeach
                                            </select>
                                            @if($isHomePlaceholder)
                                                <span style="font-size: 1.05rem; margin: 0 0.15rem;">⚽</span>
                                            @else
                                                <img src="https://flagcdn.com/w80/{{ $homeTeam->code }}.png" alt="" style="width: 28px; height: 19px; object-fit: cover; border-radius: 2px;">
                                            @endif
                                        </div>
                                        
                                        <span style="font-weight: 700; color: var(--text-muted); font-size: 0.8rem;">VS</span>
                                        
                                        <div style="display: flex; align-items: center; gap: 0.4rem; flex: 1;">
                                            @if($isAwayPlaceholder)
                                                <span style="font-size: 1.05rem; margin: 0 0.15rem;">⚽</span>
                                            @else
                                                <img src="https://flagcdn.com/w80/{{ $awayTeam->code }}.png" alt="" style="width: 28px; height: 19px; object-fit: cover; border-radius: 2px;">
                                            @endif
                                            <select name="games[{{ $game->id }}][away_team_id]" style="background: #1a1535; border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.35rem; color: white; font-size: 0.8rem; max-width: 120px; cursor: pointer; outline: none;">
                                                @if($game->awayTeam->group === 'TBD' && $awayTeam->id === $game->awayTeam->id)
                                                    <option value="{{ $game->awayTeam->id }}" selected>{{ $game->awayTeam->name }}</option>
                                                @endif
                                                @foreach($realTeams as $team)
                                                    <option value="{{ $team->id }}" {{ $awayTeam->id == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Input scores & Qualifier -->
                            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                                @if($game->stage !== 'group')
                                    <!-- Winner selection for knockout -->
                                    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.1rem;">
                                        <span style="font-size: 0.6rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Clasifica:</span>
                                        <select name="games[{{ $game->id }}][winner_id]" data-game-field="{{ $game->id }}" style="background: #1a1535; border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.3rem; color: white; font-size: 0.75rem; outline: none; cursor: pointer; max-width: 115px;" {{ $isFinished ? 'disabled' : '' }}>
                                            <option value="">-- Auto --</option>
                                            @if(!$isHomePlaceholder)
                                                <option value="{{ $homeTeam->id }}" {{ $game->winner_id == $homeTeam->id ? 'selected' : '' }}>{{ $homeTeam->name }}</option>
                                            @endif
                                            @if(!$isAwayPlaceholder)
                                                <option value="{{ $awayTeam->id }}" {{ $game->winner_id == $awayTeam->id ? 'selected' : '' }}>{{ $awayTeam->name }}</option>
                                            @endif
                                        </select>
                                    </div>
                                @endif

                                <div class="admin-score-fields" style="gap: 0.35rem;">
                                    <input type="number" 
                                           name="games[{{ $game->id }}][home_score]" 
                                           value="{{ $game->home_score }}" 
                                           class="score-input" 
                                           data-game-field="{{ $game->id }}"
                                           min="0" 
                                           placeholder="-" 
                                           style="width: 42px; height: 36px; font-size: 1.05rem; border-radius: 8px;"
                                           {{ $isFinished ? 'disabled' : '' }}>
                                    
                                    <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem;">-</span>

                                    <input type="number" 
                                           name="games[{{ $game->id }}][away_score]" 
                                           value="{{ $game->away_score }}" 
                                           class="score-input" 
                                           data-game-field="{{ $game->id }}"
                                           min="0" 
                                           placeholder="-" 
                                           style="width: 42px; height: 36px; font-size: 1.05rem; border-radius: 8px;"
                                           {{ $isFinished ? 'disabled' : '' }}>
                                </div>

                                <div style="min-width: 100px; text-align: right; font-size: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                                    @if($isFinished)
                                        <span class="game-status status-finished" style="padding: 0.15rem 0.4rem; font-size: 0.7rem;" id="badge-{{ $game->id }}">✅ REGISTRADO</span>
                                        <button type="button" class="btn btn-sm" id="edit-btn-{{ $game->id }}"
                                            style="padding: 0.3rem 0.8rem; font-size: 0.72rem; background: rgba(250,204,21,0.12); color: #facc15; border: 1px solid rgba(250,204,21,0.3); border-radius: 8px; cursor: pointer; transition: all 0.2s ease;"
                                            onmouseover="this.style.background='rgba(250,204,21,0.25)'"
                                            onmouseout="this.style.background='rgba(250,204,21,0.12)'"
                                            onclick="enableEditGame({{ $game->id }})">
                                            ✏️ Editar
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-sm" id="save-btn-{{ $game->id }}" style="padding: 0.3rem 0.8rem; font-size: 0.75rem; display: none;">Guardar</button>
                                    @else
                                        <span class="game-status status-pending" style="padding: 0.15rem 0.4rem; font-size: 0.7rem;">PENDIENTE</span>
                                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.3rem 0.8rem; font-size: 0.75rem;">Guardar</button>
                                    @endif
                                </div>
                            </div>
                        </form>

                        {{-- Show all users' predictions for live games or games starting in <= 65 mins --}}
                        @php
                            $isLive = $game->match_date->copy()->subMinutes(65)->isBefore(now()) && !$isFinished;
                            $gamePredictions = $isLive && isset($livePredictions[$game->id]) ? $livePredictions[$game->id] : collect();
                            $finishedGamePreds = $isFinished && isset($finishedPredictions[$game->id]) ? $finishedPredictions[$game->id] : collect();
                        @endphp

                        {{-- LIVE: show automatically --}}
                        @if($isLive && $gamePredictions->isNotEmpty())
                            <div style="margin-top: -0.5rem; margin-bottom: 0.75rem; background: rgba(99, 102, 241, 0.04); border: 1px solid rgba(99, 102, 241, 0.15); border-radius: 12px; padding: 0.75rem 1rem; animation: fadeIn 0.4s ease;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.6rem;">
                                    <span style="font-size: 0.9rem;">👀</span>
                                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary);">Pronósticos de los participantes ({{ $gamePredictions->count() }})</span>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 0.4rem;">
                                    @foreach($gamePredictions->sortBy(fn($p) => $p->user->name) as $pred)
                                        @if($pred->user && $pred->user->role !== 'admin')
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.35rem 0.6rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border-glass); border-radius: 8px; font-size: 0.75rem;">
                                            <span style="font-weight: 600; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;" title="{{ $pred->user->name }}">{{ $pred->user->name }}</span>
                                            <span style="font-weight: 800; color: white; white-space: nowrap;">{{ $pred->home_score }} - {{ $pred->away_score }}</span>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @elseif($isLive && $gamePredictions->isEmpty())
                            <div style="margin-top: -0.5rem; margin-bottom: 0.75rem; background: rgba(255,255,255,0.02); border: 1px dashed var(--border-glass); border-radius: 12px; padding: 0.6rem 1rem; font-size: 0.75rem; color: var(--text-muted); text-align: center;">
                                🚫 Ningún participante registró pronóstico para este partido.
                            </div>
                        @endif

                        {{-- FINISHED: toggle button to show predictions --}}
                        @if($isFinished)
                            <div style="margin-top: -0.5rem; margin-bottom: 0.75rem;">
                                <button type="button" id="toggle-preds-btn-{{ $game->id }}"
                                    onclick="toggleFinishedPredictions({{ $game->id }})"
                                    style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.2); color: #818cf8; font-size: 0.78rem; font-weight: 600; padding: 0.4rem 1rem; border-radius: 20px; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 0.4rem;"
                                    onmouseover="this.style.background='rgba(99, 102, 241, 0.15)'"
                                    onmouseout="this.style.background='rgba(99, 102, 241, 0.08)'">
                                    <span id="toggle-preds-icon-{{ $game->id }}">👁️</span>
                                    <span id="toggle-preds-text-{{ $game->id }}">Ver Pronósticos ({{ $finishedGamePreds->filter(fn($p) => $p->user && $p->user->role !== 'admin')->count() }})</span>
                                </button>

                                <div id="preds-panel-{{ $game->id }}" style="display: none; margin-top: 0.5rem; background: rgba(99, 102, 241, 0.04); border: 1px solid rgba(99, 102, 241, 0.15); border-radius: 12px; padding: 0.75rem 1rem; animation: fadeIn 0.4s ease;">
                                    @if($finishedGamePreds->filter(fn($p) => $p->user && $p->user->role !== 'admin')->isNotEmpty())
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 0.4rem;">
                                            @foreach($finishedGamePreds->sortBy(fn($p) => $p->user->name) as $pred)
                                                @if($pred->user && $pred->user->role !== 'admin')
                                                @php
                                                    $predPoints = $pred->points_earned;
                                                    $pointColor = $predPoints == 3 ? '#10b981' : ($predPoints == 1 ? '#facc15' : '#ef4444');
                                                    $pointBg = $predPoints == 3 ? 'rgba(16,185,129,0.12)' : ($predPoints == 1 ? 'rgba(250,204,21,0.12)' : 'rgba(239,68,68,0.08)');
                                                    $pointBorder = $predPoints == 3 ? 'rgba(16,185,129,0.25)' : ($predPoints == 1 ? 'rgba(250,204,21,0.25)' : 'rgba(239,68,68,0.15)');
                                                @endphp
                                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.35rem 0.6rem; background: {{ $pointBg }}; border: 1px solid {{ $pointBorder }}; border-radius: 8px; font-size: 0.75rem;">
                                                    <span style="font-weight: 600; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 80px;" title="{{ $pred->user->name }}">{{ $pred->user->name }}</span>
                                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                                        <span style="font-weight: 800; color: white; white-space: nowrap;">{{ $pred->home_score }} - {{ $pred->away_score }}</span>
                                                        <span style="font-size: 0.65rem; font-weight: 700; color: {{ $pointColor }}; background: rgba(0,0,0,0.2); padding: 0.1rem 0.35rem; border-radius: 4px;">+{{ $predPoints ?? 0 }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="font-size: 0.75rem; color: var(--text-muted); text-align: center; padding: 0.5rem;">
                                            🚫 Ningún participante registró pronóstico para este partido.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            </div>
        @endforeach
    @endif
</div>
@endsection

@section('scripts')
<script>
function enableEditGame(gameId) {
    // Enable all fields for this game
    document.querySelectorAll('[data-game-field="' + gameId + '"]').forEach(function(el) {
        el.disabled = false;
        el.style.boxShadow = '0 0 0 2px rgba(250,204,21,0.4)';
        setTimeout(function() { el.style.boxShadow = ''; }, 1500);
    });

    // Hide the edit button
    var editBtn = document.getElementById('edit-btn-' + gameId);
    if (editBtn) editBtn.style.display = 'none';

    // Update badge to show editing mode
    var badge = document.getElementById('badge-' + gameId);
    if (badge) {
        badge.textContent = '✏️ EDITANDO';
        badge.style.background = 'rgba(250,204,21,0.15)';
        badge.style.color = '#facc15';
        badge.style.borderColor = 'rgba(250,204,21,0.3)';
    }

    // Show the save button
    var saveBtn = document.getElementById('save-btn-' + gameId);
    if (saveBtn) saveBtn.style.display = 'inline-flex';

    // Focus on the home score input
    var homeInput = document.querySelector('input[name="games[' + gameId + '][home_score]"]');
    if (homeInput) {
        homeInput.focus();
        homeInput.select();
    }
}

function toggleFinishedPredictions(gameId) {
    var panel = document.getElementById('preds-panel-' + gameId);
    var icon = document.getElementById('toggle-preds-icon-' + gameId);
    var text = document.getElementById('toggle-preds-text-' + gameId);

    if (!panel) return;

    var isVisible = panel.style.display !== 'none';

    if (isVisible) {
        panel.style.display = 'none';
        if (icon) icon.textContent = '👁️';
        if (text) text.textContent = text.textContent.replace('Ocultar', 'Ver');
    } else {
        panel.style.display = 'block';
        if (icon) icon.textContent = '🙈';
        if (text) text.textContent = text.textContent.replace('Ver', 'Ocultar');
    }
}
</script>
@endsection

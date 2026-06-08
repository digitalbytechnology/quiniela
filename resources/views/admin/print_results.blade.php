<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Outfit', sans-serif;
            color: #1f2937;
            background: #ffffff;
            margin: 2rem;
            line-height: 1.5;
        }
        h1 {
            font-size: 1.8rem;
            margin-bottom: 0.25rem;
            color: #1e1b4b;
            text-align: center;
        }
        p.subtitle {
            text-align: center;
            color: #4b5563;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        th, td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        td {
            font-size: 0.9rem;
        }
        .team-cell {
            font-weight: 600;
            width: 30%;
        }
        .score-cell {
            font-weight: 800;
            font-size: 1.1rem;
            text-align: center;
            color: #111827;
            background-color: #f9fafb;
            width: 15%;
        }
        .status-cell {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .status-finished {
            color: #059669;
        }
        .status-pending {
            color: #d97706;
        }
        @media print {
            body {
                margin: 1cm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 2rem; text-align: center;">
        <button onclick="window.print()" style="padding: 0.75rem 2rem; background-color: #6366f1; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">
            🖨️ Imprimir / Guardar como PDF
        </button>
        <button onclick="window.close()" style="padding: 0.75rem 2rem; background-color: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.95rem; margin-left: 1rem;">
            ❌ Cerrar
        </button>
    </div>

    <h1>{{ $title }}</h1>
    <p class="subtitle">Quiniela Mundial 2026 — Reporte Oficial generado el {{ date('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Fecha y Hora</th>
                <th>Fase</th>
                <th style="text-align: right;">Local</th>
                <th style="text-align: center;">Resultado</th>
                <th>Visitante</th>
                <th>Clasifica</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($games as $game)
                @php
                    $homeTeam = $game->homeTeam->getRealTeam();
                    $awayTeam = $game->awayTeam->getRealTeam();
                @endphp
                <tr>
                    <td style="color: #6b7280; font-size: 0.85rem;">{{ $game->match_date->format('d/m/Y H:i') }}</td>
                    <td style="text-transform: uppercase; font-weight: 700; font-size: 0.8rem; color: #4f46e5;">
                        @if($game->stage === 'group')
                            Grupo {{ $homeTeam->group }}
                        @else
                            {{ $game->stage }}
                        @endif
                    </td>
                    <td class="team-cell" style="text-align: right;">{{ $homeTeam->name }}</td>
                    <td class="score-cell">
                        @if($game->status === 'finished')
                            {{ $game->home_score }} - {{ $game->away_score }}
                        @else
                            VS
                        @endif
                    </td>
                    <td class="team-cell">{{ $awayTeam->name }}</td>
                    <td style="font-weight: 600; color: #0f172a;">
                        @if($game->winner_id)
                            {{ $game->winner->name }}
                        @elseif($game->status === 'finished')
                            @if($game->home_score > $game->away_score)
                                {{ $homeTeam->name }}
                            @elseif($game->away_score > $game->home_score)
                                {{ $awayTeam->name }}
                            @else
                                --
                            @endif
                        @else
                            --
                        @endif
                    </td>
                    <td class="status-cell {{ $game->status === 'finished' ? 'status-finished' : 'status-pending' }}">
                        {{ $game->status === 'finished' ? 'Registrado' : 'Pendiente' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>

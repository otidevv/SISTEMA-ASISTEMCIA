@extends('layouts.cepre')

@section('title', 'Cuadro de Vacantes')

@section('content')
    @include('partials.cepre.head')
    @include('partials.cepre.header')

    <!-- Hero Vacantes Premium Edition -->
    <section class="hero-section" style="min-height: 280px; background: linear-gradient(135deg, #d81b60 0%, #ad1457 50%, #880e4f 100%); position: relative; overflow: hidden; display: flex; align-items: center;">
        <!-- Efectos de brillo decorativos -->
        <div style="position: absolute; top: -50%; left: -10%; width: 60%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); transform: rotate(-30deg); pointer-events: none;"></div>
        
        <div class="kene-pattern-overlay" style="opacity: 0.05; background-size: 150px;"></div>
        
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px; position: relative; z-index: 2; text-align: center; color: white;">
            <span style="display: inline-block; padding: 6px 16px; background: rgba(255,255,255,0.1); border-radius: 30px; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 20px; backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.1);" class="animate-on-scroll">Información Oficial</span>
            <h1 class="animate-on-scroll" style="font-size: 56px; font-weight: 900; color: white; margin-bottom: 15px; letter-spacing: -1.5px; text-shadow: 0 4px 12px rgba(0,0,0,0.15);">Cuadro de <span style="color: var(--verde-cepre);">Vacantes</span></h1>
            <p class="animate-on-scroll" style="font-size: 19px; opacity: 0.95; max-width: 750px; margin: 0 auto; line-height: 1.6; font-weight: 500;">
                Ciclo Académico <span style="color: var(--verde-cepre);">{{ $cicloActivo->nombre ?? 'Vigente' }}</span>. Consulta las plazas habilitadas para el ingreso directo a la excelencia universitaria.
            </p>
        </div>
    </section>

    <!-- Divisor Original -->
    <div class="torn-paper-edge"></div>

    <!-- Tabla de Vacantes Optimizada -->
    <section class="courses-section academic-notebook-pattern" style="padding: 60px 0;">
        <div class="container" style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
            
            <div class="table-wrapper">
                <table class="table-premium">
                    <thead style="background: var(--azul-oscuro); color: white;">
                        <tr>
                            <th style="padding: 20px 30px;">Carrera Profesional</th>
                            <th style="padding: 20px; text-align: center;">Grupo</th>
                            <th style="padding: 20px; text-align: center;">Vacantes</th>
                            <th style="padding: 20px 30px; text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vacantes as $index => $vacante)
                            @php
                                $nombreCarrera = strtolower($vacante->carrera->nombre);
                                
                                // Mapeo por defecto
                                $icon = 'fa-graduation-cap';
                                $color = '#00aeef'; // Cyan
                                $bg = 'rgba(0, 174, 239, 0.1)';
                                
                                // Lógica condicional de asignación
                                if (str_contains($nombreCarrera, 'sistema') || str_contains($nombreCarrera, 'informática')) {
                                    $icon = 'fa-laptop-code';
                                    $color = '#2563eb'; // Blue
                                    $bg = 'rgba(37, 99, 235, 0.1)';
                                } elseif (str_contains($nombreCarrera, 'enfermería')) {
                                    $icon = 'fa-user-nurse';
                                    $color = '#ec008c'; // Magenta
                                    $bg = 'rgba(236, 0, 140, 0.1)';
                                } elseif (str_contains($nombreCarrera, 'derecho')) {
                                    $icon = 'fa-balance-scale';
                                    $color = '#b91c1c'; // Red
                                    $bg = 'rgba(185, 28, 28, 0.1)';
                                } elseif (str_contains($nombreCarrera, 'veterinaria') || str_contains($nombreCarrera, 'zootecnia')) {
                                    $icon = 'fa-paw';
                                    $color = '#16a34a'; // Green
                                    $bg = 'rgba(22, 163, 74, 0.1)';
                                } elseif (str_contains($nombreCarrera, 'forestal')) {
                                    $icon = 'fa-tree';
                                    $color = '#15803d'; // Dark Green
                                    $bg = 'rgba(21, 128, 61, 0.1)';
                                } elseif (str_contains($nombreCarrera, 'educación')) {
                                    $icon = 'fa-chalkboard-teacher';
                                    $color = '#ea580c'; // Orange
                                    $bg = 'rgba(234, 88, 12, 0.1)';
                                } elseif (str_contains($nombreCarrera, 'turismo') || str_contains($nombreCarrera, 'ecoturismo')) {
                                    $icon = 'fa-map-marked-alt';
                                    $color = '#0d9488'; // Teal
                                    $bg = 'rgba(13, 148, 136, 0.1)';
                                } elseif (str_contains($nombreCarrera, 'administración') || str_contains($nombreCarrera, 'contabilidad')) {
                                    $icon = 'fa-chart-pie';
                                    $color = '#8b5cf6'; // Purple
                                    $bg = 'rgba(139, 92, 246, 0.1)';
                                } elseif (str_contains($nombreCarrera, 'agro')) {
                                    $icon = 'fa-seedling';
                                    $color = '#84cc16'; // Lime
                                    $bg = 'rgba(132, 204, 22, 0.1)';
                                }
                            @endphp
                            <tr class="table-row-hover" style="--career-color: {{ $color }}; border-bottom: 1px solid #f1f5f9; transition: all 0.3s ease;">
                                <td data-label="Carrera" style="padding: 25px 40px;">
                                    <div style="display: flex; align-items: center; gap: 20px;" class="cell-content-carrera">
                                        <div style="width: 55px; height: 55px; border-radius: 18px; background: {{ $bg }}; display: flex; align-items: center; justify-content: center; color: {{ $color }}; font-size: 22px; transition: all 0.3s ease;" class="icon-box">
                                            <i class="fas {{ $icon }}"></i>
                                        </div>
                                        <div>
                                            <p style="margin: 0; font-weight: 800; color: var(--azul-oscuro); font-size: 18px; line-height: 1.2;">{{ $vacante->carrera->nombre }}</p>
                                        </div>
                                    </div>
                                </td>
                                @php
                                    $grupoName = strtoupper(trim($vacante->carrera->grupo ?? ''));
                                    
                                    // Colores definidos para Grupos
                                    if ($grupoName === 'A' || str_contains($grupoName, 'A')) {
                                        $gColor = '#ec008c'; // Magenta
                                        $gBg = 'rgba(236, 0, 140, 0.05)';
                                        $gBorder = 'rgba(236, 0, 140, 0.2)';
                                    } elseif ($grupoName === 'B' || str_contains($grupoName, 'B')) {
                                        $gColor = '#00aeef'; // Cyan Institucional
                                        $gBg = 'rgba(0, 174, 239, 0.05)';
                                        $gBorder = 'rgba(0, 174, 239, 0.2)';
                                    } elseif ($grupoName === 'C' || str_contains($grupoName, 'C')) {
                                        $gColor = '#74b927'; // Verde Cepre
                                        $gBg = 'rgba(116, 185, 39, 0.05)';
                                        $gBorder = 'rgba(116, 185, 39, 0.2)';
                                    } else {
                                        $gColor = '#64748b'; // Gris Plomo
                                        $gBg = '#f8fafc';
                                        $gBorder = '#e2e8f0';
                                    }
                                @endphp
                                <td data-label="Grupo" style="padding: 25px 40px; text-align: center;">
                                    <span style="font-size: 20px; font-weight: 900; color: {{ $gColor }}; background: {{ $gBg }}; padding: 8px 20px; border-radius: 12px; border: 1px solid {{ $gBorder }};">
                                            {{ $vacante->carrera->grupo ?? '-' }}
                                    </span>
                                </td>
                                <td data-label="Vacantes" style="padding: 25px 40px; text-align: center;">
                                    <div style="display: inline-flex; flex-direction: column; align-items: center;">
                                        <span style="font-size: 24px; font-weight: 900; color: var(--azul-oscuro);">{{ $vacante->vacantes_total }}</span>
                                        <span style="font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Plazas</span>
                                    </div>
                                </td>
                                <td data-label="Acciones" style="padding: 25px 40px; text-align: right;">
                                    <a href="{{ route('register') }}" class="btn-postular-table animate-pulse-btn" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 25px; border-radius: 15px; background: var(--azul-oscuro); color: white; text-decoration: none; font-weight: 800; font-size: 13px; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                                        <span>POSTULAR</span>
                                        <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-col" style="padding: 100px 40px; text-align: center;">
                                    <div style="width: 120px; height: 120px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                                        <i class="fas fa-clipboard-list" style="font-size: 45px; color: #cbd5e1;"></i>
                                    </div>
                                    <h4 style="color: var(--azul-oscuro); font-size: 22px; font-weight: 800;">No hay vacantes configuradas</h4>
                                    <p style="color: #64748b; margin-top: 10px;">Estamos actualizando los cuadros de vacantes para el ciclo actual. Por favor, vuelve pronto.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($vacantes->count() > 0)
                        <tfoot style="background: rgba(0, 174, 239, 0.03); border-top: 2px solid rgba(0, 174, 239, 0.1);">
                            <tr>
                                <td colspan="2" style="padding: 20px 40px; text-align: right; font-weight: 800; color: var(--azul-oscuro); font-size: 18px;">
                                    TOTAL VACANTES INGRESO DIRECTO:
                                </td>
                                <td style="padding: 20px 40px; text-align: center;">
                                    <div style="display: inline-flex; align-items: center; justify-content: center; background: var(--verde-cepre); color: white; border-radius: 15px; padding: 10px 25px; box-shadow: 0 4px 15px rgba(116, 185, 39, 0.3);">
                                        <i class="fas fa-users" style="margin-right: 10px; font-size: 18px;"></i>
                                        <span style="font-size: 26px; font-weight: 900;">{{ $vacantes->sum('vacantes_total') }}</span>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <div style="margin-top: 40px; background: rgba(0, 174, 239, 0.05); border-left: 5px solid var(--cyan-acento); padding: 25px; border-radius: 0 15px 15px 0;">
                <h4 style="margin: 0 0 10px 0; color: var(--azul-oscuro); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-lightbulb"></i>
                    Nota Importante
                </h4>
                <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #555;">
                    Las vacantes mostradas corresponden a las plazas de <strong>Ingreso Directo</strong> a través del Centro Pre-Universitario. El número de vacantes puede estar sujeto a cambios según resoluciones de consejo universitario.
                </p>
            </div>

        </div>
    </section>

    <style>
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 20px;
            box-shadow: var(--shadow-premium);
            border: 1px solid rgba(0,0,0,0.05);
            background: white;
        }
        .table-premium {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            min-width: 800px; /* Asegura que la tabla no se colapse en móviles muy pequeños */
        }
        .table-row-hover:hover {
            background: #fcfdfe;
        }
        .table-row-hover:hover .icon-box {
            background: var(--career-color, var(--verde-cepre)) !important;
            color: white !important;
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 5px 15px var(--career-color, rgba(0,0,0,0.2));
        }
        .btn-postular-table:hover {
            background: var(--career-color, var(--magenta-unamad)) !important;
            transform: translateX(5px);
            box-shadow: 0 8px 15px var(--career-color, rgba(236, 0, 140, 0.2));
            color: white !important;
        }
        
        @keyframes pulse-btn {
            0% { box-shadow: 0 0 0 0 rgba(0, 174, 239, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 174, 239, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 174, 239, 0); }
        }
        .animate-pulse-btn {
            animation: pulse-btn 2s infinite;
        }
        
        @media (max-width: 768px) {
            .hero-section h1 { font-size: 38px !important; }
            .hero-section p { font-size: 16px !important; }
            .icon-box { width: 45px !important; height: 45px !important; border-radius: 12px !important; font-size: 18px !important; }
            
            /* Responsive Table (Card Layout) */
            .table-wrapper { 
                border-radius: 0 !important; 
                box-shadow: none !important; 
                border: none !important; 
                background: transparent !important;
                padding: 0;
            }
            .table-premium {
                min-width: 100% !important;
                display: block;
            }
            .table-premium thead {
                display: none;
            }
            .table-premium tbody {
                display: block;
            }
            .table-premium tr {
                display: flex;
                flex-direction: column;
                background: white;
                border-radius: 20px;
                margin-bottom: 25px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.06);
                border: 1px solid rgba(0,0,0,0.05);
                padding: 25px 20px;
            }
            .table-premium td:not(.empty-col) {
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right !important;
                padding: 15px 0 !important;
                border-bottom: 1px dashed #e2e8f0;
            }
            .table-premium td:not(.empty-col):last-child {
                border-bottom: none;
                padding-bottom: 0 !important;
                margin-top: 10px;
            }
            .table-premium td:not(.empty-col)::before {
                content: attr(data-label);
                font-weight: 800;
                color: #64748b;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.5px;
                text-align: left;
                margin-right: 15px;
            }
            
            /* Ajuste interno de Carrera en movil */
            .cell-content-carrera {
                justify-content: flex-end;
                text-align: right;
            }
            .table-premium td[data-label="Carrera"] p {
                font-size: 15px !important;
            }
        }
    </style>

    @include('partials.cepre.countdown-widget')
    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection

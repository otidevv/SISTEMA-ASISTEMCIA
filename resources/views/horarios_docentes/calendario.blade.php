@extends('layouts.app')

@section('title', 'Horario Académico - Centro Preuniversitario')

@push('css')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    


    .header-content {
        position: relative;
        z-index: 2;
        text-align: center;
    }

    .header-universidad h1 {
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 15px 0;
        text-transform: uppercase;
        letter-spacing: 2px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .header-universidad h2 {
        font-size: 16px;
        font-weight: 400;
        margin: 8px 0;
        opacity: 0.9;
        line-height: 1.5;
    }

    .header-universidad h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 25px 0 10px 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .ciclo-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 14px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .info-section {
        padding: 30px 40px;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-bottom: 1px solid #e2e8f0;
    }

    .info-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .info-card {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #4f46e5;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .info-card h4 {
        margin: 0 0 8px 0;
        font-size: 12px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .info-card p {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .btn-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
    }

    .btn-secondary {
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    .btn-success {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(5, 150, 105, 0.4);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .schedule-container {
        padding: 0 40px 40px 40px;
    }

    .tabla-horario {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .tabla-horario th {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        padding: 20px 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 13px;
        border: none;
        position: relative;
    }

    .tabla-horario th::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #4f46e5, #7c3aed, #ec4899);
    }

    .tabla-horario td {
        padding: 0;
        border: 1px solid #f1f5f9;
        vertical-align: middle;
        height: 80px;
        position: relative;
    }

    .tabla-horario .hora-col {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-weight: 600;
        width: 100px;
        color: #1e293b;
        font-size: 12px;
        text-align: center;
        border-right: 2px solid #e2e8f0;
    }

    .celda-materia {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 8px;
        text-align: center;
        border-radius: 8px;
        margin: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .celda-materia::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .celda-materia:hover::before {
        left: 100%;
    }

    .celda-materia:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }

    /* Colores modernos y profesionales para las materias */
    .razonamiento-matematico {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .razonamiento-verbal {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .algebra {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .geometria {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
    }

    .aritmetica {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .economia {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
    }

    .fisica {
        background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(132, 204, 22, 0.3);
    }

    .educacion-civica {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
    }

    .trigonometria {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(20, 184, 166, 0.3);
    }

    .historia {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
    }

    .biologia {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
    }

    .comprension-lectora {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }

    .quimica {
        background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(234, 179, 8, 0.3);
    }

    .lenguaje {
        background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3);
    }

    .receso {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        color: white;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
    }

    .nombre-materia {
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1.2;
    }

    .nombre-docente {
        font-size: 9px;
        font-weight: 400;
        opacity: 0.9;
        line-height: 1.1;
    }

    .receso-row {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    }

    .receso-row td {
        padding: 15px;
        font-size: 16px;
        font-weight: 700;
        color: white;
        text-transform: uppercase;
        letter-spacing: 2px;
        text-align: center;
        border: none;
    }

    .coordinacion-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
        padding: 25px 40px;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-top: 3px solid #4f46e5;
        font-weight: 600;
        color: #1e293b;
        border-radius: 0 0 20px 20px;
    }

    .coordinacion-footer div {
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    /* Leyenda de colores */
    .legend {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin: 30px 0;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        font-size: 12px;
        font-weight: 500;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    @media print {
        body {
            background: white;
            padding: 0;
        }
        
        .btn-actions, .legend {
            display: none !important;
        }
        
        .main-container {
            box-shadow: none;
            border-radius: 0;
        }
        
        .header-universidad {
            background: #1a365d !important;
            -webkit-print-color-adjust: exact;
        }
    }

    @media (max-width: 768px) {
        .main-container {
            margin: 0 10px;
            border-radius: 15px;
        }
        
        .header-universidad, .info-section, .schedule-container {
            padding: 20px;
        }
        
        .tabla-horario {
            font-size: 10px;
        }
        
        .tabla-horario td {
            height: 60px;
        }
        
        .nombre-materia {
            font-size: 9px;
        }
        
        .nombre-docente {
            font-size: 8px;
        }
        
        .ciclo-badge {
            position: static;
            margin: 10px 0;
            text-align: center;
        }
        
        .info-cards {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <!-- Header de la Universidad -->
    <div class="header-universidad">
        <div class="ciclo-badge">CICLO ORDINARIO 2025-1</div>
        <div class="header-content">
            <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
            <h2>"Año del Bicentenario, de la consolidación de nuestra Independencia, y de la conmemoración de las heroicas batallas de Junín y Ayacucho"</h2>
            <h2>"Madre de Dios, Capital de la Biodiversidad del Perú"</h2>
            <h3>Centro Pre Universitario</h3>
        </div>
    </div>

    <!-- Información del horario -->
    <div class="info-section">
        <!-- Botones de acción -->
        <div class="btn-actions">
            <a href="{{ route('horarios-docentes.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Volver a Lista
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="mdi mdi-printer"></i> Imprimir Horario
            </button>
            <a href="{{ route('horarios-docentes.create') }}" class="btn btn-success">
                <i class="mdi mdi-plus"></i> Nuevo Horario
            </a>
        </div>

        <div class="info-cards">
            <div class="info-card">
                <h4>Turno</h4>
                <p>{{ $turnoSeleccionado ?? 'MAÑANA' }}</p>
            </div>
            <div class="info-card">
                <h4>Semana</h4>
                <p>N° {{ $semana ?? '01' }}</p>
            </div>
            <div class="info-card">
                <h4>Aula</h4>
                <p>{{ $aulaSeleccionada->codigo ?? $aulaSeleccionada->nombre ?? 'GRUPO - A1' }}</p>
            </div>
        </div>
    </div>

    <!-- Contenido de horarios -->
    <div class="schedule-container">
        @foreach($calendarios as $cal)
        <div style="text-align: center; margin-bottom: 10px;">
       <h4 style="margin: 20px 0 10px 0; font-size: 18px; font-weight: 600; text-transform: uppercase; color: #1e293b;">
           AULA: {{ $cal['aula']->codigo ?? $cal['aula']->nombre ?? 'SIN AULA' }} | TURNO: {{ strtoupper($cal['turno'] ?? 'NO DEFINIDO') }}
       </h4>
    </div>

        <table class="tabla-horario">
            <thead>
                <tr>
                    <th class="hora-col">HORA</th>
                    @foreach($cal['horariosSemana']['dias'] as $dia)
                        <th>{{ strtoupper($dia) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($cal['horariosSemana']['bloques'] as $index => $bloque)
                    @if($bloque == '10:00-10:30')
                        <tr class="receso-row">
                            <td class="hora-col">{{ $bloque }}</td>
                            <td colspan="6">RECESO 30 MINUTOS</td>
                        </tr>
                    @else
                        <tr>
                            <td class="hora-col">{{ $bloque }}</td>
                            @foreach($cal['horariosSemana']['dias'] as $dia)
                                <td>
                                    @foreach($cal['horariosSemana']['horarios'][$bloque][$dia] ?? [] as $horario)
                                        @php
                                            $nombreCurso = $horario->curso->nombre ?? 'Sin curso';
                                            $nombreDocente = $horario->docente->nombre_completo ?? 'Sin docente';
                                            $claseCurso = '';
                                            $nombreLower = strtolower($nombreCurso);
                                            
                                            if (str_contains($nombreLower, 'razonamiento matemático') || str_contains($nombreLower, 'razonamiento matematico')) 
                                                $claseCurso = 'razonamiento-matematico';
                                            elseif (str_contains($nombreLower, 'razonamiento verbal')) 
                                                $claseCurso = 'razonamiento-verbal';
                                            elseif (str_contains($nombreLower, 'álgebra') || str_contains($nombreLower, 'algebra')) 
                                                $claseCurso = 'algebra';
                                            elseif (str_contains($nombreLower, 'geometría') || str_contains($nombreLower, 'geometria')) 
                                                $claseCurso = 'geometria';
                                            elseif (str_contains($nombreLower, 'aritmética') || str_contains($nombreLower, 'aritmetica')) 
                                                $claseCurso = 'aritmetica';
                                            elseif (str_contains($nombreLower, 'economía') || str_contains($nombreLower, 'economia')) 
                                                $claseCurso = 'economia';
                                            elseif (str_contains($nombreLower, 'física') || str_contains($nombreLower, 'fisica')) 
                                                $claseCurso = 'fisica';
                                            elseif (str_contains($nombreLower, 'educación cívica') || str_contains($nombreLower, 'educacion civica')) 
                                                $claseCurso = 'educacion-civica';
                                            elseif (str_contains($nombreLower, 'trigonometría') || str_contains($nombreLower, 'trigonometria')) 
                                                $claseCurso = 'trigonometria';
                                            elseif (str_contains($nombreLower, 'historia')) 
                                                $claseCurso = 'historia';
                                            elseif (str_contains($nombreLower, 'biología') || str_contains($nombreLower, 'biologia')) 
                                                $claseCurso = 'biologia';
                                            elseif (str_contains($nombreLower, 'comprensión') || str_contains($nombreLower, 'comprension')) 
                                                $claseCurso = 'comprension-lectora';
                                            elseif (str_contains($nombreLower, 'química') || str_contains($nombreLower, 'quimica')) 
                                                $claseCurso = 'quimica';
                                            elseif (str_contains($nombreLower, 'lenguaje')) 
                                                $claseCurso = 'lenguaje';
                                            else 
                                                $claseCurso = 'otros';
                                        @endphp

                                        <div class="celda-materia {{ $claseCurso }}">
                                            <div class="nombre-materia">{{ $nombreCurso }}</div>
                                            <div class="nombre-docente">{{ $nombreDocente }}</div>
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        @endforeach
    </div>

    <!-- Footer -->
    <div class="coordinacion-footer">
        <div>MAYO - AGOSTO 2025</div>
        <div>COORDINACIÓN ACADÉMICA CEPRE</div>
    </div>
</div>
@endsection
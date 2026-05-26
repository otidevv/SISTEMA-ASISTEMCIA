@extends('layouts.cepre')

@section('title', 'Estructura del Examen | CEPRE UNAMAD')

@section('content')
    @php
        $inscripcionesAbiertas = isset($cicloActivo) && $cicloActivo->estaPeriodoInscripcionAbierto();
    @endphp
    @include('partials.cepre.head')
    @include('partials.cepre.header')

    <!-- Hero Estructura Premium Edition -->
    <section class="hero-section" style="min-height: 300px; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #0d9488 100%); position: relative; overflow: hidden; display: flex; align-items: center;">
        <!-- Efectos de brillo decorativos -->
        <div style="position: absolute; top: -50%; left: -10%; width: 60%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%); transform: rotate(-30deg); pointer-events: none;"></div>
        <div style="position: absolute; bottom: -50%; right: -10%; width: 60%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%); transform: rotate(30deg); pointer-events: none;"></div>
        
        <div class="kene-pattern-overlay" style="opacity: 0.05; background-size: 150px;"></div>
        
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px; position: relative; z-index: 2; text-align: center; color: white;">
            <span style="display: inline-block; padding: 6px 16px; background: rgba(255,255,255,0.1); border-radius: 30px; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 20px; backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.15);" class="animate-on-scroll">Admisión Oficial</span>
            <h1 class="animate-on-scroll" style="font-size: 50px; font-weight: 900; color: white; margin-bottom: 15px; letter-spacing: -1.5px; text-shadow: 0 4px 12px rgba(0,0,0,0.15);">Estructura del <span style="color: var(--verde-cepre);">Examen</span></h1>
            <p class="animate-on-scroll" style="font-size: 18px; opacity: 0.95; max-width: 750px; margin: 0 auto; line-height: 1.6; font-weight: 500;">
                Ciclo Académico <span style="color: var(--verde-cepre);">{{ $cicloActivo->nombre ?? 'Vigente' }}</span>. Consulta la distribución oficial de preguntas por asignaturas y las carreras correspondientes a cada grupo.
            </p>
        </div>
    </section>

    <!-- Divisor Estilo Papel -->
    <div class="torn-paper-edge"></div>

    <!-- Contenido Principal -->
    <section class="courses-section academic-notebook-pattern" style="padding: 60px 0; min-height: 600px;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            
            <div style="margin-bottom: 40px; background: white; border-left: 5px solid var(--azul-oscuro); padding: 25px; border-radius: 8px 20px 20px 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; align-items: flex-start; gap: 15px;">
                <div style="background: rgba(0, 174, 239, 0.1); color: var(--azul-oscuro); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h4 style="margin: 0 0 8px 0; color: var(--azul-oscuro); font-weight: 800; font-size: 18px;">Información Importante para el Postulante</h4>
                    <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #555;">
                        Los exámenes de CEPRE UNAMAD se estructuran según los tres grupos de carreras profesionales: <strong>Grupo A (Ingenierías)</strong>, <strong>Grupo B (Ciencias de la Salud)</strong> y <strong>Grupo C (Ciencias Sociales y Educación)</strong>. Cada grupo responde a un temario y ponderación específica según la naturaleza de sus áreas académicas.
                    </p>
                </div>
            </div>

            <!-- Panel de 3 Columnas (Grupos) -->
            <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -15px;">
                @foreach([
                    'A' => ['blue', 'Ingenierías', '#005bb7', 'rgba(0, 91, 183, 0.05)', '#00aeef'], 
                    'B' => ['green', 'Ciencias de la Salud', '#16a34a', 'rgba(22, 163, 74, 0.05)', '#74b927'], 
                    'C' => ['purple', 'Ciencias Sociales y Educación', '#7c3aed', 'rgba(124, 58, 237, 0.05)', '#a855f7']
                ] as $letra => $meta)
                    @php
                        $grupoColorClass = $meta[0];
                        $grupoName = $meta[1];
                        $grupoHex = $meta[2];
                        $grupoBg = $meta[3];
                        $grupoLightHex = $meta[4];
                        
                        $config = $examenConfigs[$letra] ?? null;
                        $distribuciones = $examenDistribucion[$letra] ?? collect();
                        $carreras = $carrerasPorGrupo[$letra] ?? collect();
                        
                        $totalPreguntas = $distribuciones->sum('cantidad_preguntas');
                        
                        // Formatear duración
                        $duracionMinutos = $config->duracion_minutos ?? 150;
                        if ($duracionMinutos == 150) {
                            $duracionTexto = 'Dos horas y media';
                        } else {
                            $horas = floor($duracionMinutos / 60);
                            $mins = $duracionMinutos % 60;
                            $duracionTexto = $horas . ' horas' . ($mins > 0 ? ' y ' . $mins . ' minutos' : '');
                        }
                    @endphp
                    <div class="col-lg-4 col-md-6 mb-4" style="flex: 1; min-width: 320px; padding: 0 15px; box-sizing: border-box; margin-bottom: 30px;">
                        <div class="card-grupo hover-shadow" style="background: white; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; height: 100%; display: flex; flex-direction: column; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);">
                            
                            <!-- Header de Grupo -->
                            <div style="background: linear-gradient(135deg, {{ $grupoHex }} 0%, {{ $grupoLightHex }} 100%); color: white; padding: 30px 20px; text-align: center; position: relative;">
                                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
                                <h3 style="margin: 0; font-size: 28px; font-weight: 900; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.15);">GRUPO {{ $letra }}</h3>
                                <p style="margin: 3px 0 15px 0; font-size: 13px; font-weight: 700; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">{{ $grupoName }}</p>
                                
                                <span style="display: inline-block; padding: 6px 20px; background: rgba(255,255,255,0.2); border-radius: 30px; font-size: 14px; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(5px); box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                                    TEMA {{ $config->tema ?? '-' }}
                                </span>
                            </div>

                            <!-- Tabla de Asignaturas -->
                            <div style="flex-grow: 1;">
                                <table style="width: 100%; border-collapse: collapse; margin: 0;">
                                    <thead>
                                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                            <th style="padding: 15px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Asignaturas</th>
                                            <th style="padding: 15px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; width: 120px; letter-spacing: 0.5px;">N° Preguntas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($distribuciones as $dist)
                                            <tr class="table-row-item" style="border-bottom: 1px solid #f1f5f9; font-size: 14px; transition: background 0.2s;">
                                                <td style="padding: 14px 20px; font-weight: 600; color: var(--azul-oscuro);">{{ $dist->curso->nombre }}</td>
                                                <td style="padding: 14px 20px; text-align: center; font-weight: 700; color: #334155; font-size: 16px;">{{ $dist->cantidad_preguntas }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" style="padding: 60px 20px; text-align: center; color: #94a3b8; font-size: 14px; background: #fafbfe;">
                                                    <div style="background: #f1f5f9; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: #cbd5e1;">
                                                        <i class="fas fa-clipboard-list" style="font-size: 20px;"></i>
                                                    </div>
                                                    <span style="font-weight: 600;">Estructura no configurada.</span>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($totalPreguntas > 0)
                                        <tfoot style="background: {{ $grupoBg }}; border-top: 2px solid {{ $grupoHex }}33;">
                                            <tr style="font-weight: 800; font-size: 14px;">
                                                <td style="padding: 18px 20px; color: var(--azul-oscuro); text-transform: uppercase; letter-spacing: 0.5px;">Total Preguntas:</td>
                                                <td style="padding: 18px 20px; text-align: center; color: {{ $grupoHex }}; font-size: 20px; font-weight: 900;">{{ $totalPreguntas }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>

                            <!-- Carreras Profesionales -->
                            <div style="padding: 25px 20px; background: #fdfeff; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; flex-grow: 0;">
                                <h4 style="margin: 0 0 15px 0; font-size: 12px; font-weight: 800; color: #94a3b8; text-transform: uppercase; text-align: center; letter-spacing: 0.8px;">Carreras Profesionales</h4>
                                <ul style="margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 8px;">
                                    @forelse($carreras as $carrera)
                                        <li style="font-size: 13px; font-weight: 600; color: #475569; padding: 6px 12px; display: flex; align-items: center; gap: 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9;">
                                            <i class="fas fa-check-circle" style="color: {{ $grupoHex }}; font-size: 14px; flex-shrink: 0;"></i>
                                            <span style="line-height: 1.2;">{{ $carrera->nombre }}</span>
                                        </li>
                                    @empty
                                        <li style="font-size: 13px; color: #94a3b8; text-align: center; padding: 10px;">Sin carreras asignadas</li>
                                    @endforelse
                                </ul>
                            </div>

                            <!-- Configuración General del Examen -->
                            <div style="background: #f8fafc; padding: 25px 20px; border-top: 1px dashed #cbd5e1; font-size: 13px; color: #64748b;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; align-items: center;">
                                    <span><strong>Duración:</strong></span>
                                    <span style="color: #334155; font-weight: 700;">{{ $duracionTexto }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; align-items: center;">
                                    <span><strong>Puntaje Máximo:</strong></span>
                                    <span style="color: #1e293b; font-weight: 800; font-size: 14px;">{{ $config->puntaje_maximo ?? 400 }} pts</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span><strong>Mínimo Aprobatorio:</strong></span>
                                    <span style="color: #ef4444; font-weight: 800; font-size: 14px;">{{ $config->puntaje_minimo_aprobatorio ?? 160 }} pts</span>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Botón de Inscripción -->
            <div style="text-align: center; margin-top: 50px; margin-bottom: 20px;">
                @if($inscripcionesAbiertas)
                    <a href="{{ route('home', ['postula' => 1]) }}" class="btn-inscripcion-estructura" style="display: inline-flex; align-items: center; gap: 12px; padding: 18px 40px; background: linear-gradient(135deg, #00aeef 0%, #005bb7 100%); color: white; text-decoration: none; border-radius: 50px; font-weight: 800; font-size: 16px; box-shadow: 0 10px 25px rgba(0, 174, 239, 0.3); transition: all 0.3s ease;">
                        <span>INICIAR MI POSTULACIÓN AHORA</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                @else
                    <a href="javascript:void(0)" onclick="mostrarAvisoInscripcionesCerradas()" class="btn-inscripcion-estructura-cerrado" style="display: inline-flex; align-items: center; gap: 12px; padding: 18px 40px; background: #64748b; color: white; text-decoration: none; border-radius: 50px; font-weight: 800; font-size: 16px; opacity: 0.8; cursor: not-allowed;">
                        <span>INSCRIPCIONES COMPLETADAS</span>
                        <i class="fas fa-lock"></i>
                    </a>
                @endif
            </div>

        </div>
    </section>

    <!-- Estilos Premium -->
    <style>
        .hover-shadow:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        .table-row-item:hover {
            background-color: #f8fafc;
        }
        .btn-inscripcion-estructura:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 174, 239, 0.4) !important;
            filter: brightness(1.05);
        }
    </style>

    @include('partials.cepre.countdown-widget')
    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function mostrarAvisoInscripcionesCerradas() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Inscripciones Culminadas!',
                    html: '<p style="color:#fff;">El proceso de inscripciones oficiales para el ciclo <strong>{{ $cicloActivo->nombre ?? "CEPRE" }}</strong> ha concluido.</p><p style="color:rgba(255,255,255,0.7); font-size: 13px;">Si tienes una postulación en revisión, puedes verificar su estado en la página de inicio presionando "Verificar mi Estado".</p>',
                    icon: 'info',
                    background: '#0c1e2f',
                    color: '#fff',
                    confirmButtonColor: '#00aeef',
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert('El proceso de inscripciones para el CEPRE ha concluido. Si tienes una postulación en revisión, puedes verificar tu estado en el botón respectivo de la página de inicio.');
            }
        }
    </script>
@endpush

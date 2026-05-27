<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Aula y Control de Asistencia</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 10px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { border: none; padding: 2px; }
        .logo-img { height: 35px; width: auto; }
        .title-section { text-align: center; font-weight: bold; font-size: 13px; color: #0A3C59; text-transform: uppercase; margin-bottom: 5px; }
        .sub-title { text-align: center; font-weight: bold; font-size: 11px; margin-bottom: 15px; text-transform: uppercase; }
        
        /* Ficha Informativa del Responsable */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; background-color: #f8fafc; border: 1px solid #cbd5e1; }
        .info-table td { padding: 6px 10px; border: 1px solid #cbd5e1; }
        .info-table td.label { font-weight: bold; color: #334155; width: 15%; background-color: #f1f5f9; }
        .info-table td.value { color: #0f172a; font-weight: 600; }

        /* Tabla de Estudiantes */
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 4px 6px; text-align: left; vertical-align: middle; }
        .table th { background-color: #0A3C59; color: #ffffff; font-weight: bold; text-align: center; font-size: 9px; text-transform: uppercase; border-bottom: 2px solid #062b40; }
        .table tr:nth-child(even) { background-color: #f8fafc; }
        .text-center { text-align: center; }
        .foto-cell { width: 45px; height: 45px; padding: 2px; text-align: center; }
        .foto-img { width: 40px; height: 40px; object-fit: cover; border-radius: 2px; }
        
        .firma-box { height: 45px; width: 120px; }
        .huella-box { height: 45px; width: 60px; text-align: center; font-size: 7px; color: #cbd5e1; vertical-align: bottom; padding-bottom: 2px; }
        
        .inhabilitado-label {
            color: #dc2626;
            font-weight: 900;
            font-size: 11px;
            text-align: center;
            background-color: #fee2e2;
            border: 2px dashed #dc2626;
            padding: 8px 4px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .firmas-footer { margin-top: 50px; width: 100%; border-collapse: collapse; }
        .firmas-footer td { border: none; padding: 10px; text-align: center; width: 50%; }
        .linea-firma { width: 200px; border-top: 1px solid #000; margin: 0 auto 5px; }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach($actas as $actaIndex => $acta)
        <div class="{{ $actaIndex > 0 ? 'page-break' : '' }}">
            <!-- Cabecera -->
            <table class="header-table">
                <tr>
                    <td style="width: 15%; text-align: left;">
                        <img src="{{ public_path('assets/images/logo unamad constancia_optimized.png') }}" class="logo-img" alt="Logo UNAMAD"/>
                    </td>
                    <td style="width: 70%; text-align: center; vertical-align: middle;">
                        <div class="title-section">UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</div>
                        <div style="font-size: 10px; font-weight: bold; color: #555;">CENTRO PRE UNIVERSITARIO - CEPRE UNAMAD</div>
                    </td>
                    <td style="width: 15%; text-align: right;">
                        <img src="{{ public_path('assets/images/logo cepre costancia_optimized.png') }}" class="logo-img" alt="Logo CEPRE"/>
                    </td>
                </tr>
            </table>

            <div class="sub-title">CONTROL DE ASISTENCIA Y ACTA DE EXAMEN - {{ $acta['examen_nombre'] }}</div>

            <!-- Ficha del Aula y Supervisor -->
            <table class="info-table">
                <tr>
                    <td class="label">Ciclo Académico:</td>
                    <td class="value" colspan="3">{{ $acta['ciclo_nombre'] }}</td>
                    <td class="label">Aula / Room:</td>
                    <td class="value" style="font-size: 14px; color: #dc2626;">{{ $acta['aula_nombre'] }}</td>
                </tr>
                <tr>
                    <td class="label">Responsable:</td>
                    <td class="value" colspan="3" style="font-size: 11px;">{{ $acta['docente_nombre'] ?: 'PTE. ASIGNACIÓN' }}</td>
                    <td class="label">Piso:</td>
                    <td class="value">{{ $acta['piso'] }}° Piso</td>
                </tr>
                <tr>
                    <td class="label">Rango Asientos:</td>
                    <td class="value">Carpeta {{ $acta['rango_inicio'] }} al {{ $acta['rango_fin'] }}</td>
                    <td class="label">Estudiantes:</td>
                    <td class="value">{{ $acta['cantidad_estudiantes'] }} alumnos</td>
                    <td class="label">Grupo / Tema:</td>
                    <td class="value">GRUPO {{ $acta['grupo'] }} - TEMA {{ $acta['tema'] }}</td>
                </tr>
            </table>

            <!-- Tabla de Alumnos -->
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">ASIENTO</th>
                        <th style="width: 8%;">FOTO</th>
                        <th style="width: 10%;">CÓDIGO</th>
                        <th style="width: 10%;">DNI</th>
                        <th style="width: 32%;">POSTULANTE (APELLIDOS Y NOMBRES)</th>
                        <th style="width: 15%;">CARRERA PROFESIONAL</th>
                        <th style="width: 12%;">FIRMA DEL POSTULANTE</th>
                        <th style="width: 8%;">HUELLA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($acta['estudiantes'] as $est)
                        <tr>
                            <td class="text-center" style="font-weight: bold; font-size: 12px;">{{ $est['asiento'] }}</td>
                            <td class="foto-cell">
                                <img src="{{ $est['foto'] ?: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' }}" class="foto-img" onerror="this.onerror=null;this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';" alt="Foto"/>
                            </td>
                            <td class="text-center" style="font-weight: bold;">{{ $est['codigo'] }}</td>
                            <td class="text-center">{{ $est['dni'] }}</td>
                            <td style="font-weight: 700; text-transform: uppercase;">{{ $est['nombres'] }}</td>
                            <td style="font-size: 8px; text-transform: uppercase; line-height: 1.1;">{{ $est['carrera'] }}</td>
                            
                            @if($est['inhabilitado'])
                                <td colspan="2" style="background-color: #fee2e2; vertical-align: middle;">
                                    <div class="inhabilitado-label">INHABILITADO</div>
                                </td>
                            @else
                                <td class="firma-box"></td>
                                <td class="huella-box">Huella</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 30px;">
                                No hay estudiantes asignados en este rango de asientos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Firmas del Footer -->
            <table class="firmas-footer">
                <tr>
                    <td>
                        <div class="linea-firma"></div>
                        <strong>{{ $acta['docente_nombre'] ?: 'FIRMA DEL DOCENTE RESPONSABLE' }}</strong><br/>
                        <span>DOCENTE CONTROLADOR / SUPERVISOR</span>
                    </td>
                    <td>
                        <div class="linea-firma"></div>
                        <strong>COMISIÓN DE ADMISIÓN CEPRE</strong><br/>
                        <span>COORDINADOR GENERAL CEPRE-UNAMAD</span>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>

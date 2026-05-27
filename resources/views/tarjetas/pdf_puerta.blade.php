<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista General de Ingreso por Aula - Examen</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 15px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .logo-img { height: 40px; width: auto; }
        .title-section { text-align: center; font-weight: bold; font-size: 14px; color: #0A3C59; text-transform: uppercase; }
        .header h3 { text-align: center; margin: 5px 0 10px 0; font-size: 13px; color: #333; font-weight: bold; text-transform: uppercase; }
        
        .aula-box { border: 1px solid #0A3C59; border-radius: 6px; padding: 10px; margin-bottom: 10px; background-color: #f8fafc; }
        .aula-title { font-size: 16px; font-weight: 900; color: #dc2626; margin-bottom: 8px; }
        .supervisor-list { margin: 0; padding-left: 20px; font-size: 11px; color: #1e293b; }
        .supervisor-item { margin-bottom: 3px; font-weight: 600; }

        .table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .table th, .table td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        .table th { background-color: #0A3C59; color: white; font-weight: bold; font-size: 10px; text-transform: uppercase; border-bottom: 2px solid #062b40; }
        .table tr:nth-child(even) { background-color: #f1f5f9; }
        .text-center { text-align: center; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @forelse($aulas as $index => $aula)
        <div class="{{ $index > 0 ? 'page-break' : '' }}">
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

            <div class="header">
                <h3>LISTADO DE INGRESOS - {{ $examen_nombre }}</h3>
            </div>
            
            <!-- Resumen del Aula y Supervisores -->
            <div class="aula-box">
                <div class="aula-title">AULA: {{ $aula['aula_nombre'] }} &nbsp;&nbsp;|&nbsp;&nbsp; PISO: {{ $aula['piso'] }}° Piso &nbsp;&nbsp;|&nbsp;&nbsp; TOTAL ESTUDIANTES: {{ count($aula['estudiantes']) }}</div>
                <strong style="font-size: 11px;">Docentes / Supervisores Asignados:</strong>
                <ul class="supervisor-list">
                    @forelse($aula['supervisores'] as $sup)
                        <li class="supervisor-item">
                            {{ $sup['nombre'] }} 
                            <span style="color: #475569; font-weight: normal;">(A cargo de los asientos {{ $sup['rango_inicio'] }} al {{ $sup['rango_fin'] }} - {{ $sup['cantidad'] }} estudiantes)</span>
                        </li>
                    @empty
                        <li class="supervisor-item" style="color: #dc2626;">Sin supervisor asignado</li>
                    @endforelse
                </ul>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">#</th>
                        <th style="width: 10%;" class="text-center">DNI</th>
                        <th style="width: 12%;" class="text-center">CÓDIGO</th>
                        <th style="width: 38%;">APELLIDOS Y NOMBRES</th>
                        <th style="width: 25%;">CARRERA PROFESIONAL</th>
                        <th style="width: 10%;" class="text-center">ASIENTO</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aula['estudiantes'] as $idx => $est)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="text-center">{{ $est['dni'] }}</td>
                            <td class="text-center" style="font-weight: bold;">{{ $est['codigo'] }}</td>
                            <td style="text-transform: uppercase; font-weight: 600;">{{ $est['nombres'] }}</td>
                            <td style="text-transform: uppercase; font-weight: 600; color: #334155;">{{ $est['carrera'] }}</td>
                            <td class="text-center" style="font-weight: bold; font-size: 12px;">{{ $est['asiento'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 20px;">
                                No hay postulantes aptos asignados en esta aula.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @empty
        <div style="text-align: center; margin-top: 50px; font-weight: bold; font-size: 16px;">
            No hay aulas distribuidas para este examen.
        </div>
    @endforelse
    
    <div class="footer">
        Generado por Sistema de Asistencia CEPRE UNAMAD - {{ date('d/m/Y H:i A') }}
    </div>
</body>
</html>

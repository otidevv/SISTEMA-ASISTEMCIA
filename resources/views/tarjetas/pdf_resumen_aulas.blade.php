<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen de Distribución por Aulas - Examen</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 15px; background-color: #ffffff; color: #1e293b; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .header-table td { border: none; }
        .logo-img { height: 42px; width: auto; }
        .title-section { text-align: center; font-weight: bold; font-size: 14px; color: #0A3C59; text-transform: uppercase; }
        .sub-title { text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 20px; text-transform: uppercase; color: #475569; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: center; vertical-align: middle; }
        .table th { background-color: #0A3C59; color: #ffffff; font-weight: bold; font-size: 10.5px; text-transform: uppercase; border-bottom: 2.5px solid #0f172a; }
        .table tr:nth-child(even) { background-color: #f8fafc; }
        
        .text-start { text-align: left !important; }
        .font-bold { font-weight: bold; }
        .text-dark { color: #0f172a; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>
    <!-- Cabecera -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: left; vertical-align: middle;">
                <img src="{{ public_path('assets/images/logo unamad constancia_optimized.png') }}" class="logo-img" alt="Logo UNAMAD"/>
            </td>
            <td style="width: 70%; text-align: center; vertical-align: middle;">
                <div class="title-section">UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</div>
                <div style="font-size: 11px; font-weight: bold; color: #475569; margin-top: 2px;">CENTRO PRE UNIVERSITARIO - CEPRE UNAMAD</div>
                <div style="font-size: 9.5px; font-weight: bold; color: #64748b; margin-top: 1px;">{{ $ciclo_nombre ?? 'CICLO ACADÉMICO' }}</div>
            </td>
            <td style="width: 15%; text-align: right; vertical-align: middle;">
                <img src="{{ public_path('assets/images/logo cepre costancia_optimized.png') }}" class="logo-img" alt="Logo CEPRE"/>
            </td>
        </tr>
    </table>

    <div class="sub-title">RESUMEN DE DISTRIBUCIÓN POR AULA, TEMA Y GRUPO - {{ $examen_nombre }}</div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">AULA</th>
                <th style="width: 15%;">TEMA</th>
                <th style="width: 15%;">GRUPO</th>
                <th style="width: 15%;">CANTIDAD</th>
                <th style="width: 40%;" class="text-start">DOCENTES / SUPERVISORES RESPONSABLES</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resumen as $row)
                <tr>
                    <td class="font-bold text-dark" style="font-size: 11.5px;">{{ $row['aula_nombre'] }}</td>
                    <td class="font-bold text-dark">{{ $row['tema'] }}</td>
                    <td class="font-bold text-dark">{{ $row['grupo'] }}</td>
                    <td class="font-bold text-dark" style="font-size: 11.5px;">{{ $row['cantidad'] }}</td>
                    <td class="text-start text-dark" style="font-size: 10px;">{{ $row['supervisores_texto'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 30px; color: #64748b;">No hay asignación generada para este examen.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado por Sistema de Asistencia CEPRE UNAMAD - {{ date('d/m/Y H:i A') }}
    </div>
</body>
</html>

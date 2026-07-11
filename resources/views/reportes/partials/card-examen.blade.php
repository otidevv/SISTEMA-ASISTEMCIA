@php
    $condicion = $info['condicion'] ?? 'Pendiente';
    
    // Configuración de colores institucionales CEPRE
    $colorCondicion = '#6b7280'; // Pendiente
    $bgCondicionSoft = '#f3f4f6';
    $bgCondicionBadge = '#e5e7eb';
    
    if ($condicion == 'Regular') {
        $colorCondicion = '#5a8a1f';
        $bgCondicionSoft = '#eef7e2';
        $bgCondicionBadge = '#d0e8b0';
    } elseif ($condicion == 'Amonestado') {
        $colorCondicion = '#c07800';
        $bgCondicionSoft = '#fff8e1';
        $bgCondicionBadge = '#ffe082';
    } elseif ($condicion == 'Inhabilitado') {
        $colorCondicion = '#cc0000';
        $bgCondicionSoft = '#fce4f0';
        $bgCondicionBadge = '#f5a3d3';
    }

    $puedeRendir = ($info['puede_rendir'] ?? 'NO') == 'SÍ';
    $esProyeccion = $info['es_proyeccion'] ?? false;
@endphp

<div class="exam-card" style="border: 1px solid #d1dde4; border-left: 5px solid {{ $colorCondicion }}; border-radius: 8px; margin-bottom: 20px; overflow: hidden; background-color: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
    {{-- Header del Examen --}}
    <table style="width: 100%; border-collapse: collapse; background-color: #2b5a6f; color: white;">
        <tr>
            <td style="padding: 10px 14px; vertical-align: middle;">
                <span style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                    &raquo; {{ $titulo }}
                    @if(\Carbon\Carbon::hasFormat($fecha, 'Y-m-d H:i:s') || \Carbon\Carbon::hasFormat($fecha, 'Y-m-d'))
                        &mdash; FECHA: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                    @else
                        &mdash; FECHA: {{ $fecha }}
                    @endif
                </span>
            </td>
            <td style="padding: 10px 14px; text-align: right; vertical-align: middle;">
                @if ($esProyeccion)
                    <span style="background: #00aeef; border: 1.5px solid #fff; padding: 1px 6px; border-radius: 3px; font-size: 7.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px;">PROYECCIÓN</span>
                @endif
                <span style="font-size: 8px; margin-left: 8px; font-family: monospace; font-weight: bold; background-color: rgba(255,255,255,0.15); padding: 2px 6px; border-radius: 3px; letter-spacing: 0.3px;">
                    ASISTIDOS: {{ $info['dias_asistidos'] }} &nbsp;|&nbsp; FALTAS: {{ $info['dias_falta'] }} &nbsp;|&nbsp; TOTAL: {{ $info['dias_habiles'] }}
                </span>
            </td>
        </tr>
    </table>

    <div style="padding: 15px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                {{-- LADO IZQUIERDO: KPI Principal de Asistencia --}}
                <td style="width: 42%; vertical-align: middle; padding-right: 15px; border-right: 1px dashed #e2e8f0;">
                    <div style="text-align: center; padding: 6px; background-color: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 7.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Porcentaje de Asistencia</span>
                        <span style="font-size: 24px; font-weight: 900; color: {{ $puedeRendir ? '#5a8a1f' : '#cc0000' }}; line-height: 1;">{{ $info['porcentaje_asistencia'] }}%</span>
                        
                        {{-- Barra de Progreso Integrada --}}
                        <div style="height: 7px; background-color: #cbd5e1; border-radius: 4px; overflow: hidden; border: none; position: relative; margin-top: 8px;">
                            <div style="height: 100%; background-color: {{ $puedeRendir ? '#5a8a1f' : '#cc0000' }}; width: {{ $info['porcentaje_asistencia'] }}%; border-radius: 4px;"></div>
                        </div>
                    </div>
                </td>
                
                {{-- LADO DERECHO: Detalle de Estados / KPIs Secundarios --}}
                <td style="width: 58%; vertical-align: middle; padding-left: 20px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding-bottom: 8px; width: 50%;">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">Inasistencias</span>
                                <span style="font-size: 11px; font-weight: bold; color: #cc0000;">{{ $info['porcentaje_falta'] }}% <small style="font-size: 8px; font-weight: normal; color: #64748b;">de faltas</small></span>
                            </td>
                            <td style="padding-bottom: 8px; width: 50%;">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">Condición Académica</span>
                                <span style="display: inline-block; background-color: {{ $bgCondicionBadge }}; color: {{ $colorCondicion }}; padding: 2px 8px; border-radius: 20px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid {{ $colorCondicion }}; margin-top: 2px;">
                                    {{ $condicion }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 4px; border-top: 1px solid #f1f5f9;">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">Habilitación</span>
                                <span style="display: inline-block; background-color: {{ $puedeRendir ? '#eef7e2' : '#fce4f0' }}; color: {{ $puedeRendir ? '#5a8a1f' : '#cc0000' }}; padding: 2px 8px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid {{ $puedeRendir ? '#5a8a1f' : '#cc0000' }}; margin-top: 2px;">
                                    {{ $puedeRendir ? 'Habilitado SÍ' : 'Habilitado NO' }}
                                </span>
                            </td>
                            <td style="padding-top: 4px; border-top: 1px solid #f1f5f9;">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px;">Inasistencias Permitidas</span>
                                <span style="font-size: 9.5px; font-weight: bold; color: #1e293b;">Máximo 30%</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    
    {{-- Banner de Estado Final --}}
    <div style="padding: 8px 14px; font-size: 8.5px; text-align: center; background-color: {{ $puedeRendir ? '#eef7e2' : '#fce4f0' }}; color: {{ $puedeRendir ? '#355311' : '#880000' }}; border-top: 1px solid #e2e8f0; letter-spacing: 0.5px; text-transform: uppercase;">
        <strong>
            {{ $puedeRendir ? '✓ EL ESTUDIANTE SE ENCUENTRA HABILITADO PARA ESTA EVALUACIÓN' : '⚠ EL ESTUDIANTE SE ENCUENTRA INHABILITADO POR EXCESO DE INASISTENCIAS' }}
        </strong>
    </div>
</div>

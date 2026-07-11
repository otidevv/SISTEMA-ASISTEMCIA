@php
    $condicion = $info['condicion'] ?? 'Pendiente';
    
    // Configuración de colores institucionales CEPRE
    // Regular: Verde (#5a8a1f), Amonestado: Ámbar (#c07800), Inhabilitado: Rojo (#cc0000)
    $colorCondicion = '#6b7280'; // Gris por defecto (Pendiente)
    $bgCondicionSoft = '#f3f4f6';
    
    if ($condicion == 'Regular') {
        $colorCondicion = '#5a8a1f';
        $bgCondicionSoft = '#eef7e2';
    } elseif ($condicion == 'Amonestado') {
        $colorCondicion = '#c07800';
        $bgCondicionSoft = '#fff8e1';
    } elseif ($condicion == 'Inhabilitado') {
        $colorCondicion = '#cc0000';
        $bgCondicionSoft = '#fce4f0';
    }

    $puedeRendir = ($info['puede_rendir'] ?? 'NO') == 'SÍ';
    $esProyeccion = $info['es_proyeccion'] ?? false;
@endphp

<div class="exam-card" style="border: 1.5px solid #000; border-radius: 4px; margin-bottom: 20px; overflow: hidden; background-color: #ffffff;">
    {{-- Header del Examen --}}
    <table style="width: 100%; border-collapse: collapse; background-color: #2b5a6f; color: white;">
        <tr>
            <td style="padding: 8px 12px; vertical-align: middle;">
                <span style="font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                    &#9656; {{ $titulo }}
                    @if(\Carbon\Carbon::hasFormat($fecha, 'Y-m-d H:i:s') || \Carbon\Carbon::hasFormat($fecha, 'Y-m-d'))
                        &mdash; FECHA: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                    @else
                        &mdash; FECHA: {{ $fecha }}
                    @endif
                </span>
            </td>
            <td style="padding: 8px 12px; text-align: right; vertical-align: middle;">
                @if ($esProyeccion)
                    <span style="background: #00aeef; border: 1px solid #fff; padding: 1px 6px; border-radius: 2px; font-size: 8px; font-weight: bold; text-transform: uppercase;">PROYECCIÓN</span>
                @endif
                <span style="font-size: 8.5px; margin-left: 10px; font-family: monospace; font-weight: bold; background-color: rgba(0,0,0,0.2); padding: 2px 6px; border-radius: 2px;">
                    AST: {{ $info['dias_asistidos'] }} | 
                    FAL: {{ $info['dias_falta'] }} | 
                    TOT: {{ $info['dias_habiles'] }}
                </span>
            </td>
        </tr>
    </table>

    <div style="padding: 12px 14px;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
            <tr>
                <td style="width: 25%; text-align: center; border-right: 1.5px solid #ddd;">
                    <span style="display: block; font-size: 8px; font-weight: bold; color: #555; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px;">Asistencia</span>
                    <span style="font-size: 16px; font-weight: bold; color: #5a8a1f;">{{ $info['porcentaje_asistencia'] }}%</span>
                </td>
                <td style="width: 25%; text-align: center; border-right: 1.5px solid #ddd;">
                    <span style="display: block; font-size: 8px; font-weight: bold; color: #555; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px;">Faltas</span>
                    <span style="font-size: 16px; font-weight: bold; color: #cc0000;">{{ $info['porcentaje_falta'] }}%</span>
                </td>
                <td style="width: 25%; text-align: center; border-right: 1.5px solid #ddd; background-color: {{ $bgCondicionSoft }};">
                    <span style="display: block; font-size: 8px; font-weight: bold; color: #555; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px;">Estado</span>
                    <span style="font-size: 13px; font-weight: bold; color: {{ $colorCondicion }}; text-transform: uppercase;">
                        {{ $condicion }}
                    </span>
                </td>
                <td style="width: 25%; text-align: center; background-color: {{ $puedeRendir ? '#eef7e2' : '#fce4f0' }};">
                    <span style="display: block; font-size: 8px; font-weight: bold; color: #555; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px;">Habilitado</span>
                    <span style="font-size: 14px; font-weight: bold; color: {{ $puedeRendir ? '#5a8a1f' : '#cc0000' }}; text-transform: uppercase;">
                        {{ $puedeRendir ? 'SÍ' : 'NO' }}
                    </span>
                </td>
            </tr>
        </table>

        <!-- Barra de Progreso Simplificada y elegante para DomPDF -->
        <div style="height: 12px; background-color: #f1f5f9; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; position: relative;">
            <div style="height: 100%; background-color: {{ $puedeRendir ? '#5a8a1f' : '#cc0000' }}; width: {{ $info['porcentaje_asistencia'] }}%; border-radius: 6px;"></div>
        </div>
    </div>
    
    {{-- Banner de Estado Final --}}
    <div style="padding: 6px 12px; font-size: 9px; text-align: center; background-color: {{ $puedeRendir ? '#eef7e2' : '#fce4f0' }}; color: {{ $puedeRendir ? '#355311' : '#880000' }}; border-top: 1.5px solid #000; letter-spacing: 0.5px;">
        <strong>
            {{ $puedeRendir ? 'ESTUDIANTE HABILITADO PARA RENDIR EXAMEN' : 'ESTUDIANTE INHABILITADO POR EXCESO DE FALTAS' }}
        </strong>
    </div>
</div>

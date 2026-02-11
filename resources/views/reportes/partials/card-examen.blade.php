@php
    $condicion = $info['condicion'] ?? 'Pendiente';
    $class = $condicion == 'Regular' ? 'success' : ($condicion == 'Amonestado' ? 'warning' : 'danger');
    $color = $condicion == 'Regular' ? '#27ae60' : ($condicion == 'Amonestado' ? '#f39c12' : '#e74c3c');
    $bg_header = $color;
    $puedeRendir = ($info['puede_rendir'] ?? 'NO') == 'SÍ';
    $esProyeccion = $info['es_proyeccion'] ?? false;
@endphp

<div class="exam-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; background-color: {{ $bg_header }}; color: white;">
        <tr>
            <td style="padding: 10px 15px; vertical-align: middle;">
                <span style="font-weight: 800; font-size: 14px;">
                    @if($class == 'success') ✓ @elseif($class == 'warning') ! @else ✕ @endif
                    {{ $titulo }} - FECHA: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                </span>
            </td>
            <td style="padding: 10px 15px; text-align: right; vertical-align: middle;">
                @if ($esProyeccion)
                    <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: 700;">PROYECCIÓN</span>
                @endif
                <span style="font-size: 10px; margin-left: 10px;">
                    A: <strong>{{ $info['dias_asistidos'] }}</strong> | 
                    F: <strong>{{ $info['dias_falta'] }}</strong> | 
                    T: <strong>{{ $info['dias_habiles'] }}</strong>
                </span>
            </td>
        </tr>
    </table>

    <div style="padding: 15px;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <td style="width: 25%; text-align: center; border-right: 1px solid #dee2e6;">
                    <span style="display: block; font-size: 9px; font-weight: 700; color: #555; text-transform: uppercase;">Asistencia</span>
                    <span style="font-size: 18px; font-weight: 800; color: #27ae60;">{{ $info['porcentaje_asistencia'] }}%</span>
                </td>
                <td style="width: 25%; text-align: center; border-right: 1px solid #dee2e6;">
                    <span style="display: block; font-size: 9px; font-weight: 700; color: #555; text-transform: uppercase;">Faltas</span>
                    <span style="font-size: 18px; font-weight: 800; color: #e74c3c;">{{ $info['porcentaje_falta'] }}%</span>
                </td>
                <td style="width: 25%; text-align: center; border-right: 1px solid #dee2e6;">
                    <span style="display: block; font-size: 9px; font-weight: 700; color: #555; text-transform: uppercase;">Estado</span>
                    <span style="font-size: 16px; font-weight: 800; color: {{ $color }};">{{ $condicion }}</span>
                </td>
                <td style="width: 25%; text-align: center;">
                    <span style="display: block; font-size: 9px; font-weight: 700; color: #555; text-transform: uppercase;">Habilitado</span>
                    <span style="font-size: 16px; font-weight: 800; color: {{ $puedeRendir ? '#27ae60' : '#e74c3c' }};">
                        {{ $puedeRendir ? 'SÍ' : 'NO' }}
                    </span>
                </td>
            </tr>
        </table>

        <!-- Barra de Progreso Simplificada para DomPDF -->
        <div style="height: 18px; background-color: #e9ecef; border-radius: 9px; overflow: hidden; border: 1px solid #dee2e6; position: relative;">
            <div style="height: 100%; background-color: {{ $color }}; width: {{ $info['porcentaje_asistencia'] }}%; text-align: center; color: white; font-size: 9px; font-weight: 800; line-height: 18px;">
                {{ $info['porcentaje_asistencia'] }}%
            </div>
        </div>
    </div>
    
    <div style="padding: 8px 15px; font-size: 11px; text-align: center; background-color: {{ $puedeRendir ? '#e8f5e9' : '#ffebee' }}; color: {{ $puedeRendir ? '#1b5e20' : '#b71c1c' }}; border-top: 1px solid #dee2e6;">
        <strong>{{ $puedeRendir ? 'ESTUDIANTE HABILITADO PARA RENDIR EXAMEN' : 'ESTUDIANTE INHABILITADO POR EXCESO DE FALTAS' }}</strong>
    </div>
</div>

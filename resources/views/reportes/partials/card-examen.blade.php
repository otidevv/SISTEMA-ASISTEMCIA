@php
    $condicion = $info['condicion'] ?? 'Pendiente';
    $class = $condicion == 'Regular' ? 'success' : ($condicion == 'Amonestado' ? 'warning' : 'danger');
    $puedeRendir = ($info['puede_rendir'] ?? 'NO') == 'SÍ';
    $esProyeccion = $info['es_proyeccion'] ?? false;
@endphp

<div class="exam-card">
    <div class="exam-header {{ $class }}">
        <div class="exam-title-group">
            <span class="exam-icon">
                @if($class == 'success') ✓ @elseif($class == 'warning') ! @else ✕ @endif
            </span>
            <h4>{{ $titulo }} - FECHA: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h4>
        </div>
        <div class="exam-badges">
            @if ($esProyeccion)
                <span class="badge badge-outline">PROYECCIÓN</span>
            @endif
            <span class="dias-info">
                A: <strong>{{ $info['dias_asistidos'] }}</strong> | 
                F: <strong>{{ $info['dias_falta'] }}</strong> | 
                T: <strong>{{ $info['dias_habiles'] }}</strong>
            </span>
        </div>
    </div>

    <div class="exam-body">
        <div class="kpi-grid">
            <div class="kpi-item">
                <span class="kpi-label">Asistencia</span>
                <div class="kpi-value text-success">{{ $info['porcentaje_asistencia'] }}%</div>
            </div>
            <div class="kpi-item">
                <span class="kpi-label">Faltas</span>
                <div class="kpi-value text-danger">{{ $info['porcentaje_falta'] }}%</div>
            </div>
            <div class="kpi-item">
                <span class="kpi-label">Estado</span>
                <div class="kpi-value {{ $class }}">{{ $condicion }}</div>
            </div>
            <div class="kpi-item">
                <span class="kpi-label">Rendir Examen</span>
                <div class="kpi-value {{ $puedeRendir ? 'success' : 'danger' }}">
                    {{ $puedeRendir ? 'SÍ' : 'NO' }}
                </div>
            </div>
        </div>

        <div class="progress-bar-wrapper">
            <div class="progress-bar">
                <div class="progress-fill {{ $class }}" style="width: {{ $info['porcentaje_asistencia'] }}%">
                    {{ $info['porcentaje_asistencia'] }}%
                </div>
            </div>
        </div>
    </div>
    
    <div class="exam-footer {{ $puedeRendir ? 'success' : 'danger' }}">
        <span class="footer-icon">
            @if($puedeRendir) 🗸 @else ⚠ @endif
        </span>
        <strong>{{ $puedeRendir ? 'ESTUDIANTE HABILITADO PARA RENDIR EXAMEN' : 'ESTUDIANTE INHABILITADO POR EXCESO DE FALTAS' }}</strong>
    </div>
</div>

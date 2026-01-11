@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-eye"></i> Monitor de Asistencia Docente en Tiempo Real</h4>
                <div>
                    <span class="badge bg-success" id="status-badge">
                        <i class="fas fa-circle"></i> En línea
                    </span>
                    <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <!-- Estadísticas en tiempo real -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Registros Hoy</h6>
                                    <h4 id="total-hoy">{{ $estadisticasHoy['total_registros'] }}</h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar-day fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Entradas</h6>
                                    <h4 id="total-entradas">{{ $estadisticasHoy['total_entradas'] }}</h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-sign-in-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Salidas</h6>
                                    <h4 id="total-salidas">{{ $estadisticasHoy['total_salidas'] }}</h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-sign-out-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Temas Pendientes</h6>
                                    <h4 id="total-temas-pendientes">{{ $estadisticasHoy['temas_pendientes'] }}</h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs de navegación -->
            <ul class="nav nav-tabs mb-3" id="monitorTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tiempo-real-tab" data-bs-toggle="tab" data-bs-target="#tiempo-real" type="button" role="tab">
                        <i class="fas fa-clock"></i> Tiempo Real
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="horario-dia-tab" data-bs-toggle="tab" data-bs-target="#horario-dia" type="button" role="tab">
                        <i class="fas fa-calendar-alt"></i> Horario del Día
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="temas-pendientes-tab" data-bs-toggle="tab" data-bs-target="#temas-pendientes" type="button" role="tab">
                        <i class="fas fa-list-check"></i> Estado de Clases
                        <span class="badge bg-danger ms-1" id="badge-temas-pendientes">{{ $estadisticasHoy['temas_pendientes'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reporte-diario-tab" data-bs-toggle="tab" data-bs-target="#reporte-diario" type="button" role="tab">
                        <i class="fas fa-file-alt"></i> Reporte Diario
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="estadisticas-tab" data-bs-toggle="tab" data-bs-target="#estadisticas" type="button" role="tab">
                        <i class="fas fa-chart-pie"></i> Estadísticas
                    </button>
                </li>
            </ul>

            <!-- Contenido de tabs -->
            <div class="tab-content" id="monitorTabsContent">
                <!-- Tab 1: Tiempo Real -->
                <div class="tab-pane fade show active" id="tiempo-real" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list"></i> Últimos Registros de Asistencia
                                <small class="text-muted">(Actualización automática cada 5 segundos)</small>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="registros-container">
                                @forelse($ultimasAsistencias as $asistencia)
                                    <div class="registro-item border-bottom py-3" data-id="{{ $asistencia->id }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <div class="d-flex align-items-center">
                                                    @if($asistencia->docente && $asistencia->docente->foto_perfil)
                                                        <img src="{{ asset('storage/' . $asistencia->docente->foto_perfil) }}" 
                                                             class="rounded-circle" width="40" height="40" alt="Foto">
                                                    @else
                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px; color: white; font-size: 14px;">
                                                            {{ $asistencia->docente ? strtoupper(substr($asistencia->docente->nombre, 0, 1)) : 'N/A' }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="fw-bold">
                                                    {{ $asistencia->docente ? $asistencia->docente->nombre . ' ' . $asistencia->docente->apellido_paterno : 'N/A' }}
                                                </div>
                                                <small class="text-muted">{{ $asistencia->docente->numero_documento ?? 'N/A' }}</small>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge bg-{{ $asistencia->estado === 'entrada' ? 'success' : 'secondary' }} fs-6">
                                                    <i class="fas fa-{{ $asistencia->estado === 'entrada' ? 'sign-in-alt' : 'sign-out-alt' }}"></i>
                                                    {{ ucfirst($asistencia->estado) }}
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="fw-bold">{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('H:i:s') }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y') }}</small>
                                            </div>
                                            <div class="col-md-2">
                                                <small class="text-muted">
                                                    {{ $asistencia->horario && $asistencia->horario->curso ? $asistencia->horario->curso->nombre : 'Sin curso' }}
                                                </small>
                                            </div>
                                            <div class="col-md-1">
                                                @if($asistencia->tema_desarrollado)
                                                    <span class="badge bg-success" title="Tema registrado">
                                                        <i class="fas fa-check"></i>
                                                    </span>
                                                @elseif($asistencia->estado === 'salida')
                                                    <span class="badge bg-warning" title="Tema pendiente">
                                                        <i class="fas fa-exclamation"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5" id="no-registros">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No hay registros de asistencia docente para mostrar.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Horario del Día -->
                <div class="tab-pane fade" id="horario-dia" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Horario del Día</h5>
                            <input type="date" id="fecha-horario" class="form-control" style="width: 200px;" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="card-body">
                            <div id="horario-dia-container" class="table-responsive">
                                <p class="text-center text-muted">Cargando horario...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Temas Pendientes -->
                <div class="tab-pane fade" id="temas-pendientes" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-list-check"></i> Estado de Todas las Clases del Día</h5>
                            <button class="btn btn-success btn-sm" onclick="notificarTodosMasivo()">
                                <i class="fab fa-whatsapp"></i> Notificar a Todos
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" id="fecha-temas-pendientes" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-primary" onclick="cargarTemasPendientes()">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                            <div id="temas-pendientes-container">
                                <p class="text-center text-muted">Seleccione una fecha y haga clic en "Buscar"</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Reporte Diario -->
                <div class="tab-pane fade" id="reporte-diario" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Reporte Diario Detallado</h5>
                            <button class="btn btn-success btn-sm" onclick="exportarReporteExcel()" id="btn-exportar-excel">
                                <i class="fas fa-file-excel"></i> Exportar a Excel
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" id="fecha-reporte" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ciclo</label>
                                    <select id="ciclo-reporte" class="form-control">
                                        <option value="">Todos</option>
                                        @if($cicloActivo)
                                            <option value="{{ $cicloActivo->id }}" selected>{{ $cicloActivo->nombre }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Turno</label>
                                    <select id="turno-reporte" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="mañana">Mañana</option>
                                        <option value="tarde">Tarde</option>
                                        <option value="noche">Noche</option>
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-primary mb-3" onclick="cargarReporteDiario()">
                                <i class="fas fa-search"></i> Generar Reporte
                            </button>
                            <div id="reporte-diario-container">
                                <p class="text-center text-muted">Seleccione los filtros y haga clic en "Generar Reporte"</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Estadísticas -->
                <div class="tab-pane fade" id="estadisticas" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Gráficos Estadísticos</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" id="fecha-estadisticas" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-primary" onclick="cargarEstadisticas()">
                                        <i class="fas fa-chart-bar"></i> Cargar Gráficos
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Asistencias vs Faltas</h6>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="chartAsistenciasFaltas" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Estado de Temas Desarrollados</h6>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="chartTemasStatus" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Distribución de Asistencias por Hora</h6>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="chartAsistenciasPorHora" height="100"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .registro-item {
        transition: all 0.3s ease;
    }
    
    .registro-item.nuevo {
        background-color: #d4edda;
        border-left: 4px solid #28a745;
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .nav-tabs .nav-link {
        color: #495057;
    }
    
    .nav-tabs .nav-link.active {
        font-weight: 600;
    }
    
    .btn-whatsapp {
        background-color: #25D366;
        border-color: #25D366;
        color: white;
    }
    
    .btn-whatsapp:hover {
        background-color: #128C7E;
        border-color: #128C7E;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let ultimaConsulta = Math.floor(Date.now() / 1000);
let intervalId;
let activeTab = 'tiempo-real';

// Variables para los gráficos
let chartAsistenciasFaltas = null;
let chartTemasStatus = null;
let chartAsistenciasPorHora = null;

// Detectar cambio de tab
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', function (e) {
        activeTab = e.target.getAttribute('data-bs-target').replace('#', '');
        
        // Cargar datos según el tab activo
        if (activeTab === 'horario-dia') {
            cargarHorarioDia();
        } else if (activeTab === 'temas-pendientes') {
            cargarTemasPendientes();
        } else if (activeTab === 'estadisticas') {
            cargarEstadisticas();
        }
    });
});

// Actualizar registros en tiempo real
function actualizarRegistros() {
    fetch(`{{ route('asistencia-docente.ultimas-procesadas') }}?ultima_consulta=${ultimaConsulta}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('status-badge').innerHTML = '<i class="fas fa-circle"></i> En línea';
                document.getElementById('status-badge').className = 'badge bg-success';
                
                // Actualizar estadísticas si están disponibles
                if (activeTab === 'tiempo-real') {
                    actualizarEstadisticas();
                }
            }
        })
        .catch(error => {
            console.error('Error al actualizar registros:', error);
            document.getElementById('status-badge').innerHTML = '<i class="fas fa-circle"></i> Sin conexión';
            document.getElementById('status-badge').className = 'badge bg-danger';
        });
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    // Recargar la página completa cada 30 segundos para actualizar estadísticas
    // (alternativa: crear endpoint específico para estadísticas)
}

// Cargar horario del día
function cargarHorarioDia() {
    const fecha = document.getElementById('fecha-horario').value;
    const container = document.getElementById('horario-dia-container');
    
    container.innerHTML = '<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando horario...</p>';
    
    fetch(`{{ route('asistencia-docente.monitor.horario-dia') }}?fecha=${fecha}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarHorarioDia(data);
            } else {
                container.innerHTML = '<p class="text-center text-danger">Error al cargar horario</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p class="text-center text-danger">Error al cargar horario</p>';
        });
}

// Mostrar horario del día
function mostrarHorarioDia(data) {
    const container = document.getElementById('horario-dia-container');
    
    if (data.schedule.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No hay clases programadas para este día</p>';
        return;
    }
    
    let html = `
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Docente</th>
                    <th>Curso</th>
                    <th>Aula</th>
                    <th>Estado</th>
                    <th>Tema</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    data.schedule.forEach(clase => {
        const estadoBadge = `<span class="badge bg-${clase.estado_color}">${clase.estado_texto}</span>`;
        const temaBadge = clase.tema_desarrollado 
            ? `<span class="badge bg-success"><i class="fas fa-check"></i> Registrado</span>`
            : (clase.estado === 'tema_pendiente' ? `<span class="badge bg-warning"><i class="fas fa-exclamation"></i> Pendiente</span>` : '-');
        
        const whatsappBtn = clase.docente_telefono && (clase.estado === 'tema_pendiente' || clase.estado === 'falta')
            ? `<button class="btn btn-sm btn-whatsapp" onclick="enviarWhatsApp(${clase.docente_id}, '${clase.estado === 'falta' ? 'falta' : 'tema_pendiente'}', {curso: '${clase.curso}', fecha: '${data.fecha}', hora: '${clase.hora_inicio}'})">
                    <i class="fab fa-whatsapp"></i>
               </button>`
            : '';
        
        html += `
            <tr>
                <td>${clase.hora_inicio} - ${clase.hora_fin}</td>
                <td>${clase.docente_nombre}</td>
                <td>${clase.curso}</td>
                <td>${clase.aula}</td>
                <td>${estadoBadge}</td>
                <td>${temaBadge}</td>
                <td>${whatsappBtn}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Cargar temas pendientes
function cargarTemasPendientes() {
    const fecha = document.getElementById('fecha-temas-pendientes').value;
    const container = document.getElementById('temas-pendientes-container');
    
    container.innerHTML = '<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';
    
    fetch(`{{ route('asistencia-docente.monitor.temas-pendientes') }}?fecha=${fecha}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarTemasPendientes(data);
            } else {
                container.innerHTML = '<p class="text-center text-danger">Error al cargar datos</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p class="text-center text-danger">Error al cargar datos</p>';
        });
}

// Mostrar horario completo del día con estados
function mostrarTemasPendientes(data) {
    const container = document.getElementById('temas-pendientes-container');
    
    if (!data.clases || data.clases.length === 0) {
        container.innerHTML = '<p class="text-center text-muted"><i class="fas fa-calendar-times fa-3x mb-3"></i><br>No hay clases programadas para este día</p>';
        return;
    }
    
    // Mostrar estadísticas
    let html = `
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>Resumen del ${data.fecha}:</strong> 
                    Total: ${data.estadisticas.total} | 
                    <span class="text-success">✓ Completados: ${data.estadisticas.completados}</span> | 
                    <span class="text-warning">⚠ Pendientes: ${data.estadisticas.temas_pendientes}</span> | 
                    <span class="text-info">⏳ En Curso: ${data.estadisticas.en_curso}</span> | 
                    <span class="text-danger">✗ Faltas: ${data.estadisticas.faltas}</span>
                </div>
            </div>
        </div>
    `;
    
    html += '<div class="list-group">';
    
    data.clases.forEach(clase => {
        let whatsappBtn = '';
        
        if (clase.estado === 'tema_pendiente' || clase.estado === 'falta' || clase.estado === 'sin_salida') {
            if (clase.docente_telefono) {
                whatsappBtn = `
                    <button class="btn btn-success btn-sm w-100 mt-2" onclick="enviarWhatsApp(${clase.docente_id}, '${clase.estado === 'falta' ? 'falta' : 'tema_pendiente'}', {curso: '${clase.curso}', fecha: '${clase.fecha}', hora: '${clase.hora_inicio}'})">
                        <i class="fab fa-whatsapp"></i> Notificar
                    </button>`;
            } else {
                whatsappBtn = '<small class="text-muted mt-2 d-block"><i class="fas fa-phone-slash"></i> Sin teléfono</small>';
            }
        }
        
        html += `
            <div class="list-group-item">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-2">
                            <i class="fas fa-user-tie text-primary"></i> 
                            <strong>${clase.docente_nombre}</strong>
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="fas fa-book text-info"></i> 
                                    <strong>Curso:</strong> ${clase.curso}
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-door-open text-success"></i> 
                                    <strong>Aula:</strong> ${clase.aula}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="fas fa-clock text-warning"></i> 
                                    <strong>Horario:</strong> ${clase.horario}
                                </p>
                                ${clase.hora_entrada ? `
                                <p class="mb-1">
                                    <i class="fas fa-sign-in-alt text-success"></i> 
                                    <strong>Entrada:</strong> ${clase.hora_entrada}
                                </p>` : ''}
                                ${clase.hora_salida ? `
                                <p class="mb-1">
                                    <i class="fas fa-sign-out-alt text-danger"></i> 
                                    <strong>Salida:</strong> ${clase.hora_salida}
                                </p>` : ''}
                            </div>
                        </div>
                        ${clase.tiempo_transcurrido ? `
                        <small class="text-muted">
                            <i class="fas fa-history"></i> ${clase.tiempo_transcurrido}
                        </small>` : ''}
                        ${clase.tema_desarrollado ? `
                        <div class="mt-2">
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> <strong>Tema:</strong> ${clase.tema_desarrollado.substring(0, 50)}${clase.tema_desarrollado.length > 50 ? '...' : ''}
                            </small>
                        </div>` : ''}
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-grid gap-2">
                            <span class="badge bg-${clase.estado_color} p-2">
                                <i class="fas fa-${clase.estado_icono}"></i> ${clase.estado_texto}
                            </span>
                            ${whatsappBtn}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Cargar reporte diario
function cargarReporteDiario() {
    const fecha = document.getElementById('fecha-reporte').value;
    const cicloId = document.getElementById('ciclo-reporte').value;
    const turno = document.getElementById('turno-reporte').value;
    const container = document.getElementById('reporte-diario-container');
    
    container.innerHTML = '<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Generando reporte...</p>';
    
    let url = `{{ route('asistencia-docente.monitor.reporte-diario') }}?fecha=${fecha}`;
    if (cicloId) url += `&ciclo_id=${cicloId}`;
    if (turno) url += `&turno=${turno}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarReporteDiario(data);
            } else {
                container.innerHTML = '<p class="text-center text-danger">Error al generar reporte</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p class="text-center text-danger">Error al generar reporte</p>';
        });
}

// Mostrar reporte diario
function mostrarReporteDiario(data) {
    const container = document.getElementById('reporte-diario-container');
    
    let html = `
        <div class="alert alert-info">
            <strong>Resumen del ${data.fecha}:</strong><br>
            Total de clases: ${data.estadisticas.total_clases} | 
            Asistencias: ${data.estadisticas.total_asistencias} | 
            Faltas: ${data.estadisticas.total_faltas} | 
            Temas pendientes: ${data.estadisticas.total_temas_pendientes} | 
            Horas dictadas: ${data.estadisticas.total_horas_dictadas.toFixed(2)}
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Docente</th>
                    <th>Curso</th>
                    <th>Aula</th>
                    <th>Turno</th>
                    <th>Horario</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Horas</th>
                    <th>Estado</th>
                    <th>Tema</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    data.reporte.forEach(registro => {
        html += `
            <tr>
                <td>${registro.docente}</td>
                <td>${registro.curso}</td>
                <td>${registro.aula}</td>
                <td>${registro.turno}</td>
                <td>${registro.hora_inicio} - ${registro.hora_fin}</td>
                <td>${registro.hora_entrada}</td>
                <td>${registro.hora_salida}</td>
                <td>${registro.horas_dictadas}</td>
                <td><span class="badge bg-${registro.estado === 'Asistió' ? 'success' : 'danger'}">${registro.estado}</span></td>
                <td><small>${registro.tema_desarrollado}</small></td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Exportar reporte a Excel
function exportarReporteExcel() {
    const fecha = document.getElementById('fecha-reporte').value;
    const cicloId = document.getElementById('ciclo-reporte').value;
    const turno = document.getElementById('turno-reporte').value;
    
    let url = `{{ route('asistencia-docente.monitor.exportar') }}?fecha=${fecha}`;
    if (cicloId) url += `&ciclo_id=${cicloId}`;
    if (turno) url += `&turno=${turno}`;
    
    window.location.href = url;
}

// Notificar a todos masivamente
function notificarTodosMasivo() {
    if (!confirm('¿Desea abrir WhatsApp para notificar a todos los docentes con temas pendientes?')) {
        return;
    }
    
    fetch(`{{ route('asistencia-docente.monitor.notificar-masivo') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            tipo: 'tema_pendiente'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.docentes.length > 0) {
            alert(`Se abrirán ${data.total} ventanas de WhatsApp`);
            data.docentes.forEach((docente, index) => {
                setTimeout(() => {
                    window.open(docente.link, '_blank');
                }, index * 1000); // 1 segundo entre cada ventana
            });
        } else {
            alert('No hay docentes para notificar');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al generar notificaciones masivas');
    });
}

// Cargar estadísticas y gráficos
function cargarEstadisticas() {
    const fecha = document.getElementById('fecha-estadisticas').value;
    
    fetch(`{{ route('asistencia-docente.monitor.estadisticas') }}?fecha=${fecha}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                crearGraficos(data);
            } else {
                alert('Error al cargar estadísticas');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar estadísticas');
        });
}

// Crear gráficos con Chart.js
function crearGraficos(data) {
    // Destruir gráficos existentes
    if (chartAsistenciasFaltas) chartAsistenciasFaltas.destroy();
    if (chartTemasStatus) chartTemasStatus.destroy();
    if (chartAsistenciasPorHora) chartAsistenciasPorHora.destroy();
    
    // Gráfico de Asistencias vs Faltas (Pie)
    const ctxAsistenciasFaltas = document.getElementById('chartAsistenciasFaltas').getContext('2d');
    chartAsistenciasFaltas = new Chart(ctxAsistenciasFaltas, {
        type: 'pie',
        data: {
            labels: data.asistencias_vs_faltas.labels,
            datasets: [{
                data: data.asistencias_vs_faltas.data,
                backgroundColor: data.asistencias_vs_faltas.colors,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    
    // Gráfico de Temas Status (Doughnut)
    const ctxTemasStatus = document.getElementById('chartTemasStatus').getContext('2d');
    chartTemasStatus = new Chart(ctxTemasStatus, {
        type: 'doughnut',
        data: {
            labels: data.temas_status.labels,
            datasets: [{
                data: data.temas_status.data,
                backgroundColor: data.temas_status.colors,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    
    // Gráfico de Asistencias por Hora (Bar)
    const ctxAsistenciasPorHora = document.getElementById('chartAsistenciasPorHora').getContext('2d');
    chartAsistenciasPorHora = new Chart(ctxAsistenciasPorHora, {
        type: 'bar',
        data: {
            labels: data.asistencias_por_hora.labels.map(h => `${h}:00`),
            datasets: [{
                label: 'Asistencias',
                data: data.asistencias_por_hora.data,
                backgroundColor: '#4472C4',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Enviar WhatsApp
function enviarWhatsApp(docenteId, tipo, data) {
    fetch(`{{ route('asistencia-docente.monitor.whatsapp') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            docente_id: docenteId,
            tipo: tipo,
            data: data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.open(data.link, '_blank');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al generar mensaje de WhatsApp');
    });
}

// Event listeners
document.getElementById('fecha-horario').addEventListener('change', cargarHorarioDia);

// Iniciar actualización automática
document.addEventListener('DOMContentLoaded', function() {
    intervalId = setInterval(actualizarRegistros, 5000); // Cada 5 segundos
});

// Limpiar intervalo al salir
window.addEventListener('beforeunload', function() {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>
@endpush

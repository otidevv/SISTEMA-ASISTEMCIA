
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
                                    <h4 id="total-hoy">{{ $ultimasAsistencias->where('fecha_hora', '>=', now()->startOfDay())->count() }}</h4>
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
                                    <h4 id="total-entradas">{{ $ultimasAsistencias->where('estado', 'entrada')->count() }}</h4>
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
                                    <h4 id="total-salidas">{{ $ultimasAsistencias->where('estado', 'salida')->count() }}</h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-sign-out-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Última Actualización</h6>
                                    <h6 id="ultima-actualizacion">{{ now()->format('H:i:s') }}</h6>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de registros en tiempo real -->
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
                                        <span class="badge bg-{{ $asistencia->tipo_verificacion === 'manual' ? 'warning' : 'info' }}">
                                            {{ ucfirst($asistencia->tipo_verificacion ?? 'manual') }}
                                        </span>
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
    
    .status-online {
        color: #28a745;
    }
    
    .status-offline {
        color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
let ultimaConsulta = Math.floor(Date.now() / 1000);
let intervalId;

function actualizarRegistros() {
    fetch(`{{ route('asistencia-docente.ultimas-procesadas') }}?ultima_consulta=${ultimaConsulta}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar badge de estado
                document.getElementById('status-badge').innerHTML = '<i class="fas fa-circle"></i> En línea';
                document.getElementById('status-badge').className = 'badge bg-success';
                
                // Actualizar timestamp
                ultimaConsulta = data.hora_actual;
                document.getElementById('ultima-actualizacion').textContent = new Date().toLocaleTimeString();
                
                if (data.tiene_nuevos && data.registros.length > 0) {
                    // Agregar nuevos registros al inicio
                    const container = document.getElementById('registros-container');
                    const noRegistros = document.getElementById('no-registros');
                    
                    if (noRegistros) {
                        noRegistros.remove();
                    }
                    
                    data.registros.forEach(registro => {
                        const nuevoElemento = crearElementoRegistro(registro);
                        container.insertBefore(nuevoElemento, container.firstChild);
                        
                        // Actualizar estadísticas
                        actualizarEstadisticas(registro);
                    });
                    
                    // Limitar a 20 registros máximo
                    const registros = container.querySelectorAll('.registro-item');
                    if (registros.length > 20) {
                        for (let i = 20; i < registros.length; i++) {
                            registros[i].remove();
                        }
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error al actualizar registros:', error);
            // Actualizar badge de estado a offline
            document.getElementById('status-badge').innerHTML = '<i class="fas fa-circle"></i> Sin conexión';
            document.getElementById('status-badge').className = 'badge bg-danger';
        });
}

function crearElementoRegistro(registro) {
    const div = document.createElement('div');
    div.className = 'registro-item border-bottom py-3 nuevo';
    div.setAttribute('data-id', registro.id);
    
    const estadoBadgeClass = registro.estado === 'entrada' ? 'success' : 'secondary';
    const estadoIcon = registro.estado === 'entrada' ? 'sign-in-alt' : 'sign-out-alt';
    const tipoBadgeClass = registro.tipo_verificacion === 'manual' ? 'warning' : 'info';
    
    div.innerHTML = `
        <div class="row align-items-center">
            <div class="col-md-2">
                <div class="d-flex align-items-center">
                    ${registro.foto_url ? 
                        `<img src="${registro.foto_url}" class="rounded-circle" width="40" height="40" alt="Foto">` :
                        `<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px; color: white; font-size: 14px;">
                            ${registro.iniciales}
                        </div>`
                    }
                </div>
            </div>
            <div class="col-md-3">
                <div class="fw-bold">${registro.docente_nombre}</div>
                <small class="text-muted">${registro.numero_documento}</small>
            </div>
            <div class="col-md-2">
                <span class="badge bg-${estadoBadgeClass} fs-6">
                    <i class="fas fa-${estadoIcon}"></i>
                    ${registro.estado.charAt(0).toUpperCase() + registro.estado.slice(1)}
                </span>
            </div>
            <div class="col-md-2">
                <div class="fw-bold">${new Date(registro.fecha_hora_formateada.split(' ')[1]).toLocaleTimeString()}</div>
                <small class="text-muted">${registro.fecha_hora_formateada.split(' ')[0]}</small>
            </div>
            <div class="col-md-2">
                <small class="text-muted">${registro.curso || 'Sin curso'}</small>
            </div>
            <div class="col-md-1">
                <span class="badge bg-${tipoBadgeClass}">
                    ${registro.tipo_verificacion.charAt(0).toUpperCase() + registro.tipo_verificacion.slice(1)}
                </span>
            </div>
        </div>
    `;
    
    // Remover clase 'nuevo' después de 3 segundos
    setTimeout(() => {
        div.classList.remove('nuevo');
    }, 3000);
    
    return div;
}

function actualizarEstadisticas(registro) {
    // Actualizar total hoy
    const totalHoy = document.getElementById('total-hoy');
    totalHoy.textContent = parseInt(totalHoy.textContent) + 1;
    
    // Actualizar entradas/salidas
    if (registro.estado === 'entrada') {
        const totalEntradas = document.getElementById('total-entradas');
        totalEntradas.textContent = parseInt(totalEntradas.textContent) + 1;
    } else {
        const totalSalidas = document.getElementById('total-salidas');
        totalSalidas.textContent = parseInt(totalSalidas.textContent) + 1;
    }
}

// Iniciar actualización automática
document.addEventListener('DOMContentLoaded', function() {
    intervalId = setInterval(actualizarRegistros, 5000); // Cada 5 segundos
});

// Limpiar intervalo al salir de la página
window.addEventListener('beforeunload', function() {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>
@endpush

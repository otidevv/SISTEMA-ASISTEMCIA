@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-download"></i> Exportar Asistencia Docente</h4>
        <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Configurar Exportación</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('asistencia-docente.exportar.action') }}" method="POST">
                        @csrf

                        {{-- Rango de fechas --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="fecha_desde" class="form-label">Fecha Desde <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('fecha_desde') is-invalid @enderror" 
                                       name="fecha_desde" id="fecha_desde" value="{{ old('fecha_desde') }}" required>
                                @error('fecha_desde')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_hasta" class="form-label">Fecha Hasta <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('fecha_hasta') is-invalid @enderror" 
                                       name="fecha_hasta" id="fecha_hasta" value="{{ old('fecha_hasta') }}" required>
                                @error('fecha_hasta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Filtros adicionales --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="docente_id" class="form-label">Docente Específico</label>
                                <select class="form-select @error('docente_id') is-invalid @enderror" 
                                        name="docente_id" id="docente_id">
                                    <option value="">Todos los docentes</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('docente_id') == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->nombre }} {{ $docente->apellido_paterno }} - {{ $docente->numero_documento }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('docente_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select @error('estado') is-invalid @enderror" 
                                        name="estado" id="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="entrada" {{ old('estado') == 'entrada' ? 'selected' : '' }}>Solo Entradas</option>
                                    <option value="salida" {{ old('estado') == 'salida' ? 'selected' : '' }}>Solo Salidas</option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Formato de exportación --}}
                        <div class="mb-4">
                            <label class="form-label">Formato de Exportación <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="formato" 
                                               id="formato_excel" value="excel" {{ old('formato', 'excel') == 'excel' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="formato_excel">
                                            <i class="fas fa-file-excel text-success"></i> Excel (.xlsx)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="formato" 
                                               id="formato_csv" value="csv" {{ old('formato') == 'csv' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="formato_csv">
                                            <i class="fas fa-file-csv text-info"></i> CSV (.csv)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="formato" 
                                               id="formato_pdf" value="pdf" {{ old('formato') == 'pdf' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="formato_pdf">
                                            <i class="fas fa-file-pdf text-danger"></i> PDF (.pdf)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('formato')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Columnas a incluir --}}
                        <div class="mb-4">
                            <label class="form-label">Columnas a Incluir</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columnas[]" 
                                               value="docente" id="col_docente" checked>
                                        <label class="form-check-label" for="col_docente">
                                            Información del Docente
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columnas[]" 
                                               value="fecha_hora" id="col_fecha_hora" checked>
                                        <label class="form-check-label" for="col_fecha_hora">
                                            Fecha y Hora
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columnas[]" 
                                               value="estado" id="col_estado" checked>
                                        <label class="form-check-label" for="col_estado">
                                            Estado (Entrada/Salida)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columnas[]" 
                                               value="curso" id="col_curso" checked>
                                        <label class="form-check-label" for="col_curso">
                                            Curso
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columnas[]" 
                                               value="tipo_verificacion" id="col_tipo">
                                        <label class="form-check-label" for="col_tipo">
                                            Tipo de Verificación
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columnas[]" 
                                               value="terminal_id" id="col_terminal">
                                        <label class="form-check-label" for="col_terminal">
                                            Terminal ID
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columnas[]" 
                                               value="horas_dictadas" id="col_horas">
                                        <label class="form-check-label" for="col_horas">
                                            Horas Dictadas
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="columnas[]" 
                                               value="monto_total" id="col_monto">
                                        <label class="form-check-label" for="col_monto">
                                            Monto Total
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Opciones adicionales --}}
                        <div class="mb-4">
                            <label class="form-label">Opciones Adicionales</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="incluir_resumen" 
                                       id="incluir_resumen" value="1" {{ old('incluir_resumen') ? 'checked' : '' }}>
                                <label class="form-check-label" for="incluir_resumen">
                                    Incluir hoja de resumen con estadísticas
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="agrupar_por_docente" 
                                       id="agrupar_por_docente" value="1" {{ old('agrupar_por_docente') ? 'checked' : '' }}>
                                <label class="form-check-label" for="agrupar_por_docente">
                                    Agrupar registros por docente
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-download"></i> Exportar Datos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Información y ayuda --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información</h6>
                </div>
                <div class="card-body">
                    <h6>Formatos Disponibles:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-file-excel text-success"></i> <strong>Excel:</strong> Ideal para análisis de datos</li>
                        <li><i class="fas fa-file-csv text-info"></i> <strong>CSV:</strong> Compatible con cualquier aplicación</li>
                        <li><i class="fas fa-file-pdf text-danger"></i> <strong>PDF:</strong> Para reportes e impresión</li>
                    </ul>

                    <hr>

                    <h6>Consejos:</h6>
                    <ul class="small">
                        <li>Seleccione un rango de fechas específico para mejores resultados</li>
                        <li>Use filtros para exportar solo los datos que necesita</li>
                        <li>El formato Excel incluye fórmulas y formato</li>
                        <li>El PDF es ideal para presentaciones</li>
                    </ul>
                </div>
            </div>

            {{-- Estadísticas rápidas --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Estadísticas Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-0">{{ \App\Models\AsistenciaDocente::whereDate('fecha_hora', today())->count() }}</h4>
                                <small class="text-muted">Hoy</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-0">{{ \App\Models\AsistenciaDocente::whereMonth('fecha_hora', now()->month)->count() }}</h4>
                            <small class="text-muted">Este Mes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Establecer fechas por defecto (último mes)
        const fechaDesde = document.getElementById('fecha_desde');
        const fechaHasta = document.getElementById('fecha_hasta');
        
        if (!fechaDesde.value) {
            const hace30Dias = new Date();
            hace30Dias.setDate(hace30Dias.getDate() - 30);
            fechaDesde.value = hace30Dias.toISOString().split('T')[0];
        }
        
        if (!fechaHasta.value) {
            const hoy = new Date();
            fechaHasta.value = hoy.toISOString().split('T')[0];
        }

        // Validar que fecha_hasta sea mayor que fecha_desde
        fechaDesde.addEventListener('change', function() {
            fechaHasta.min = this.value;
        });

        fechaHasta.addEventListener('change', function() {
            fechaDesde.max = this.value;
        });

        // Seleccionar/deseleccionar todas las columnas
        const selectAllBtn = document.createElement('button');
        selectAllBtn.type = 'button';
        selectAllBtn.className = 'btn btn-sm btn-outline-secondary mb-2';
        selectAllBtn.innerHTML = '<i class="fas fa-check-square"></i> Seleccionar Todo';
        
        const columnasLabel = document.querySelector('label[for="col_docente"]').closest('.mb-4').querySelector('.form-label');
        columnasLabel.appendChild(selectAllBtn);

        selectAllBtn.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="columnas[]"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => cb.checked = !allChecked);
            
            this.innerHTML = allChecked ? 
                '<i class="fas fa-check-square"></i> Seleccionar Todo' : 
                '<i class="fas fa-square"></i> Deseleccionar Todo';
        });
    });
</script>
@endpush

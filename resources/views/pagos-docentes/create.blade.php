@extends('layouts.app')

@section('title', 'Registrar Nuevo Pago')

@push('css')
<style>
    /* Paleta de colores y variables */
    :root {
        --primary-gradient: linear-gradient(135deg, #4f32c2 0%, #7367f0 100%);
        --primary-glow: 0 0 20px rgba(115, 103, 240, 0.4);
    }
    
    /* Estilo del botón principal */
    .btn-primary-gradient {
        background-image: var(--primary-gradient);
        border: none;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(115, 103, 240, 0.5);
    }
    .btn-primary-gradient:hover {
        transform: translateY(-2px);
        box-shadow: var(--primary-glow);
        color: white;
    }

    /* Estilos para validación visual */
    .form-control.is-valid, .form-select.is-valid {
        border-color: var(--bs-success);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: var(--bs-danger);
    }

    /* Feedback de validación */
    .valid-feedback, .invalid-feedback {
        display: block;
        margin-top: .25rem;
        font-size: .875em;
    }

    /* Sidebar cards */
    .card-sidebar .card-header {
        background: linear-gradient(135deg, #f8f9fe 0%, #f1f3f9 100%);
        border-bottom: 1px solid #eef2f7;
    }

    /* Indicador de Tarifa Automática */
    #tarifaIndicator {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%) scale(0.8);
        background: var(--bs-success);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        opacity: 0;
        transition: all 0.3s ease;
        pointer-events: none;
    }
    #tarifaIndicator.show {
        opacity: 1;
        transform: translateY(-50%) scale(1);
    }

</style>
@endpush

@section('content')
<div class="container-fluid">

    <!-- Título de la página y breadcrumbs -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('pagos-docentes.index') }}">Gestión de Pagos</a></li>
                        <li class="breadcrumb-item active">Nuevo Registro</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-cash-plus me-1"></i>
                    Registrar Nuevo Pago
                </h4>
            </div>
        </div>
    </div>
    <!-- fin del título de la página -->

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-file-document-edit-outline me-2"></i>
                        Formulario de Pago
                    </h4>
                    <p class="text-muted mb-0">Complete la información para establecer la remuneración del docente.</p>
                </div>
                <div class="card-body">

                    <!-- Alertas de Error -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h5 class="alert-heading"><i class="mdi mdi-alert-circle-outline me-2"></i>Se encontraron errores</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Formulario Principal -->
                    <form action="{{ route('pagos-docentes.store') }}" method="POST" id="paymentForm" novalidate>
                        @csrf

                        <!-- Selección de Docente -->
                        <div class="mb-3">
                            <label for="docente_id" class="form-label">
                                <i class="mdi mdi-account-circle-outline me-1"></i>Docente Asignado <span class="text-danger">*</span>
                            </label>
                            <select name="docente_id" id="docente_id" class="form-select" required>
                                <option value="" disabled selected>Seleccione un docente...</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id }}" {{ old('docente_id') == $docente->id ? 'selected' : '' }}>
                                        {{ $docente->nombre }} {{ $docente->apellido_paterno }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Por favor, seleccione un docente.</div>
                        </div>

                        <!-- Selección de Ciclo -->
                        <div class="mb-3">
                            <label for="ciclo_id" class="form-label">
                                <i class="mdi mdi-calendar-sync-outline me-1"></i>Ciclo Académico <span class="text-danger">*</span>
                            </label>
                            <select name="ciclo_id" id="ciclo_id" class="form-select" required>
                                <option value="" disabled selected>Seleccione un ciclo académico...</option>
                                @foreach($ciclos as $ciclo)
                                    <option value="{{ $ciclo->id }}" {{ old('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }} ({{ $ciclo->fecha_inicio }} - {{ $ciclo->fecha_fin }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Por favor, seleccione un ciclo académico.</div>
                        </div>

                        <!-- Tarifa por Hora -->
                        <div class="mb-3">
                            <label for="tarifa_por_hora" class="form-label">
                                <i class="mdi mdi-cash me-1"></i>Tarifa por Hora <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" 
                                       step="0.01" 
                                       name="tarifa_por_hora" 
                                       id="tarifa_por_hora" 
                                       class="form-control" 
                                       value="{{ old('tarifa_por_hora') }}" 
                                       placeholder="0.00"
                                       min="0.01"
                                       max="999.99"
                                       required>
                                <div id="tarifaIndicator">
                                    <i class="mdi mdi-check me-1"></i>Auto
                                </div>
                                <div class="invalid-feedback">La tarifa debe ser un valor positivo.</div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary-gradient" id="submitBtn">
                                <i class="mdi mdi-content-save-outline me-1"></i>
                                Guardar Pago
                            </button>
                            <a href="{{ route('pagos-docentes.index') }}" class="btn btn-secondary ms-2">
                                <i class="mdi mdi-cancel me-1"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar de Ayuda -->
        <div class="col-lg-4">
            <div class="card card-sidebar">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-information-outline me-2"></i>
                        Instrucciones
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Siga estos pasos para registrar correctamente un nuevo pago:
                    </p>
                    <ol class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent">
                            <i class="mdi mdi-numeric-1-circle-outline text-primary me-2"></i>
                            <strong>Seleccione el Docente:</strong> Elija el docente al que se le asignará el pago de la lista desplegable.
                        </li>
                        <li class="list-group-item bg-transparent mt-2">
                            <i class="mdi mdi-numeric-2-circle-outline text-primary me-2"></i>
                            <strong>Elija el Ciclo Académico:</strong> Seleccione el ciclo correspondiente. El sistema intentará autocompletar la tarifa basada en registros anteriores.
                        </li>
                        <li class="list-group-item bg-transparent mt-2">
                            <i class="mdi mdi-numeric-3-circle-outline text-primary me-2"></i>
                            <strong>Establezca la Tarifa:</strong> Ingrese el monto que se pagará por hora. Si el sistema autocompleta la tarifa, puede modificarla si es necesario.
                        </li>
                    </ol>
                    <div class="alert alert-info mt-3" role="alert">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        Una vez guardado, el pago se considerará <strong>activo</strong> hasta que se le asigne una fecha de finalización.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentForm');
    const submitBtn = document.getElementById('submitBtn');
    const docenteSelect = document.getElementById('docente_id');
    const cicloSelect = document.getElementById('ciclo_id');
    const tarifaInput = document.getElementById('tarifa_por_hora');
    const tarifaIndicator = document.getElementById('tarifaIndicator');

    const fields = [docenteSelect, cicloSelect, tarifaInput];

    // Función para obtener tarifa automáticamente
    function fetchTarifa() {
        const cicloId = cicloSelect.value;
        const docenteId = docenteSelect.value;

        if (!cicloId || !docenteId) {
            tarifaInput.value = '';
            validateField(tarifaInput);
            return;
        }

        tarifaInput.disabled = true;
        
        fetch(`/pagos-docentes/tarifa/${docenteId}/${cicloId}`)
            .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok.'))
            .then(data => {
                if (data.tarifa_por_hora) {
                    tarifaInput.value = data.tarifa_por_hora;
                    tarifaIndicator.classList.add('show');
                    setTimeout(() => tarifaIndicator.classList.remove('show'), 2000);
                }
            })
            .catch(error => console.error('Error al obtener tarifa:', error))
            .finally(() => {
                tarifaInput.disabled = false;
                validateField(tarifaInput);
            });
    }

    // Validación de un campo
    function validateField(field) {
        let isValid = false;
        if (field.type === 'number') {
            isValid = field.value && parseFloat(field.value) > 0;
        } else {
            isValid = field.value.trim() !== '';
        }

        if (isValid) {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        } else {
            field.classList.remove('is-valid');
            // No agregamos is-invalid aquí para no mostrar el error antes del submit
        }
        return isValid;
    }

    // Event listeners para validación en tiempo real
    fields.forEach(field => {
        field.addEventListener('input', () => validateField(field));
    });

    docenteSelect.addEventListener('change', fetchTarifa);
    cicloSelect.addEventListener('change', fetchTarifa);

    // Validación del formulario al enviar
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let isFormValid = true;

        fields.forEach(field => {
            if (!validateField(field)) {
                field.classList.add('is-invalid'); // Ahora sí mostramos el error
                isFormValid = false;
            }
        });

        if (isFormValid) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Guardando...`;
            form.submit();
        } else {
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
});
</script>
@endpush

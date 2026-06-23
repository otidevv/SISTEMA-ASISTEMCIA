@extends('layouts.app')

@section('title', 'Nueva Solicitud')

@php
    $tiposData = $tipos->keyBy('id')->map(fn ($t) => [
        'campos' => $t->campos ?? [],
        'requiere_pago' => (bool) $t->requiere_pago,
        'requiere_adjunto' => (bool) $t->requiere_adjunto,
        'permite_adjuntos' => (bool) $t->permite_adjuntos,
        'codigo_tusne' => optional($t->concepto)->codigo,
    ]);
    $carrerasData = $carreras->map(fn ($c) => ['id' => $c->id, 'nombre' => $c->nombre])->values();
    $turnosData = $turnos->map(fn ($t) => ['id' => $t->id, 'nombre' => $t->nombre])->values();
@endphp

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="mdi mdi-file-plus-outline me-2"></i>Nueva Solicitud</h4>
        <a href="{{ route('solicitudes.index') }}" class="btn btn-secondary btn-sm"><i class="mdi mdi-arrow-left"></i> Volver</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Paso 1: Estudiante --}}
        <div class="card mb-3">
            <div class="card-header"><i class="mdi mdi-account-outline me-1"></i>1. Estudiante</div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">DNI</label>
                        <input type="text" id="dniInput" name="numero_documento" class="form-control"
                               value="{{ auth()->user()->numero_documento }}"
                               {{ $puedeOtros ? '' : 'readonly' }}>
                        @if($puedeOtros)<small class="text-muted">Puedes consultar otro DNI para registrar en su nombre.</small>@endif
                    </div>
                    <div class="col-md-8">
                        <div id="infoEstudiante" class="alert alert-light border mb-0 py-2">
                            <span class="text-muted">Ingresa el DNI para cargar los datos del estudiante…</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Paso 2: Tipo de trámite --}}
        <div class="card mb-3">
            <div class="card-header"><i class="mdi mdi-format-list-bulleted-type me-1"></i>2. Tipo de trámite</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Selecciona el trámite</label>
                        <select name="solicitud_tipo_id" id="tipoSelect" class="form-select" required>
                            <option value="">— Seleccione —</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                        <small id="tipoInfo" class="text-info"></small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Paso 3: Datos del trámite (dinámico) --}}
        <div class="card mb-3" id="cardDatos" style="display:none;">
            <div class="card-header"><i class="mdi mdi-pencil-outline me-1"></i>3. Datos del trámite</div>
            <div class="card-body">
                <div id="camposDinamicos" class="row g-3"></div>

                {{-- Voucher --}}
                <div id="bloquePago" class="row g-3 mt-1" style="display:none;">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">N° de Voucher de pago</label>
                        <input type="text" name="serial_voucher" id="voucherInput" class="form-control" placeholder="Se detecta automáticamente al ingresar el DNI">
                        <div id="pagoStatus" class="small mt-1"></div>
                    </div>
                </div>

                {{-- Evidencias --}}
                <div id="bloqueAdjunto" class="row g-3 mt-1" style="display:none;">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Evidencia / Documento <span id="adjReq" class="text-danger" style="display:none;">*</span></label>
                        <input type="file" name="adjuntos[]" id="adjInput" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">PDF o imagen, máx. 5 MB c/u. Puedes seleccionar varios.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" id="btnEnviar" class="btn btn-primary"><i class="mdi mdi-send"></i> Enviar solicitud</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    @php /* datos para el formulario inteligente */ @endphp
    const TIPOS = @json($tiposData);
    const CARRERAS = @json($carrerasData);
    const TURNOS = @json($turnosData);
    const URL_INFO = "{{ route('solicitudes.estudiante-info') }}";
    const URL_PAGOS = "{{ route('solicitudes.pagos-disponibles') }}";

    let estudiante = { nombre: '', carrera_id: null, carrera_nombre: '', turno_id: null, turno_nombre: '' };

    const cont = document.getElementById('camposDinamicos');
    const cardDatos = document.getElementById('cardDatos');
    const bloquePago = document.getElementById('bloquePago');
    const bloqueAdjunto = document.getElementById('bloqueAdjunto');
    const adjInput = document.getElementById('adjInput');
    const adjReq = document.getElementById('adjReq');
    const tipoInfo = document.getElementById('tipoInfo');
    const dniInput = document.getElementById('dniInput');
    const infoBox = document.getElementById('infoEstudiante');
    const voucherInput = document.getElementById('voucherInput');
    const pagoStatus = document.getElementById('pagoStatus');
    const btnEnviar = document.getElementById('btnEnviar');
    let pagoOk = true;

    // ---- Cargar info del estudiante por DNI ----
    function cargarEstudiante() {
        const dni = dniInput.value.trim();
        if (dni.length < 6) return;
        infoBox.innerHTML = '<span class="text-muted"><span class="spinner-border spinner-border-sm"></span> Cargando…</span>';
        fetch(URL_INFO + '?dni=' + encodeURIComponent(dni))
            .then(r => r.json())
            .then(d => {
                if (!d.encontrado) {
                    estudiante = { nombre: '', carrera_id: null, carrera_nombre: '', turno_id: null, turno_nombre: '' };
                    infoBox.innerHTML = '<span class="text-danger"><i class="mdi mdi-alert"></i> No se encontró un estudiante con ese DNI.</span>';
                    return;
                }
                estudiante = d;
                infoBox.innerHTML =
                    '<b>' + (d.nombre || '—') + '</b><br>' +
                    '<small><i class="mdi mdi-school-outline"></i> Carrera: <b>' + (d.carrera_nombre || 'No registrada') + '</b> ' +
                    '&nbsp;·&nbsp; <i class="mdi mdi-clock-outline"></i> Turno: <b>' + (d.turno_nombre || 'No registrado') + '</b></small>';
                // Si ya hay un tipo elegido, re-render para prefijar carrera/turno actual
                if (tipoSelect.value) render(tipoSelect.value);
            })
            .catch(() => infoBox.innerHTML = '<span class="text-danger">Error al consultar.</span>');
    }
    dniInput.addEventListener('change', cargarEstudiante);
    dniInput.addEventListener('blur', cargarEstudiante);

    // ---- Render dinámico de campos según el tipo ----
    function opcionesSelect(source) {
        const lista = source === 'carreras' ? CARRERAS : (source === 'turnos' ? TURNOS : []);
        return lista.map(o => `<option value="${o.nombre}">${o.nombre}</option>`).join('');
    }

    function render(tipoId) {
        cont.innerHTML = '';
        cardDatos.style.display = 'none';
        bloquePago.style.display = 'none';
        bloqueAdjunto.style.display = 'none';
        adjReq.style.display = 'none';
        adjInput.required = false;
        tipoInfo.textContent = '';
        const t = TIPOS[tipoId];
        if (!t) return;

        cardDatos.style.display = '';

        (t.campos || []).forEach(c => {
            const req = c.required ? 'required' : '';
            const star = c.required ? ' <span class="text-danger">*</span>' : '';
            let field = '';

            if (c.type === 'textarea') {
                field = `<textarea name="campo_${c.name}" class="form-control" rows="2" ${req}></textarea>`;
            } else if (c.type === 'select') {
                field = `<select name="campo_${c.name}" class="form-select" ${req}>
                            <option value="">— Seleccione —</option>${opcionesSelect(c.source)}
                         </select>`;
            } else if (c.type === 'current') {
                const val = c.source === 'carrera' ? (estudiante.carrera_nombre || '') : (estudiante.turno_nombre || '');
                field = `<input type="text" name="campo_${c.name}" class="form-control bg-light" value="${val}" readonly>`;
            } else if (c.type === 'dates') {
                field = `
                    <div class="input-group mb-2">
                        <input type="date" id="fechaPick" class="form-control">
                        <button type="button" class="btn btn-outline-primary" id="btnAddFecha"><i class="mdi mdi-plus"></i> Agregar</button>
                    </div>
                    <div id="fechasChips" class="mb-1"></div>
                    <input type="hidden" name="fechas" id="fechasHidden">
                    <small class="text-muted">Agrega cada día que faltaste.</small>`;
            } else {
                field = `<input type="text" name="campo_${c.name}" class="form-control" ${req}>`;
            }

            cont.insertAdjacentHTML('beforeend',
                `<div class="col-md-6"><label class="form-label fw-bold">${c.label || c.name}${star}</label>${field}</div>`);
        });

        wireFechas();

        if (t.requiere_pago) bloquePago.style.display = '';
        if (t.permite_adjuntos) bloqueAdjunto.style.display = '';
        if (t.requiere_adjunto) {
            adjReq.style.display = '';
            adjInput.required = true;
            tipoInfo.textContent = 'Este trámite requiere adjuntar al menos un documento.';
        }

        checkPagos();
    }

    // ---- Detección automática de pago + bloqueo del botón ----
    function actualizarSubmit() {
        btnEnviar.disabled = !pagoOk;
    }

    function checkPagos() {
        const t = TIPOS[tipoSelect.value];
        if (!t) { pagoOk = true; actualizarSubmit(); return; }

        if (!t.requiere_pago) { pagoOk = true; pagoStatus.innerHTML = ''; actualizarSubmit(); return; }

        const dni = dniInput.value.trim();
        const codigo = t.codigo_tusne || '';
        if (!dni || !codigo) {
            pagoOk = false; actualizarSubmit();
            pagoStatus.innerHTML = '<span class="text-muted">Ingresa el DNI para verificar el pago.</span>';
            return;
        }

        pagoOk = false; actualizarSubmit();
        pagoStatus.innerHTML = '<span class="text-muted"><span class="spinner-border spinner-border-sm"></span> Verificando pago…</span>';

        fetch(URL_PAGOS + '?dni=' + encodeURIComponent(dni) + '&codigo=' + encodeURIComponent(codigo))
            .then(r => r.json())
            .then(d => {
                if (!d.ok) {
                    pagoOk = true; // API no disponible → no bloquear
                    pagoStatus.innerHTML = '<span class="text-warning"><i class="mdi mdi-alert"></i> No se pudo verificar el pago automáticamente. Podrás registrar el voucher luego.</span>';
                } else if (d.pagos && d.pagos.length) {
                    const p = d.pagos[0];
                    voucherInput.value = p.serial;
                    pagoOk = true;
                    const extra = d.pagos.length > 1 ? ' (+' + (d.pagos.length - 1) + ' más)' : '';
                    pagoStatus.innerHTML = '<span class="text-success"><i class="mdi mdi-check-circle"></i> Pago detectado: <b>' + p.serial + '</b> — S/ ' + (p.monto ?? '') + extra + '</span>';
                } else {
                    voucherInput.value = '';
                    pagoOk = false;
                    pagoStatus.innerHTML = '<span class="text-danger"><i class="mdi mdi-close-circle"></i> No se encontró un pago para este trámite con este DNI. Realiza el pago en caja para continuar.</span>';
                }
                actualizarSubmit();
            })
            .catch(() => {
                pagoOk = true;
                pagoStatus.innerHTML = '<span class="text-warning">No se pudo verificar el pago.</span>';
                actualizarSubmit();
            });
    }

    // ---- Widget de fechas (calendario + chips) ----
    function wireFechas() {
        const pick = document.getElementById('fechaPick');
        const btn = document.getElementById('btnAddFecha');
        const chips = document.getElementById('fechasChips');
        const hidden = document.getElementById('fechasHidden');
        if (!pick || !btn) return;
        let fechas = [];

        function pintar() {
            chips.innerHTML = fechas.map((f, i) =>
                `<span class="badge bg-primary me-1 mb-1">${f} <a href="#" data-i="${i}" class="text-white text-decoration-none quitar">&times;</a></span>`
            ).join('');
            hidden.value = fechas.join(',');
        }
        btn.addEventListener('click', () => {
            if (pick.value && !fechas.includes(pick.value)) { fechas.push(pick.value); fechas.sort(); pintar(); }
            pick.value = '';
        });
        chips.addEventListener('click', e => {
            const a = e.target.closest('.quitar');
            if (!a) return;
            e.preventDefault();
            fechas.splice(parseInt(a.dataset.i), 1);
            pintar();
        });
    }

    const tipoSelect = document.getElementById('tipoSelect');
    tipoSelect.addEventListener('change', e => render(e.target.value));

    // Cargar datos del estudiante al abrir (DNI propio)
    cargarEstudiante();
</script>
@endpush

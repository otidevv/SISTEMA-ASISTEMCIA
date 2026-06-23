@extends('layouts.app')

@section('title', 'Expediente ' . $solicitud->codigo)

@php
    $u = auth()->user();
    $canApprove = $u->hasPermission('solicitudes.approve');
    $canAtender = $u->hasPermission('solicitudes.atender');
    $canManage = $u->hasPermission('solicitudes.manage');
    $esDueno = $solicitud->user_id === $u->id;
    $estadoFinal = in_array($solicitud->estado, ['atendida', 'rechazada']);
    $puedeAdjuntar = ($esDueno || $canManage || $canApprove || $canAtender) && !$estadoFinal;
    $puedeBorrarAdjunto = $esDueno || $canManage;
    $necesitaPago = optional($solicitud->tipo)->requiere_pago && !$solicitud->pago_validado;
    $puedePagar = $necesitaPago && ($esDueno || $canManage || $canApprove) && !$estadoFinal;

    $niveles = ['pendiente_pago' => 0, 'enviada' => 1, 'en_revision' => 1, 'aprobada' => 2, 'derivada' => 3, 'atendida' => 4];
    $nivel = $niveles[$solicitud->estado] ?? 1;
    $pasos = ['Enviada', 'V°B° Director', 'En atención', 'Atendida'];
@endphp

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="mdi mdi-file-document-outline me-2"></i>Expediente {{ $solicitud->codigo }}</h4>
        <div>
            <a href="{{ route('solicitudes.comprobante-pdf', $solicitud->id) }}" class="btn btn-outline-success btn-sm" target="_blank"><i class="mdi mdi-receipt-text-outline"></i> Comprobante</a>
            <a href="{{ route('solicitudes.seguimiento-pdf', $solicitud->id) }}" class="btn btn-outline-primary btn-sm" target="_blank"><i class="mdi mdi-file-pdf-box"></i> Constancia de seguimiento</a>
            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm"><i class="mdi mdi-arrow-left"></i> Volver</a>
        </div>
    </div>

    @if(session('comprobante'))
        <div class="alert alert-success d-flex justify-content-between align-items-center">
            <div><i class="mdi mdi-check-circle me-1"></i> ¡Tu solicitud fue registrada! Descarga tu comprobante de recepción.</div>
            <a href="{{ route('solicitudes.comprobante-pdf', session('comprobante')) }}" class="btn btn-success btn-sm" target="_blank">
                <i class="mdi mdi-receipt-text-outline"></i> Descargar comprobante
            </a>
        </div>
    @elseif(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    @if($solicitud->estado === 'observada')
        <div class="alert alert-warning"><b>Observado:</b> {{ $solicitud->observacion }}</div>
    @elseif($solicitud->estado === 'rechazada')
        <div class="alert alert-danger"><b>Rechazado:</b> {{ $solicitud->observacion }}</div>
    @endif

    {{-- Seguimiento (stepper) --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between text-center">
                @foreach($pasos as $i => $paso)
                    @php $idx = $i + 1; $on = $nivel >= $idx; @endphp
                    <div class="flex-fill">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center
                            {{ $on ? 'bg-primary text-white' : 'bg-light text-muted' }}" style="width:38px;height:38px;">
                            <i class="mdi {{ $on ? 'mdi-check' : 'mdi-circle-small' }}"></i>
                        </div>
                        <small class="{{ $on ? 'fw-bold' : 'text-muted' }}">{{ $paso }}</small>
                    </div>
                    @if(!$loop->last)<div class="align-self-center flex-fill"><hr class="{{ $nivel > $idx ? 'border-primary' : '' }}"></div>@endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            {{-- Datos del trámite --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="mdi mdi-information-outline me-1"></i>Datos del trámite</span>
                    @include('solicitudes.partials.estado', ['estado' => $solicitud->estado])
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Trámite</dt><dd class="col-sm-8">{{ $solicitud->tipo->nombre ?? '—' }}</dd>
                        <dt class="col-sm-4">Solicitante</dt><dd class="col-sm-8">
                            {{ $solicitud->estudiante->nombre ?? '' }} {{ $solicitud->estudiante->apellido_paterno ?? '' }}
                            <small class="text-muted">({{ $solicitud->numero_documento }})</small>
                        </dd>
                        <dt class="col-sm-4">Fecha</dt><dd class="col-sm-8">{{ $solicitud->created_at?->format('d/m/Y H:i') }}</dd>
                        @if($solicitud->serial_voucher)
                            <dt class="col-sm-4">Pago</dt><dd class="col-sm-8">
                                {{ $solicitud->serial_voucher }}
                                @if($solicitud->pago_validado)<span class="badge bg-success">validado</span>@else<span class="badge bg-secondary">sin validar</span>@endif
                            </dd>
                        @endif
                        @if($solicitud->vbDirector)
                            <dt class="col-sm-4">V°B° por</dt><dd class="col-sm-8">{{ $solicitud->vbDirector->nombre }} {{ $solicitud->vbDirector->apellido_paterno }} · {{ $solicitud->vb_director_at?->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if($solicitud->usuarioActual || $solicitud->rolActual)
                            <dt class="col-sm-4">Actualmente con</dt><dd class="col-sm-8">
                                {{ $solicitud->usuarioActual ? ($solicitud->usuarioActual->nombre.' '.$solicitud->usuarioActual->apellido_paterno) : $solicitud->rolActual->nombre }}
                            </dd>
                        @endif
                    </dl>

                    @if(!empty($solicitud->datos))
                        <hr>
                        <h6>Detalle</h6>
                        <dl class="row mb-0">
                            @foreach($solicitud->datos as $k => $v)
                                <dt class="col-sm-4 text-capitalize">{{ str_replace('_', ' ', $k) }}</dt>
                                <dd class="col-sm-8">{{ is_array($v) ? implode(', ', $v) : $v }}</dd>
                            @endforeach
                        </dl>
                    @endif

                    @if($solicitud->inasistencias->count())
                        <hr>
                        <h6>Fechas a justificar</h6>
                        @foreach($solicitud->inasistencias as $ina)
                            <span class="badge bg-{{ $ina->justificada ? 'success' : 'secondary' }} me-1">
                                {{ \Carbon\Carbon::parse($ina->fecha)->format('d/m/Y') }}
                                {{ $ina->justificada ? '✓' : '' }}
                            </span>
                        @endforeach
                    @endif

                    @if($solicitud->adjuntos->count() || $puedeAdjuntar)
                        <hr>
                        <h6>Evidencias</h6>
                        @if($solicitud->adjuntos->count())
                            <ul class="list-group mb-2">
                                @foreach($solicitud->adjuntos as $adj)
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                        <a href="{{ asset('storage/' . $adj->path) }}" target="_blank">
                                            <i class="mdi mdi-paperclip"></i> {{ $adj->nombre_original ?? $adj->path }}
                                        </a>
                                        @if($puedeBorrarAdjunto)
                                            <form method="POST" action="{{ route('solicitudes.adjuntos.destroy', [$solicitud->id, $adj->id]) }}"
                                                  onsubmit="return confirm('¿Eliminar esta evidencia?');" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="mdi mdi-delete"></i></button>
                                            </form>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted small">Sin evidencias adjuntas.</p>
                        @endif

                        @if($puedeAdjuntar)
                            <form method="POST" action="{{ route('solicitudes.adjuntos.store', $solicitud->id) }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-auto flex-fill">
                                    <input type="file" name="adjuntos[]" class="form-control form-control-sm" multiple required accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-primary"><i class="mdi mdi-upload"></i> Subir</button>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Registrar pago --}}
            @if($puedePagar)
                <div class="card mb-3 border-warning">
                    <div class="card-header bg-warning-subtle"><i class="mdi mdi-cash-multiple me-1"></i>Pago pendiente</div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">Este trámite requiere pago. Ingresa tu voucher para validarlo contra el sistema de pagos.</p>
                        <form method="POST" action="{{ route('solicitudes.pago', $solicitud->id) }}" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-auto flex-fill">
                                <input type="text" name="serial_voucher" class="form-control form-control-sm" placeholder="Ej. V002-00060792" value="{{ $solicitud->serial_voucher }}" required>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-warning"><i class="mdi mdi-check"></i> Validar pago</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Acciones --}}
            @if(($canApprove && $solicitud->estado === 'enviada') || ($canAtender && $solicitud->estado === 'derivada'))
                <div class="card mb-3">
                    <div class="card-header"><i class="mdi mdi-gesture-tap me-1"></i>Acciones</div>
                    <div class="card-body d-flex gap-2 flex-wrap">
                        @if($canApprove && $solicitud->estado === 'enviada')
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalVB"><i class="mdi mdi-check-decagram"></i> Dar V°B° y derivar</button>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalObservar"><i class="mdi mdi-alert"></i> Observar</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalRechazar"><i class="mdi mdi-close"></i> Rechazar</button>
                        @endif
                        @if($canAtender && $solicitud->estado === 'derivada')
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAtender"><i class="mdi mdi-check-all"></i> Atender</button>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalDerivar"><i class="mdi mdi-share"></i> Re-derivar</button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Historial / Hoja de trámite --}}
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header"><i class="mdi mdi-timeline-clock-outline me-1"></i>Seguimiento del expediente</div>
                <div class="card-body">
                    @forelse($solicitud->historial as $h)
                        <div class="d-flex mb-3">
                            <div class="me-2"><i class="mdi mdi-circle text-primary"></i></div>
                            <div>
                                <div>@include('solicitudes.partials.estado', ['estado' => $h->estado_nuevo])</div>
                                @if($h->comentario)<div class="small">{{ $h->comentario }}</div>@endif
                                <small class="text-muted">
                                    {{ $h->usuario ? ($h->usuario->nombre.' '.$h->usuario->apellido_paterno) : 'Sistema' }} ·
                                    {{ $h->created_at?->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Sin movimientos.</p>
                    @endforelse
                </div>
            </div>

            @if($solicitud->derivaciones->count())
                <div class="card">
                    <div class="card-header"><i class="mdi mdi-transit-connection-variant me-1"></i>Derivaciones (Hoja de Trámite)</div>
                    <div class="card-body">
                        @foreach($solicitud->derivaciones as $d)
                            <div class="border-start border-2 ps-2 mb-2">
                                <div class="small">
                                    <b>De:</b> {{ $d->deUsuario ? ($d->deUsuario->nombre.' '.$d->deUsuario->apellido_paterno) : '—' }}
                                    <b>→ A:</b>
                                    {{ $d->usuarioDestino ? ($d->usuarioDestino->nombre.' '.$d->usuarioDestino->apellido_paterno) : ($d->rolDestino->nombre ?? '—') }}
                                </div>
                                <div class="small text-muted">{{ \App\Models\SolicitudDerivacion::ACCIONES[$d->accion] ?? $d->accion }}@if($d->observacion) · {{ $d->observacion }}@endif</div>
                                <small class="text-muted">{{ $d->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('modals')
    @if($canApprove && $solicitud->estado === 'enviada')
        {{-- V°B° y derivar --}}
        <div class="modal fade" id="modalVB" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('solicitudes.vb', $solicitud->id) }}">@csrf
                <div class="modal-header"><h5 class="modal-title">Dar V°B° y derivar</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">@include('solicitudes.partials.derivacion-fields', ['roles' => $roles])</div>
                <div class="modal-footer"><button class="btn btn-success">Confirmar V°B°</button></div>
            </form>
        </div></div></div>

        <div class="modal fade" id="modalObservar" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('solicitudes.observar', $solicitud->id) }}">@csrf
                <div class="modal-header"><h5 class="modal-title">Observar trámite</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><textarea name="observacion" class="form-control" rows="3" required placeholder="Indica qué debe corregir..."></textarea></div>
                <div class="modal-footer"><button class="btn btn-warning">Enviar observación</button></div>
            </form>
        </div></div></div>

        <div class="modal fade" id="modalRechazar" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('solicitudes.rechazar', $solicitud->id) }}">@csrf
                <div class="modal-header"><h5 class="modal-title">Rechazar trámite</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><textarea name="observacion" class="form-control" rows="3" required placeholder="Motivo del rechazo..."></textarea></div>
                <div class="modal-footer"><button class="btn btn-danger">Rechazar</button></div>
            </form>
        </div></div></div>
    @endif

    @if($canAtender && $solicitud->estado === 'derivada')
        <div class="modal fade" id="modalAtender" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('solicitudes.atender', $solicitud->id) }}">@csrf
                <div class="modal-header"><h5 class="modal-title">Atender trámite</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><textarea name="comentario" class="form-control" rows="3" placeholder="Comentario de atención (opcional)..."></textarea></div>
                <div class="modal-footer"><button class="btn btn-success">Marcar atendido</button></div>
            </form>
        </div></div></div>

        <div class="modal fade" id="modalDerivar" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('solicitudes.derivar', $solicitud->id) }}">@csrf
                <div class="modal-header"><h5 class="modal-title">Re-derivar trámite</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">@include('solicitudes.partials.derivacion-fields', ['roles' => $roles])</div>
                <div class="modal-footer"><button class="btn btn-info">Derivar</button></div>
            </form>
        </div></div></div>
    @endif
@endpush

@push('scripts')
<script>
    // Autocompletado de personas (administrativos) para los campos de derivación
    document.querySelectorAll('.persona-search').forEach(function (input) {
        const box = input.parentElement.querySelector('.persona-results');
        const hidden = input.parentElement.querySelector('.persona-id');
        const elegido = input.parentElement.querySelector('.persona-elegido');
        let t;

        input.addEventListener('input', function () {
            hidden.value = '';
            elegido.textContent = '';
            clearTimeout(t);
            const q = input.value.trim();
            if (q.length < 2) { box.style.display = 'none'; return; }
            t = setTimeout(function () {
                fetch("{{ route('solicitudes.administrativos') }}?q=" + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(list => {
                        if (!list.length) { box.style.display = 'none'; return; }
                        box.innerHTML = list.map(p =>
                            `<button type="button" class="list-group-item list-group-item-action py-1 px-2"
                                data-id="${p.id}" data-nombre="${p.nombre} ${p.apellido_paterno}">
                                ${p.nombre} ${p.apellido_paterno} <small class="text-muted">${p.numero_documento}</small>
                            </button>`).join('');
                        box.style.display = 'block';
                    });
            }, 350);
        });

        box.addEventListener('mousedown', function (e) {
            const btn = e.target.closest('[data-id]');
            if (!btn) return;
            e.preventDefault();
            hidden.value = btn.dataset.id;
            input.value = btn.dataset.nombre;
            elegido.textContent = 'Seleccionado: ' + btn.dataset.nombre;
            box.style.display = 'none';
        });

        input.addEventListener('blur', () => setTimeout(() => box.style.display = 'none', 200));
    });
</script>
@endpush

@extends('layouts.app')

@section('title', 'Consultar Estudiante')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="mdi mdi-account-search-outline me-2"></i>Consulta de Estudiante (Trámites y Pagos)</h4>

    <div class="card mb-3"><div class="card-body">
        <form method="GET" action="{{ route('solicitudes.estudiante') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">DNI del estudiante</label>
                <input type="text" name="documento" class="form-control" value="{{ $documento }}" placeholder="Ej. 61785808" autofocus>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary"><i class="mdi mdi-magnify"></i> Consultar</button>
            </div>
        </form>
    </div></div>

    @if($documento !== '')
        <div class="alert alert-info">
            <b>Estudiante:</b>
            @if($estudiante)
                {{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }} — DNI {{ $documento }}
            @else
                No registrado en el sistema (DNI {{ $documento }}). Se muestran sus pagos igualmente.
            @endif
        </div>

        <div class="row">
            {{-- Trámites --}}
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header"><i class="mdi mdi-file-document-multiple-outline me-1"></i>Trámites ({{ $solicitudes->count() }})</div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light"><tr><th>Código</th><th>Trámite</th><th>Estado</th><th>Voucher</th></tr></thead>
                            <tbody>
                                @forelse($solicitudes as $s)
                                    <tr>
                                        <td><a href="{{ route('solicitudes.show', $s->id) }}">{{ $s->codigo }}</a></td>
                                        <td>{{ $s->tipo->nombre ?? '—' }}</td>
                                        <td>@include('solicitudes.partials.estado', ['estado' => $s->estado])</td>
                                        <td>
                                            @if($s->serial_voucher)
                                                {{ $s->serial_voucher }}
                                                @if($s->pago_validado)<span class="badge bg-success">✓</span>@else<span class="badge bg-warning text-dark">sin validar</span>@endif
                                            @else — @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sin trámites.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pagos (API) + conciliación --}}
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header"><i class="mdi mdi-cash-multiple me-1"></i>Historial de Pagos (sistema de pagos)</div>
                    <div class="card-body p-0">
                        @if($pagosError)
                            <div class="alert alert-warning m-2 mb-0">{{ $pagosError }}</div>
                        @else
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light"><tr><th>Voucher</th><th>Concepto</th><th class="text-end">Monto</th><th>Conciliación</th></tr></thead>
                                <tbody>
                                    @forelse($pagos as $p)
                                        @php $serial = $p['serial_voucher'] ?? $p['serial'] ?? '—'; $usado = in_array($serial, $vouchersUsados); @endphp
                                        <tr>
                                            <td>{{ $serial }}</td>
                                            <td><small>{{ $p['concepto'] ?? ($p['items'][0]['descripcion'] ?? 'Pago') }}</small></td>
                                            <td class="text-end">S/ {{ $p['monto_total'] ?? $p['monto'] ?? '0.00' }}</td>
                                            <td>
                                                @if($usado)<span class="badge bg-success">Usado en trámite</span>
                                                @else<span class="badge bg-secondary">Sin trámite</span>@endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">Sin pagos registrados.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('title', 'Reporte de Estudiantes Inhabilitados')

@section('content')
<div class="container-fluid px-4">

    {{-- ── HEADER INSTITUCIONAL ── --}}
    <div style="background: linear-gradient(135deg, #2b5a6f 0%, #1a3d52 55%, #0d2838 100%);
                border-radius: 16px; padding: 22px 28px; color: #fff; margin-bottom: 20px;
                position: relative; overflow: hidden; box-shadow: 0 8px 32px rgba(43,90,111,0.3);">
        {{-- Círculos decorativos --}}
        <div style="position:absolute;top:-40px;right:-40px;width:160px;height:160px;border-radius:50%;background:rgba(140,198,63,0.1);"></div>
        <div style="position:absolute;bottom:-25px;left:40%;width:100px;height:100px;border-radius:50%;background:rgba(0,174,239,0.08);"></div>
        {{-- Franja tricolor inferior --}}
        <div style="position:absolute;bottom:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#ec008c 0%,#ec008c 33%,#00aeef 33%,#00aeef 66%,#8cc63f 66%,#8cc63f 100%);"></div>

        <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1;">
            <div style="width:52px;height:52px;border-radius:14px;
                        background:linear-gradient(135deg,#ec008c,#c0006e);
                        display:flex;align-items:center;justify-content:center;
                        font-size:26px;color:#fff;flex-shrink:0;
                        box-shadow:0 4px 16px rgba(236,0,140,0.45);">
                <i class="mdi mdi-account-remove"></i>
            </div>
            <div style="flex:1;">
                <h4 style="margin:0;font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-0.3px;">
                    Reporte de Estudiantes Inhabilitados
                </h4>
                <p style="margin:3px 0 0;font-size:0.82rem;color:rgba(255,255,255,0.7);">
                    Gestiona y genera reportes de estudiantes que superan el límite de inasistencias — CEPRE UNAMAD
                </p>
            </div>
            <div class="d-none d-lg-block">
                <img src="{{ asset('assets/images/logo cepre.png') }}" alt="CEPRE" style="height:46px;opacity:0.9;">
            </div>
        </div>
    </div>

    {{-- ── FILTROS ── --}}
    <div class="card mb-4" style="border:1px solid #d1dde4;border-left:4px solid #00aeef;border-radius:14px;box-shadow:0 4px 16px rgba(0,0,0,0.05);">
        <div class="card-body p-4">
            <form id="filterForm" action="{{ route('postulaciones.reportes.inhabilitados.pdf') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label fw-bold text-uppercase" style="font-size:0.75rem;color:#2b5a6f;letter-spacing:0.5px;">Ciclo Académico</label>
                        <select class="form-select select2" id="ciclo_id" name="ciclo_id" required>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}" {{ $ciclo->es_activo ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }} {{ $ciclo->es_activo ? '(Actual)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label fw-bold text-uppercase" style="font-size:0.75rem;color:#2b5a6f;letter-spacing:0.5px;">Calcular hasta:</label>
                        <select class="form-select" id="periodo_examen" name="periodo_examen">
                            <option value="hoy">Fecha Actual (Proyectado)</option>
                            <option value="1">Hasta Primer Examen</option>
                            <option value="2" selected>Hasta Segundo Examen</option>
                            <option value="3">Hasta Tercer Examen</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label fw-bold text-uppercase" style="font-size:0.75rem;color:#2b5a6f;letter-spacing:0.5px;">Modalidad</label>
                        <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion">
                            <option value="">Todas las modalidades</option>
                            <option value="postulante">Postulante</option>
                            <option value="reforzamiento">Reforzamiento</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label fw-bold text-uppercase" style="font-size:0.75rem;color:#2b5a6f;letter-spacing:0.5px;">Carrera</label>
                        <select class="form-select select2" id="carrera_id" name="carrera_id">
                            <option value="">Todas las carreras</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2 pt-1">
                        <button type="button" id="btnPreview"
                            style="background:linear-gradient(135deg,#2b5a6f,#1a3d52);color:#fff;border:none;font-weight:700;font-size:0.83rem;padding:9px 22px;border-radius:10px;box-shadow:0 4px 12px rgba(43,90,111,0.3);transition:all 0.2s;cursor:pointer;">
                            <i class="mdi mdi-eye me-1"></i> CARGAR RESULTADOS
                        </button>
                        <button type="button" id="btnExportPdf"
                            style="background:linear-gradient(135deg,#ec008c,#c0006e);color:#fff;border:none;font-weight:700;font-size:0.83rem;padding:9px 22px;border-radius:10px;box-shadow:0 4px 12px rgba(236,0,140,0.3);transition:all 0.2s;cursor:pointer;">
                            <i class="mdi mdi-file-pdf-box me-1"></i> DESCARGAR PDF
                        </button>
                        <button type="button" id="btnExportExcel"
                            style="background:linear-gradient(135deg,#8cc63f,#6fa82d);color:#fff;border:none;font-weight:700;font-size:0.83rem;padding:9px 22px;border-radius:10px;box-shadow:0 4px 12px rgba(140,198,63,0.3);transition:all 0.2s;cursor:pointer;">
                            <i class="mdi mdi-microsoft-excel me-1"></i> EXPORTAR EXCEL
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div id="statsSection" class="row g-3 mb-4" style="display:none;">
        <div class="col-6 col-md-3">
            <div style="background:linear-gradient(135deg,#2b5a6f,#1a3d52);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 16px rgba(43,90,111,0.25);position:relative;overflow:hidden;">
                <div style="width:44px;height:44px;border-radius:11px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;flex-shrink:0;">
                    <i class="mdi mdi-account-group"></i></div>
                <div>
                    <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:rgba(255,255,255,0.75);">Total Analizados</div>
                    <div id="statTotal" style="font-size:2rem;font-weight:900;color:#fff;line-height:1.1;">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div style="background:linear-gradient(135deg,#8cc63f,#6fa82d);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 16px rgba(140,198,63,0.28);position:relative;overflow:hidden;">
                <div style="width:44px;height:44px;border-radius:11px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;flex-shrink:0;">
                    <i class="mdi mdi-check-circle"></i></div>
                <div>
                    <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:rgba(255,255,255,0.75);">Regulares</div>
                    <div id="statRegulares" style="font-size:2rem;font-weight:900;color:#fff;line-height:1.1;">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div style="background:linear-gradient(135deg,#e69c00,#c87d00);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 16px rgba(230,156,0,0.28);position:relative;overflow:hidden;">
                <div style="width:44px;height:44px;border-radius:11px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;flex-shrink:0;">
                    <i class="mdi mdi-alert"></i></div>
                <div>
                    <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:rgba(255,255,255,0.75);">Amonestados</div>
                    <div id="statAmonestados" style="font-size:2rem;font-weight:900;color:#fff;line-height:1.1;">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div style="background:linear-gradient(135deg,#ec008c,#c0006e);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 16px rgba(236,0,140,0.3);position:relative;overflow:hidden;">
                <div style="width:44px;height:44px;border-radius:11px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;flex-shrink:0;">
                    <i class="mdi mdi-account-cancel"></i></div>
                <div>
                    <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:rgba(255,255,255,0.75);">Inhabilitados</div>
                    <div id="statInhabilitados" style="font-size:2rem;font-weight:900;color:#fff;line-height:1.1;">0</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABLA DE RESULTADOS ── --}}
    <div id="resultsSection" class="card" style="display:none;border:none;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,0.07);overflow:hidden;">

        {{-- Header de la tarjeta --}}
        <div style="background:linear-gradient(90deg,#2b5a6f,#1a3d52);color:#fff;padding:14px 20px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <i class="mdi mdi-format-list-numbered fs-5"></i>
            <span style="font-weight:700;font-size:0.95rem;">Lista de Estudiantes Inhabilitados</span>
            <span id="resultsCount" style="background:rgba(255,255,255,0.2);color:#fff;font-size:0.78rem;font-weight:700;padding:2px 10px;border-radius:20px;margin-left:4px;"></span>

            {{-- 🔍 BUSCADOR EN TIEMPO REAL --}}
            <div class="ms-auto d-flex align-items-center" style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);border-radius:10px;padding:5px 12px;min-width:240px;">
                <i class="mdi mdi-magnify" style="color:rgba(255,255,255,0.7);font-size:16px;margin-right:7px;"></i>
                <input type="text" id="searchInput" placeholder="Buscar por nombre, DNI, carrera..."
                    style="background:transparent;border:none;outline:none;color:#fff;font-size:0.85rem;width:100%;"
                    oninput="filterTable(this.value)">
                <i class="mdi mdi-close" id="clearSearch" style="color:rgba(255,255,255,0.5);cursor:pointer;display:none;font-size:14px;margin-left:5px;" onclick="clearSearchBox()"></i>
            </div>
        </div>

        {{-- Contador de resultados del filtro --}}
        <div id="searchInfo" style="display:none;background:#f0f5f8;padding:7px 20px;font-size:0.8rem;color:#2b5a6f;border-bottom:1px solid #d1dde4;">
            <i class="mdi mdi-filter-check me-1"></i>
            Mostrando <strong id="filteredCount">0</strong> de <strong id="totalCount">0</strong> registros
            <span style="color:#ec008c;cursor:pointer;margin-left:10px;" onclick="clearSearchBox()">✕ Limpiar filtro</span>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" id="resultsTable" style="font-size:0.88rem;">
                <thead>
                    <tr style="background:#f0f5f8;">
                        <th style="color:#2b5a6f;font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #00aeef;padding:10px 14px;">Estudiante</th>
                        <th class="text-center" style="width:95px;color:#2b5a6f;font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #00aeef;padding:10px 8px;">DNI</th>
                        <th style="color:#2b5a6f;font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #00aeef;padding:10px 14px;">Carrera</th>
                        <th class="text-center" style="width:70px;color:#2b5a6f;font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #00aeef;padding:10px 8px;">Faltas</th>
                        <th class="text-center" style="width:85px;color:#2b5a6f;font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #00aeef;padding:10px 8px;">% Inasist.</th>
                        <th class="text-center" style="width:120px;color:#2b5a6f;font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #00aeef;padding:10px 8px;">Estado</th>
                    </tr>
                </thead>
                <tbody id="resultsBody"></tbody>
            </table>
        </div>

        {{-- Sin resultados del buscador --}}
        <div id="noSearchResults" style="display:none;text-align:center;padding:40px 20px;color:#64748b;">
            <i class="mdi mdi-magnify-close" style="font-size:2.5rem;opacity:0.4;display:block;margin-bottom:10px;"></i>
            No hay resultados para "<strong id="searchTermDisplay"></strong>"
        </div>
    </div>

    {{-- Sin resultados del servidor --}}
    <div id="noResults" style="display:none;text-align:center;padding:50px 20px;background:#f8fafc;border-radius:14px;border:2px dashed #d1e0e8;margin-top:16px;">
        <i class="mdi mdi-magnify-close" style="font-size:3rem;color:#a0b8c5;display:block;margin-bottom:10px;"></i>
        <p style="color:#64748b;font-weight:500;margin:0;">No se encontraron estudiantes inhabilitados con los filtros seleccionados.</p>
    </div>

    {{-- Nota informativa --}}
    <div style="margin-top:16px;background:linear-gradient(135deg,rgba(0,174,239,0.06),rgba(43,90,111,0.04));border:1px solid rgba(0,174,239,0.2);border-left:4px solid #00aeef;border-radius:10px;padding:13px 18px;font-size:0.84rem;color:#334155;">
        <i class="mdi mdi-information" style="color:#00aeef;margin-right:6px;"></i>
        <strong>Criterios:</strong>
        <strong>Inhabilitado</strong>: supera el {{ $cicloActivo->porcentaje_inhabilitacion ?? '30' }}% de inasistencias.
        &nbsp;|&nbsp;
        <strong>Amonestado</strong>: supera el {{ $cicloActivo->porcentaje_amonestacion ?? '20' }}% de inasistencias.
    </div>

</div>
@endsection

@push('styles')
<style>
/* ── Modality badges ── */
.badge-postulante {
    background: linear-gradient(135deg, #00aeef, #0090cc);
    color: #fff; font-size: 0.64rem; font-weight: 800;
    padding: 2px 9px; border-radius: 20px;
    letter-spacing: 0.5px; text-transform: uppercase;
    display: inline-flex; align-items: center; gap: 3px;
    box-shadow: 0 2px 6px rgba(0,174,239,0.35); margin-left: 6px;
    vertical-align: middle;
}
.badge-reforzamiento {
    background: linear-gradient(135deg, #8cc63f, #6fa82d);
    color: #fff; font-size: 0.64rem; font-weight: 800;
    padding: 2px 9px; border-radius: 20px;
    letter-spacing: 0.5px; text-transform: uppercase;
    display: inline-flex; align-items: center; gap: 3px;
    box-shadow: 0 2px 6px rgba(140,198,63,0.4); margin-left: 6px;
    vertical-align: middle;
}
.badge-inhabilitado {
    background: linear-gradient(135deg, #cc0000, #aa0000);
    color: #fff; font-size: 0.64rem; font-weight: 800;
    padding: 2px 9px; border-radius: 20px;
    letter-spacing: 0.5px; text-transform: uppercase;
    display: inline-flex; align-items: center; gap: 3px;
    box-shadow: 0 2px 6px rgba(204,0,0,0.38);
}

/* ── Career group header ── */
.career-group-header td {
    background: linear-gradient(90deg, rgba(43,90,111,0.1), rgba(43,90,111,0.04)) !important;
    font-weight: 800; color: #2b5a6f; font-size: 0.82rem;
    border-top: 2px solid rgba(43,90,111,0.25) !important;
    border-bottom: 1px solid rgba(43,90,111,0.12) !important;
    padding: 9px 14px;
}

/* ── Table row hover ── */
#resultsTable tbody tr:not(.career-group-header):hover {
    background-color: rgba(0,174,239,0.04) !important;
}
#resultsTable tbody tr:not(.career-group-header) td {
    padding: 9px 14px;
    vertical-align: middle;
    border-bottom: 1px solid #eef2f5;
}

/* ── Search input placeholder ── */
#searchInput::placeholder { color: rgba(255,255,255,0.5); }

/* ── highlight matched text ── */
.search-highlight { background: #fff200; color: #0d2838; border-radius: 3px; padding: 0 2px; font-weight: 800; }

/* ── Button hover ── */
button { transition: transform 0.2s, box-shadow 0.2s !important; }
button:hover { transform: translateY(-2px); }
</style>
@endpush

@push('scripts')
<script>
// All inhabilitados data (stored globally for search)
let allInhabilitadosData = [];

$(document).ready(function () {
    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap-4', width: '100%' });
    }

    function loadResults() {
        const formData = $('#filterForm').serialize();

        Swal.fire({
            title: 'Procesando...',
            text: 'Calculando inasistencias, por favor espere.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "{{ route('postulaciones.reportes.inhabilitados.data') }}",
            type: "GET",
            data: formData,
            success: function (response) {
                Swal.close();
                if (response.success) {
                    const data = response.data;

                    // Stats
                    $('#statTotal').text(data.total_general);
                    $('#statRegulares').text(data.total_regulares);
                    $('#statAmonestados').text(data.total_amonestados);
                    $('#statInhabilitados').text(data.total_inhabilitados);
                    $('#statsSection').fadeIn();

                    // Store data globally
                    allInhabilitadosData = data.inhabilitados || [];

                    // Clear search
                    $('#searchInput').val('');
                    $('#searchInfo').hide();
                    $('#clearSearch').hide();

                    if (allInhabilitadosData.length > 0) {
                        $('#totalCount').text(allInhabilitadosData.length);
                        renderTable(allInhabilitadosData, '');
                        $('#resultsSection').fadeIn();
                        $('#noResults').hide();
                    } else {
                        $('#resultsSection').hide();
                        $('#noResults').fadeIn();
                    }
                }
            },
            error: function () {
                Swal.fire('Error', 'No se pudo cargar la vista previa.', 'error');
            }
        });
    }

    function renderTable(items, searchTerm) {
        const body = $('#resultsBody');
        body.empty();
        $('#noSearchResults').hide();

        if (items.length === 0) {
            $('#noSearchResults').show();
            $('#searchTermDisplay').text(searchTerm);
            $('#resultsCount').text('0 registros');
            return;
        }

        $('#resultsCount').text(items.length + ' registros');

        // Sort by career then name
        const sorted = [...items].sort((a, b) => {
            const cmp = a.carrera.localeCompare(b.carrera, 'es');
            return cmp !== 0 ? cmp : a.nombres.localeCompare(b.nombres, 'es');
        });

        // Group by career
        const grouped = {};
        sorted.forEach(item => {
            if (!grouped[item.carrera]) grouped[item.carrera] = [];
            grouped[item.carrera].push(item);
        });

        function hl(text, term) {
            if (!term) return text;
            const escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return String(text).replace(new RegExp(escaped, 'gi'), m => `<span class="search-highlight">${m}</span>`);
        }

        let rowNum = 1;
        Object.entries(grouped).forEach(([carrera, carreraItems]) => {
            const postCount = carreraItems.filter(i => i.tipo_inscripcion === 'postulante').length;
            const refoCount = carreraItems.filter(i => i.tipo_inscripcion !== 'postulante').length;

            body.append(`
                <tr class="career-group-header">
                    <td colspan="6">
                        <i class="mdi mdi-folder-open me-1"></i>
                        ${hl(carrera, searchTerm)}
                        <span style="font-size:0.74rem;font-weight:400;opacity:0.75;margin-left:6px;">
                            &mdash; ${carreraItems.length} inhabilitado${carreraItems.length > 1 ? 's' : ''}
                            &nbsp;|&nbsp;
                            <span class="badge-postulante" style="margin-left:3px;"><i class="mdi mdi-school"></i> Post: ${postCount}</span>
                            <span class="badge-reforzamiento"><i class="mdi mdi-refresh"></i> Ref: ${refoCount}</span>
                        </span>
                    </td>
                </tr>`);

            carreraItems.forEach(item => {
                const pct = (100 - item.porcentaje).toFixed(1);
                const modBadge = item.tipo_inscripcion === 'postulante'
                    ? `<span class="badge-postulante"><i class="mdi mdi-school"></i> Postulante</span>`
                    : `<span class="badge-reforzamiento"><i class="mdi mdi-refresh"></i> Reforzamiento</span>`;

                body.append(`
                    <tr data-searchable="${(item.nombres + ' ' + item.dni + ' ' + item.carrera).toLowerCase()}">
                        <td>
                            <div class="d-flex align-items-center flex-wrap">
                                <span class="fw-bold me-1" style="color:#0d2838;">${rowNum}. ${hl(item.nombres, searchTerm)}</span>
                                ${modBadge}
                            </div>
                            <small class="text-muted">Aula: ${item.aula} | Turno: ${item.turno}</small>
                        </td>
                        <td class="text-center" style="font-family:monospace;font-size:0.9rem;">${hl(item.dni, searchTerm)}</td>
                        <td><small style="color:#475569;">${hl(item.carrera, searchTerm)}</small></td>
                        <td class="text-center fw-bold" style="color:#cc0000;">${item.faltas}</td>
                        <td class="text-center fw-bold" style="color:#cc0000;">${pct}%</td>
                        <td class="text-center">
                            <span class="badge-inhabilitado"><i class="mdi mdi-cancel me-1"></i>Inhabilitado</span>
                        </td>
                    </tr>`);
                rowNum++;
            });
        });
    }

    // ── Real-time search ──
    window.filterTable = function (term) {
        const q = term.trim().toLowerCase();

        if (q.length === 0) {
            $('#searchInfo').hide();
            $('#clearSearch').hide();
            renderTable(allInhabilitadosData, '');
            return;
        }

        $('#clearSearch').show();

        const filtered = allInhabilitadosData.filter(item => {
            return (item.nombres.toLowerCase().includes(q) ||
                    item.dni.toString().includes(q) ||
                    item.carrera.toLowerCase().includes(q));
        });

        $('#filteredCount').text(filtered.length);
        $('#totalCount').text(allInhabilitadosData.length);
        $('#searchInfo').show();

        renderTable(filtered, term.trim());
    };

    window.clearSearchBox = function () {
        $('#searchInput').val('').trigger('input');
        $('#searchInfo').hide();
        $('#clearSearch').hide();
    };

    $('#btnPreview').click(function () { loadResults(); });

    $('#btnExportPdf').click(function () {
        $('#filterForm').attr('action', "{{ route('postulaciones.reportes.inhabilitados.pdf') }}").submit();
    });

    $('#btnExportExcel').click(function () {
        $('#filterForm').attr('action', "{{ route('postulaciones.reportes.inhabilitados.excel') }}").submit();
    });
});
</script>
@endpush

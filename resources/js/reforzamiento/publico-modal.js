/**
 * JS para el Modal de Inscripción de Reforzamiento - Premium v7.5
 * Funcionalidades completas: RENIEC, Cascada Ubigeo, Búsqueda de Colegios, Gestión de Apoderados.
 */

let currentStep = 1;
let totalSteps = 5;
let apoderadoCount = 0;
let debounceTimer;
let pagoDetectado = false; 
let apiSerial = ''; 
const stepHeaders = {
    1: { title: 'Datos del Estudiante', desc: 'Ingresa tu DNI o completa tus datos personales.' },
    2: { title: 'Apoderados y Tutores', desc: 'Registra los datos de tus padres o tutor legal.' },
    3: { title: 'Información Académica', desc: 'Grado, turno y centro educativo del estudiante.' },
    4: { title: 'Documentos Requeridos', desc: 'Sube tus documentos arrastrando o seleccionando archivos.' },
    5: { title: 'Confirmar y Enviar', desc: 'Revisa tus datos antes de finalizar la inscripción.' }
};

document.addEventListener('DOMContentLoaded', function() {
    if (!window.reforzamientoInitialized) {
        initPremiumWizard();
        window.reforzamientoInitialized = true;
    }
});

function getBaseUrl() {
    // 1. Prioridad: Buscar el meta tag app-url (La opción más segura)
    const metaUrl = document.querySelector('meta[name="app-url"]');
    if (metaUrl && metaUrl.content) {
        return metaUrl.content.replace(/\/$/, ''); // Quitar barra final si existe
    }
    
    // 2. Fallback inteligente: Detectar si estamos en una carpeta del servidor
    // Ej: https://portalcepre.unamad.edu.pe/sistema_asistencia/
    const origin = window.location.origin;
    const path = window.location.pathname;
    const parts = path.split('/').filter(p => p.length > 0);
    
    // Si no estamos en la raíz y la primera parte no es 'api', es una subcarpeta
    if (parts.length > 0 && parts[0] !== 'api' && parts[0] !== 'reforzamiento') {
        return origin + '/' + parts[0];
    }
    
    return origin;
}

function initPremiumWizard() {
    const modal = document.getElementById('reforzamientoModal');
    if (!modal) return;

    console.log("Reforzamiento Wizard Initialized. Base URL:", getBaseUrl());

    // Navigation
    document.getElementById('btnNext').addEventListener('click', nextStep);
    document.getElementById('btnPrev').addEventListener('click', prevStep);
    
    // Core actions
    document.getElementById('btnVerifyDni').addEventListener('click', verifyDni);
    document.getElementById('btnPlanB').addEventListener('click', activatePlanB);
    document.getElementById('btnAddApoderado').addEventListener('click', addApoderadoForm);
    
    // School search  
    document.getElementById('ref_dep').addEventListener('change', handleDepChange);
    document.getElementById('ref_prov').addEventListener('change', handleProvChange);
    document.getElementById('ref_dist').addEventListener('change', handleDistChange);
    
    const searchInput = document.getElementById('ref_search_colegio');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => performSchoolSearch(e.target.value), 500);
        });
    }
    
    const btnSearch = document.getElementById('btnBuscarColegio');
    if (btnSearch) {
        btnSearch.addEventListener('click', () => {
            const val = document.getElementById('ref_search_colegio').value;
            performSchoolSearch(val);
        });
    }

    // Form submit
    const form = document.getElementById('reforzamientoForm');
    if (form) {
        form.addEventListener('submit', handleFinalSubmit);
        form.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                nextStep();
            }
        });
    }

    // DNI input: only numbers
    const dniInput = document.getElementById('ref_dni');
    if (dniInput) {
        dniInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length === 8) verifyDni();
        });
    }

    initDropzones();
    if (apoderadoCount === 0) addApoderadoForm();
    loadDepartamentos();
    updateProgressBar();
    updateWizardUI(); 
}

// ==========================================
// VALIDATION LOGIC
// ==========================================
function validateCurrentStep() {
    let errors = [];

    if (currentStep === 1) {
        const nombre = document.getElementById('ref_nombre').value.trim();
        const paterno = document.getElementById('ref_apellido_paterno').value.trim();
        const telefono = document.getElementById('ref_telefono').value.trim();
        const dni = document.getElementById('ref_dni').value.trim();

        if (!dni || dni.length !== 8) errors.push('DNI del estudiante (8 dígitos)');
        if (!nombre || nombre.length < 2) errors.push('Nombres completos');
        if (!paterno || paterno.length < 2) errors.push('Apellido paterno');
        if (!telefono || !/^[0-9]{9}$/.test(telefono)) errors.push('Teléfono (9 dígitos)');
    }

    if (currentStep === 2) {
        const apoderados = document.querySelectorAll('#apoderadosContainer .rf-card');
        if (apoderados.length === 0) errors.push('Debe registrar al menos un apoderado');
        
        apoderados.forEach((card, i) => {
            const idx = i + 1;
            const dniEl = card.querySelector(`[name*="[dni]"]`);
            const nomEl = card.querySelector(`[name*="[nombre]"]`);
            if (dniEl && (!dniEl.value.trim() || dniEl.value.trim().length !== 8))
                errors.push(`Apoderado ${idx}: DNI invalido`);
            if (nomEl && (!nomEl.value.trim() || nomEl.value.trim().length < 2))
                errors.push(`Apoderado ${idx}: Nombre incompleto`);
        });
    }

    if (currentStep === 3) {
        const dist = document.getElementById('ref_dist').value;
        const colId = document.getElementById('ref_colegio_id_hidden').value;
        const colManual = document.getElementById('ref_colegio_nombre_manual').value;
        const grado = document.querySelector('[name="grado"]').value;

        if (!dist) errors.push('Debe seleccionar Departamento, Provincia y Distrito');
        if (!colId && !colManual) errors.push('Debe seleccionar un colegio de la lista o registrar manual');
        if (!grado) errors.push('Falta seleccionar grado');
    }

    if (currentStep === 4) {
        const foto = document.querySelector('[name="foto"]');
        const dniFile = document.querySelector('[name="dni_file"]');
        const dniApoFile = document.querySelector('[name="dni_apoderado_file"]');
        const voucherFile = document.querySelector('[name="voucher_file"]');
        const certFile = document.querySelector('[name="certificado_file"]');
        
        if (!foto?.files?.length) errors.push('Falta subir la Foto (Rostro)');
        if (!dniFile?.files?.length) errors.push('Falta subir Copia de DNI del Estudiante');
        if (!dniApoFile?.files?.length) errors.push('Falta subir Copia de DNI del Apoderado');
        if (!voucherFile?.files?.length) errors.push('Falta subir el Voucher de Pago');
        if (!certFile?.files?.length) errors.push('Falta subir Certificado/Constancia');
    }

    return errors;
}

// ==========================================
// NAVIGATION & UI
// ==========================================
function updateProgressBar() {
    const fill = document.getElementById('rfProgressFill');
    if (fill) fill.style.height = `${((currentStep - 1) / (totalSteps - 1)) * 100}%`;
}

function updateWizardUI() {
    document.querySelectorAll('.rf-panel').forEach((p, idx) => {
        p.classList.toggle('active', (idx + 1) === currentStep);
    });

    document.querySelectorAll('.rf-nav-item').forEach((nav, idx) => {
        const stepNo = idx + 1;
        const numEl = nav.querySelector('.rf-step-num');
        nav.classList.remove('active', 'completed');
        
        if (stepNo === currentStep) {
            nav.classList.add('active');
            numEl.innerHTML = (stepNo < 10 ? '0' : '') + stepNo;
        } else if (stepNo < currentStep) {
            nav.classList.add('completed');
            numEl.innerHTML = '<span class="material-icons-round">check</span>';
        } else {
            numEl.innerHTML = (stepNo < 10 ? '0' : '') + stepNo;
        }
    });

    const header = stepHeaders[currentStep];
    const titleEl = document.getElementById('rfHeaderTitle');
    const descEl = document.getElementById('rfHeaderDesc');
    if (titleEl && header) titleEl.textContent = header.title;
    if (descEl && header) descEl.textContent = header.desc;

    document.getElementById('btnPrev').classList.toggle('hidden', currentStep === 1);
    const isLast = (currentStep === totalSteps);
    document.getElementById('btnNext').classList.toggle('hidden', isLast);
    document.getElementById('btnSubmit').classList.toggle('hidden', !isLast);
    
    if (document.getElementById('currentStepText')) {
        document.getElementById('currentStepText').textContent = currentStep;
    }

    if (isLast) generateSummary();
    if (currentStep === 2) {
        const container = document.getElementById('apoderadosContainer');
        if (container && container.children.length === 0) {
            addApoderadoForm();
        }
    }

    const rfBody = document.querySelector('.rf-body');
    if (rfBody) rfBody.scrollTop = 0;
}

function nextStep() {
    const errors = validateCurrentStep();
    if (errors.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Datos Incompletos',
            html: `<div style="text-align:left; font-size:0.85rem;"><ul>${errors.map(e => `<li>${e}</li>`).join('')}</ul></div>`,
            confirmButtonColor: '#ec008c'
        });
        return;
    }
    if (currentStep < totalSteps) {
        currentStep++;
        updateWizardUI();
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateWizardUI();
    }
}

// ==========================================
// DNI & RENIEC
// ==========================================
async function verifyDni() {
    const dniInput = document.getElementById('ref_dni');
    const dni = dniInput.value.trim();
    const btn = document.getElementById('btnVerifyDni');
    const resultDiv = document.getElementById('dniVerifyResult');

    if (dni.length !== 8) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="rf-spin" style="display:inline-block;">cached</span>';

    try {
        const baseUrl = getBaseUrl();
        const url = `${baseUrl}/api/public-reforzamiento/verify-dni/${dni}`;
        console.log("Verificando DNI en:", url);
        
        const response = await fetch(url);
        const data = await response.json();

        if (response.ok) {
            const resData = data.data;
            document.getElementById('ref_ciclo_id').value = resData.ciclo.id;

            if (resData.pago_encontrado) {
                pagoDetectado = true;
                const p = resData.pago_encontrado;
                
                // Guardar serial real de la API UNAMAD (serial_voucher)
                document.getElementById('ref_pago_api_serial').value = p.serial_voucher || '';
                
                resultDiv.innerHTML = `
                    <div class="rf-alert rf-alert-success">
                        <i class="material-icons-round">verified</i>
                        <div>
                            <strong>Pago Verificado:</strong> S/. ${p.total || p.monto_total}<br>
                            <small style="opacity:0.8;">Recibo: ${p.serial_voucher || '---'}</small>
                        </div>
                    </div>`;
                document.getElementById('ref_es_manual').value = "0";
                document.getElementById('planBBox').style.display = 'none';
            } else {
                pagoDetectado = false;
                document.getElementById('ref_pago_api_serial').value = "";
                document.getElementById('ref_es_manual').value = "1";
                resultDiv.innerHTML = `
                    <div class="rf-alert rf-alert-warning">
                        <i class="material-icons-round">payments</i>
                        <div><strong>Pago no detectado:</strong> Adjunta voucher en paso 4.</div>
                    </div>`;
                document.getElementById('planBBox').style.display = 'block';
            }

            if (resData.estudiante_existente) fillStudentFields(resData.estudiante_existente);
            else await fetchReniecStudent(dni);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No se pudo verificar',
                text: data.message || 'El DNI ingresado no es válido o ya existe una inscripción activa.',
                confirmButtonColor: '#ec008c'
            });
            resultDiv.innerHTML = '';
        }
    } catch (e) { 
        console.error(e);
        Swal.fire({
            icon: 'error',
            title: 'Error de Conexión',
            text: 'No pudimos conectar con el servidor de verificación. Inténtalo de nuevo.',
            confirmButtonColor: '#ec008c'
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="material-icons-round">search</i>';
    }
}

async function fetchReniecStudent(dni) {
    try {
        const r = await fetch(`${getBaseUrl()}/api/public-reforzamiento/reniec/consultar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ dni })
        });
        const d = await r.json();
        if (d.success) fillStudentFields(d.data);
    } catch(e) {}
}

function fillStudentFields(est) {
    const fields = [
        { id: 'ref_nombre', val: est.nombre || est.nombres || '' },
        { id: 'ref_apellido_paterno', val: est.paterno || est.apellido_paterno || '' },
        { id: 'ref_apellido_materno', val: est.materno || est.apellido_materno || '' }
    ];

    fields.forEach(f => {
        const el = document.getElementById(f.id);
        if (el && f.val) {
            el.value = f.val;
            // Efecto Visual: Pintar verde momentáneamente
            el.style.transition = 'all 0.5s ease';
            el.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
            el.style.borderColor = '#10b981';
            el.style.boxShadow = '0 0 0 4px rgba(16, 185, 129, 0.1)';
            
            setTimeout(() => {
                el.style.backgroundColor = '';
                el.style.borderColor = '';
                el.style.boxShadow = '';
            }, 2000);
        }
    });

    // Notificación Premium
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: 'success',
            title: 'Datos Identificados',
            text: 'Se han autocompletado los campos correctamente.'
        });
    }
}

function activatePlanB() {
    pagoDetectado = false;
    document.getElementById('ref_es_manual').value = "1";
    document.getElementById('planBBox').style.display = 'none';
    Swal.fire({ icon: 'info', title: 'Modo Manual Activo', text: 'Podrás adjuntar tu voucher de pago en el paso 4.' });
}

// ==========================================
// REGIONAL & SCHOOLS
// ==========================================
async function loadDepartamentos() {
    const sel = document.getElementById('ref_dep');
    if (!sel) return;
    if (window.DEPARTAMENTOS_INICIALES && window.DEPARTAMENTOS_INICIALES.length > 0) {
        populateSelect(sel, window.DEPARTAMENTOS_INICIALES);
        return;
    }
    try {
        const r = await fetch(`${getBaseUrl()}/api/public-postulation/departamentos`);
        const d = await r.json();
        if (d.success) populateSelect(sel, d.departamentos);
    } catch(e) { sel.innerHTML = '<option value="">Error</option>'; }
}

async function handleDepChange(e) {
    const dep = e.target.value;
    const ps = document.getElementById('ref_prov');
    const ds = document.getElementById('ref_dist');
    ps.innerHTML = '<option value="">Cargando...</option>';
    ps.disabled = true;
    ds.innerHTML = '<option value="">-- Provincia --</option>';
    ds.disabled = true;
    clearColegioSelection();
    if (!dep) return;
    try {
        const r = await fetch(`${getBaseUrl()}/api/public-postulation/provincias/${encodeURIComponent(dep)}`);
        const d = await r.json();
        if (d.success) { populateSelect(ps, d.provincias); ps.disabled = false; }
    } catch(e) {}
}

async function handleProvChange(e) {
    const dep = document.getElementById('ref_dep').value;
    const prov = e.target.value;
    const ds = document.getElementById('ref_dist');
    ds.innerHTML = '<option value="">Cargando...</option>';
    ds.disabled = true;
    clearColegioSelection();
    if (!prov) return;
    try {
        const r = await fetch(`${getBaseUrl()}/api/public-postulation/distritos/${encodeURIComponent(dep)}/${encodeURIComponent(prov)}`);
        const d = await r.json();
        if (d.success) { populateSelect(ds, d.distritos); ds.disabled = false; }
    } catch(e) {}
}

function handleDistChange() {
    const searchInput = document.getElementById('ref_search_colegio');
    const dist = document.getElementById('ref_dist').value;
    if (searchInput) {
        searchInput.disabled = !dist;
        searchInput.placeholder = dist ? "Busca tu colegio..." : "Elige distrito primero";
    }
    clearColegioSelection();
}

async function performSchoolSearch(termino) {
    const dep = document.getElementById('ref_dep').value;
    const prov = document.getElementById('ref_prov').value;
    const dist = document.getElementById('ref_dist').value;
    const sugar = document.getElementById('sugerencias-colegios');

    if (!dist || termino.length < 2) { sugar.style.display = 'none'; return; }
    sugar.innerHTML = '<div class="rf-search-item" style="color:var(--rf-cyan); text-align:center;"><i class="fas fa-spinner fa-spin mr-2"></i> Buscando colegios...</div>';
    sugar.style.display = 'block';

    try {
        const queryParams = new URLSearchParams({
            departamento: dep,
            provincia: prov,
            distrito: dist,
            termino: termino
        });
        const r = await fetch(`${getBaseUrl()}/api/public-postulation/buscar-colegios?${queryParams.toString()}`);
        const d = await r.json();
        if (d.success && d.colegios.length) {
            let h = '';
            d.colegios.forEach(c => {
                const nombre = c.nombre || c.cen_edu || 'Sin nombre';
                const nivel = c.nivel || c.d_niv_mod || 'I.E.';
                const direccion = c.direccion || c.dir_cen || 'Sin dirección';
                
                h += `
                <div class="rf-search-item" onclick="seleccionarColegio(${c.id}, '${nombre.replace(/'/g, "\\'")}')" style="padding:1rem;">
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <div style="background:rgba(0,174,239,0.08); color:var(--rf-cyan); width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid rgba(0,174,239,0.2);">
                            <i class="material-icons-round" style="font-size:1.4rem;">school</i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; color:var(--rf-navy); font-size:1rem; line-height:1.2; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">${nombre}</div>
                            <div style="font-size:0.75rem; color:var(--rf-text-muted); display:flex; align-items:center; gap:0.6rem; margin-top:0.3rem;">
                                <span class="rf-badge rf-badge-cyan" style="font-size:0.65rem; padding:0.15rem 0.6rem; height:auto; letter-spacing:0; font-weight:800; background:rgba(0,174,239,0.1); border:1px solid rgba(0,174,239,0.2);">${nivel}</span>
                                <span style="display:flex; align-items:center; gap:0.25rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <i class="fas fa-map-marker-alt" style="font-size:0.7rem; color:var(--rf-magenta);"></i> 
                                    ${direccion}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            sugar.innerHTML = h;
        } else {
            sugar.innerHTML = `
                <div class="rf-search-item" onclick="activarManual('${termino}')" style="background:rgba(236,0,140,0.03); border:1px dashed var(--rf-magenta); margin:0.5rem; border-radius:1rem; padding:1.2rem; text-align:center;">
                    <div style="color:var(--rf-magenta); margin-bottom:0.5rem;"><i class="material-icons-round" style="font-size:2rem;">help_outline</i></div>
                    <div style="font-weight:800; color:var(--rf-navy); font-size:1rem;">¿No aparece tu colegio?</div>
                    <div style="font-size:0.8rem; color:var(--rf-text-muted); margin-top:0.25rem;">Haz clic aquí para registrar: <strong style="color:var(--rf-magenta);">"${termino}"</strong> manualmente.</div>
                </div>`;
        }
    } catch(e) { sugar.style.display = 'none'; }
}

window.seleccionarColegio = function(id, name) {
    document.getElementById('ref_colegio_id_hidden').value = id;
    document.getElementById('ref_colegio_nombre_manual').value = name;
    document.getElementById('nombre_colegio_sel').textContent = name;
    document.getElementById('colegio_seleccionado_box').style.display = 'block';
    document.getElementById('sugerencias-colegios').style.display = 'none';
    document.getElementById('ref_search_colegio').value = name;
};

window.activarManual = function(termino) {
    document.getElementById('ref_colegio_id_hidden').value = '';
    document.getElementById('ref_colegio_nombre_manual').value = termino;
    document.getElementById('nombre_colegio_sel').textContent = termino + ' (Manual)';
    document.getElementById('colegio_seleccionado_box').style.display = 'block';
    document.getElementById('sugerencias-colegios').style.display = 'none';
    document.getElementById('ref_search_colegio').value = termino;
};

window.clearColegio = function() {
    document.getElementById('ref_colegio_id_hidden').value = '';
    document.getElementById('ref_colegio_nombre_manual').value = '';
    document.getElementById('colegio_seleccionado_box').style.display = 'none';
    document.getElementById('ref_search_colegio').value = '';
};

function clearColegioSelection() { if (document.getElementById('colegio_seleccionado_box')) window.clearColegio(); }

function populateSelect(select, items) {
    let h = '<option value="">— Seleccionar —</option>';
    items.forEach(it => {
        const val = typeof it === 'string' ? it : (it.nombre || it.id);
        const name = typeof it === 'string' ? it : it.nombre;
        h += `<option value="${val}">${name}</option>`;
    });
    select.innerHTML = h;
}

// ==========================================
// APODERADOS
// ==========================================
function reindexApoderados() {
    const cards = document.querySelectorAll('#apoderadosContainer .rf-card');
    cards.forEach((card, idx) => {
        const currentIdx = idx + 1;
        
        // Actualizar el Título Visual
        const title = card.querySelector('.rf-card-header strong');
        if (title) {
            title.innerHTML = `<i class="material-icons-round" style="font-size:1.2rem;">person</i> Apoderado N° ${currentIdx}`;
        }
        
        // Actualizar el Botón Eliminar (solo del 2 en adelante)
        const header = card.querySelector('.rf-card-header');
        let btnDel = header.querySelector('.rf-btn-delete-ap');
        
        if (currentIdx === 1) {
            if (btnDel) btnDel.remove();
        } else {
            if (!btnDel) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'rf-btn-text rf-btn-delete-ap';
                btn.innerHTML = '<i class="material-icons-round">delete</i>';
                btn.style.cssText = 'color:var(--rf-red); border:none; background:none; cursor:pointer;';
                btn.onclick = () => removeApoderado(card.id);
                header.appendChild(btn);
            }
        }

        // Re-indexar los inputs
        card.querySelectorAll('input, select').forEach(el => {
            const name = el.getAttribute('name');
            if (name && name.startsWith('apoderados[')) {
                const newName = name.replace(/apoderados\[\d+\]/, `apoderados[${currentIdx}]`);
                el.setAttribute('name', newName);
            }
            // Actualizar IDs para prevenir colisiones
            if (el.id) {
                const newId = el.id.replace(/_\d+$/, `_${currentIdx}`);
                el.id = newId;
            }
        });

        // Actualizar el botón de búsqueda para el nuevo índice
        const btnSearch = card.querySelector('button[onclick^="consultarApoderado"]');
        if (btnSearch) {
            btnSearch.setAttribute('onclick', `consultarApoderado(${currentIdx})`);
        }
    });
    apoderadoCount = cards.length;
}

function addApoderadoForm() {
    const container = document.getElementById('apoderadosContainer');
    if (!container) return;
    
    apoderadoCount++;
    const currentIdx = apoderadoCount;

    const div = document.createElement('div');
    div.className = 'rf-card animate__animated animate__fadeInUp';
    div.id = `ap_card_${Date.now()}`; // ID único temporal
    
    div.innerHTML = `
        <div class="rf-card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <strong style="color:var(--rf-magenta); display:flex; align-items:center; gap:0.5rem;">
                <i class="material-icons-round" style="font-size:1.2rem;">person</i> 
                Apoderado N° ${currentIdx}
            </strong>
        </div>
        <div class="rf-grid-2" style="margin-top:1rem;">
            <div class="rf-form-group">
                <label class="rf-label">DNI Apoderado <span style="color:var(--rf-red);">*</span></label>
                <div style="display:flex; gap:0.5rem; position:relative;">
                    <input type="text" name="apoderados[${currentIdx}][dni]" maxlength="8" class="rf-input" id="ap_dni_${currentIdx}" placeholder="Ingresa DNI">
                    <button type="button" class="rf-btn rf-btn-magenta" style="width:50px; height:50px; padding:0; flex-shrink:0;" onclick="consultarApoderado(${currentIdx})">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="rf-form-group">
                <label class="rf-label">Parentesco <span style="color:var(--rf-red);">*</span></label>
                <select name="apoderados[${currentIdx}][parentesco]" class="rf-select">
                    <option value="PADRE">PADRE</option>
                    <option value="MADRE">MADRE</option>
                    <option value="TUTOR">TUTOR</option>
                    <option value="OTRO">OTRO</option>
                </select>
            </div>
        </div>
        
        <div class="rf-form-group">
            <label class="rf-label">Nombres Completos</label>
            <input type="text" name="apoderados[${currentIdx}][nombre]" class="rf-input" id="ap_nom_${currentIdx}" placeholder="Se autocompletará con DNI">
        </div>
        
        <div class="rf-grid-2">
            <div class="rf-form-group">
                <label class="rf-label">Ap. Paterno</label>
                <input type="text" name="apoderados[${currentIdx}][apellido_paterno]" class="rf-input" id="ap_pat_${currentIdx}">
            </div>
            <div class="rf-form-group">
                <label class="rf-label">Ap. Materno</label>
                <input type="text" name="apoderados[${currentIdx}][apellido_materno]" class="rf-input" id="ap_mat_${currentIdx}">
            </div>
        </div>
        
        <div class="rf-form-group" style="margin-bottom:0.5rem;">
            <label class="rf-label">Celular / Teléfono <span style="color:var(--rf-red);">*</span></label>
            <input type="text" name="apoderados[${currentIdx}][telefono]" class="rf-input" placeholder="987 654 321" maxlength="9">
        </div>
    `;
    
    container.appendChild(div);
    reindexApoderados();

    // Búsqueda automática al escribir DNI
    const dniInput = div.querySelector('input[id^="ap_dni_"]');
    if (dniInput) {
        dniInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length === 8) {
                // Obtenemos el ID dinámico actual del input
                const currentId = this.id.match(/\d+/)[0];
                consultarApoderado(currentId);
            }
        });
    }
}

window.removeApoderado = function(cardId) {
    const el = document.getElementById(cardId);
    if (el) { 
        el.classList.replace('animate__fadeInUp', 'animate__fadeOutDown'); 
        setTimeout(() => {
            el.remove();
            reindexApoderados();
        }, 400); 
    }
};

window.consultarApoderado = async function(idx) {
    const dniInput = document.getElementById(`ap_dni_${idx}`);
    const dni = dniInput.value.trim();
    if (dni.length !== 8) return;

    const btn = dniInput.nextElementSibling;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; 
    btn.disabled = true;

    try {
        const r = await fetch(`${getBaseUrl()}/api/public-reforzamiento/reniec/consultar`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ dni })
        });
        
        const d = await r.json();
        if (d.success) {
            const est = d.data;
            const nomField = document.getElementById(`ap_nom_${idx}`);
            const patField = document.getElementById(`ap_pat_${idx}`);
            const matField = document.getElementById(`ap_mat_${idx}`);
            
            if (nomField) nomField.value = est.nombre || est.nombres || '';
            if (patField) patField.value = est.paterno || est.apellido_paterno || '';
            if (matField) matField.value = est.materno || est.apellido_materno || '';
            
            // Animación de éxito (destello verde)
            [nomField, patField, matField].forEach(f => {
                if (f) {
                    f.style.transition = 'all 0.4s ease';
                    f.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                    f.style.borderColor = '#10b981';
                    setTimeout(() => {
                        f.style.backgroundColor = '';
                        f.style.borderColor = '';
                    }, 2000);
                }
            });

            if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                });
                Toast.fire({ icon: 'success', title: 'Apoderado Identificado' });
            }
        } else {
            Swal.fire({ icon: 'info', title: 'DNI no encontrado', text: 'Por favor, ingresa los datos de los padres manualmente.', confirmButtonColor: '#ec008c' });
        }
    } catch(e) { 
        console.error("Error apoderado:", e);
    } finally { 
        btn.innerHTML = oldHtml; 
        btn.disabled = false; 
    }
};

// ==========================================
// DOCUMENTS (DRAG & DROP)
// ==========================================
function initDropzones() {
    document.querySelectorAll('.rf-dropzone').forEach(zone => {
        const fileInput = zone.querySelector('input[type="file"]');
        if (!fileInput) return;
        // El input ya tiene inset:0, el zone.onclick sobraba y disparaba doble explorador
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('rf-dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('rf-dragover'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault(); zone.classList.remove('rf-dragover');
            if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; handleFileSelected(zone, fileInput); }
        });
        fileInput.addEventListener('change', () => handleFileSelected(zone, fileInput));
    });
}

function handleFileSelected(zone, fileInput) {
    const file = fileInput.files[0];
    if (!file) return;
    zone.classList.add('rf-has-file');
    const preview = zone.querySelector('.rf-file-preview');
    if (preview) {
        preview.style.display = 'flex';
        preview.style.alignItems = 'center';
        preview.style.gap = '0.75rem';
        
        // Template base
        preview.innerHTML = `
            <div id="thumb_${zone.id}" style="width:48px; height:48px; border-radius:10px; overflow:hidden; background:rgba(16,185,129,0.1); display:flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid rgba(16,185,129,0.2);">
                <i class="material-icons-round" style="color:var(--rf-green); font-size:1.6rem;">task_alt</i>
            </div>
            <div style="text-align:left; overflow:hidden; flex:1;">
                <div style="font-size:0.85rem; font-weight:800; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; color:var(--rf-navy);">${file.name}</div>
                <div style="font-size:0.75rem; color:var(--rf-text-muted); font-weight:600;">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
            </div>
            <button type="button" onclick="event.stopPropagation(); window.removeFileInModal('${zone.id}')" style="background:none; border:none; color:var(--rf-red); cursor:pointer;">
                <i class="material-icons-round" style="font-size:1.2rem;">cancel</i>
            </button>
        `;

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const thumb = document.getElementById(`thumb_${zone.id}`);
                if (thumb) thumb.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
            };
            reader.readAsDataURL(file);
        }
    }
}

// Helper para quitar archivos
window.removeFileInModal = function(zoneId) {
    const zone = document.getElementById(zoneId);
    if (!zone) return;
    const input = zone.querySelector('input[type="file"]');
    if (input) input.value = '';
    zone.classList.remove('rf-has-file');
    const preview = zone.querySelector('.rf-file-preview');
    if (preview) {
        preview.style.display = 'none';
        preview.innerHTML = '';
    }
};

// ==========================================
// FINAL SUBMIT & SUMMARY
// ==========================================
function generateSummary() {
    const sum = document.getElementById('reforzamientoResumen');
    if (!sum) return;

    const nombre = document.getElementById('ref_nombre').value;
    const paterno = document.getElementById('ref_apellido_paterno').value;
    const materno = document.getElementById('ref_apellido_materno').value;
    const dni = document.getElementById('ref_dni').value;
    const grado = document.querySelector('[name="grado"]').value;
    const seccion = document.querySelector('[name="seccion"]').value;
    const colegio = document.getElementById('nombre_colegio_sel').textContent;
    const apiSerial = document.getElementById('ref_pago_api_serial').value;

    // Obtener vista previa de la foto
    const fotoInput = document.querySelector('input[name="foto"]');
    let fotoUrl = null;
    if (fotoInput && fotoInput.files && fotoInput.files[0]) {
        fotoUrl = URL.createObjectURL(fotoInput.files[0]);
    }

    // Obtener datos de apoderados
    const apoderados = [];
    document.querySelectorAll('#apoderadosContainer .rf-card').forEach((card, i) => {
        const idx = i + 1;
        const dni = card.querySelector(`[id^="ap_dni_"]`).value;
        const nombre = card.querySelector(`[id^="ap_nom_"]`).value;
        const paterno = card.querySelector(`[id^="ap_pat_"]`).value;
        const parentesco = card.querySelector(`select`).value;
        apoderados.push({ dni, nombre: `${nombre} ${paterno}`, parentesco });
    });

    sum.innerHTML = `
        <div class="summary-premium-card animate__animated animate__zoomIn" style="padding: 2.5rem; background: #fff; border-radius: 2rem;">
            <div style="display: flex; gap: 2rem; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 2rem; flex-wrap: wrap;">
                <div style="width: 140px; height: 160px; border-radius: 1.5rem; background: #f8fafc; border: 4px solid #fff; box-shadow: 0 15px 35px rgba(0,0,0,0.1); overflow: hidden; flex-shrink: 0;">
                    ${fotoUrl ? `<img src="${fotoUrl}" style="width: 100%; height: 100%; object-fit: cover;">` : `<div style="height:100%; display:flex; align-items:center; justify-content:center; flex-direction:column; color:#cbd5e1; background:#f1f5f9;"><i class="material-icons-round" style="font-size:3.5rem;">account_circle</i><small>Sin foto</small></div>`}
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem;">
                        <span class="rf-badge rf-badge-magenta" style="padding: 0.2rem 0.8rem; font-size: 0.7rem;">ESTUDIANTE</span>
                        <span style="color: var(--rf-text-muted); font-size: 0.85rem; font-weight: 600;">#${Math.floor(Math.random() * 90000) + 10000}</span>
                    </div>
                    <h4 style="font-family: var(--rf-font-titles); font-weight: 900; font-size: 1.6rem; color: var(--rf-navy); margin: 0; line-height: 1.1;">${nombre} <br> ${paterno} ${materno}</h4>
                    <div style="display: flex; gap: 0.8rem; margin-top: 1rem; flex-wrap: wrap;">
                        <span class="rf-badge rf-badge-cyan"><i class="material-icons-round" style="font-size: 1rem; margin-right: 0.3rem;">badge</i> ${dni}</span>
                        <span class="rf-badge rf-badge-magenta"><i class="material-icons-round" style="font-size: 1rem; margin-right: 0.3rem;">school</i> ${grado}° SECUNDARIA</span>
                    </div>
                </div>
            </div>

            <div class="rf-grid-2" style="margin-bottom: 1.5rem;">
                <div class="rf-card" style="margin:0; padding:1.25rem; border-left: 4px solid var(--rf-cyan);">
                    <div style="font-weight: 800; font-size: 0.75rem; color: var(--rf-text-muted); text-transform: uppercase; margin-bottom: 0.8rem; display: flex; align-items: center; gap: 0.4rem;">
                        <i class="material-icons-round" style="font-size: 1rem; color: var(--rf-cyan);">domain</i> Institución Educativa
                    </div>
                    <div style="font-weight:800; color:var(--rf-navy); font-size:0.9rem; line-height: 1.3;">${colegio}</div>
                    <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem; color: var(--rf-text-muted); font-size: 0.8rem;">
                        <i class="material-icons-round" style="font-size: 1rem; color: var(--rf-magenta);">schedule</i> Turno: ${seccion.split(' (')[0]}
                    </div>
                </div>
                <div class="rf-card" style="margin:0; padding:1.25rem; border: 2px solid ${pagoDetectado ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)'}; background: ${pagoDetectado ? 'rgba(16, 185, 129, 0.02)' : 'rgba(239, 68, 68, 0.02)'};">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem;">
                        <div style="font-weight: 800; font-size: 0.75rem; color: var(--rf-text-muted); text-transform: uppercase;">Estado del Pago</div>
                        <div style="font-weight: 900; color: var(--rf-magenta); font-size: 0.95rem;">S/. 200.00</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: ${pagoDetectado ? 'var(--rf-green)' : 'var(--rf-red)'}; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="material-icons-round" style="font-size: 1.4rem;">${pagoDetectado ? 'verified' : 'priority_high'}</i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 900; color: var(--rf-navy); font-size: 1rem;">${pagoDetectado ? 'VERIFICADO' : 'POR VALIDAR'}</div>
                            <div style="font-size: 0.7rem; color: var(--rf-text-muted); margin-top: 0.1rem;">${pagoDetectado ? 'Recibo: ' + (apiSerial || '---') : 'Voucher adjuntado manual'}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rf-card" style="margin:0 0 1.5rem 0; padding:1.25rem; border-left: 4px solid var(--rf-magenta);">
                <div style="font-weight: 800; font-size: 0.75rem; color: var(--rf-text-muted); text-transform: uppercase; margin-bottom: 1rem;">Padres o Apoderados Responsables</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    ${apoderados.map(a => `
                        <div style="background: #f8fafc; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #f1f5f9;">
                            <div style="font-weight: 800; color: var(--rf-navy); font-size: 0.85rem;">${a.nombre}</div>
                            <div style="display: flex; justify-content: space-between; margin-top: 0.25rem;">
                                <span style="font-size: 0.7rem; color: var(--rf-magenta); font-weight: 700;">${a.parentesco}</span>
                                <span style="font-size: 0.7rem; color: var(--rf-text-muted);">DNI: ${a.dni}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(0, 174, 239, 0.05) 100%); border: 1px solid rgba(16, 185, 129, 0.2); padding: 1rem 1.25rem; border-radius: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.05);">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #fff; color: var(--rf-green); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <i class="material-icons-round" style="font-size: 1.6rem;">check_circle</i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 900; color: #065f46; font-size: 0.95rem;">Documentación en Orden</div>
                    <div style="font-size: 0.8rem; color: #065f46; opacity: 0.8; font-weight: 600;">Se han anexado Foto, DNIs, Voucher y Certificado.</div>
                </div>
            </div>
        </div>`;
}

async function handleFinalSubmit(e) {
    e.preventDefault();
    const errors = validateCurrentStep();
    if (errors.length > 0) { Swal.fire('Campos faltantes', errors.join('<br>'), 'warning'); return; }

    const btn = document.getElementById('btnSubmit');
    const fd = new FormData(e.target);
    
    // Inyectar datos clave
    fd.set('dni', document.getElementById('ref_dni').value);
    fd.set('es_manual', !pagoDetectado);
    if (!pagoDetectado) {
        fd.set('voucher_secuencia', document.querySelector('[name="voucher_secuencia"]')?.value || '');
        fd.set('monto_voucher', document.querySelector('[name="monto_voucher"]')?.value || '200.00');
        fd.set('voucher_fecha', document.querySelector('[name="voucher_fecha"]')?.value || '');
    } else {
        fd.set('pago_api_serial', apiSerial || '');
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="rf-spin">cached</span> Procesando...';

    try {
        const r = await fetch(`${getBaseUrl()}/api/public-reforzamiento/register`, { 
            method: 'POST', body: fd,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const res = await r.json();
        if (res.success) {
            // Mostrar Vista de Éxito Ultra-Premium
            const modalBody = document.querySelector('.rf-body');
            const wizardLayout = document.querySelector('.rf-layout'); 
            
            // Si no encontramos el layout rf-layout, usamos el modal body directo
            const targetContainer = wizardLayout || modalBody;

            targetContainer.innerHTML = `
                <div class="text-center py-5 animate__animated animate__fadeIn" style="background:#fff; border-radius: 2rem;">
                    <div class="mb-4">
                        <img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" width="120" class="animate__animated animate__bounceIn">
                    </div>
                    <h2 style="font-family: var(--rf-font-titles); font-weight: 900; color: var(--rf-navy); font-size: 2.2rem; margin-bottom: 0.5rem;">¡Inscripción Registrada!</h2>
                    <p style="color: var(--rf-text-muted); font-size: 1.1rem; margin-bottom: 2.5rem; font-weight: 600;">Hemos recibido tu expediente digital correctamente.</p>
                    
                    <div style="max-width: 550px; margin: 0 auto; background: #f8fafc; border-radius: 1.5rem; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.02);">
                        <div style="background: linear-gradient(135deg, #1A237E 0%, #311B92 100%); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: rgba(255,255,255,0.8); font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Estado de tu Solicitud</span>
                            <span style="background: #fbbf24; color: #78350f; padding: 0.4rem 1rem; border-radius: 0.5rem; font-weight: 900; font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="material-icons-round" style="font-size: 1.1rem;">history</i> PENDIENTE
                            </span>
                        </div>
                        <div style="padding: 2rem; text-align: left;">
                            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(0,174,239,0.1); color: var(--rf-cyan); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="material-icons-round">description</i>
                                </div>
                                <div>
                                    <h6 style="font-weight: 800; color: var(--rf-navy); margin-bottom: 0.2rem; font-size: 1rem;">Documentación en Revisión</h6>
                                    <p style="color: var(--rf-text-muted); font-size: 0.85rem; margin: 0; line-height: 1.4;">El equipo administrativo de la UNAMAD está validando tu DNI y el voucher de pago adjuntado.</p>
                                </div>
                            </div>
                            <div style="display: flex; gap: 1rem;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(236,0,140,0.1); color: var(--rf-magenta); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="material-icons-round">notifications_active</i>
                                </div>
                                <div>
                                    <h6 style="font-weight: 800; color: var(--rf-navy); margin-bottom: 0.2rem; font-size: 1rem;">Notificación de Resultados</h6>
                                    <p style="color: var(--rf-text-muted); font-size: 0.85rem; margin: 0; line-height: 1.4;">Se te notificará vía correo electrónico y llamada/WhatsApp cuando tu vacante sea confirmada.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 3rem;">
                        <button type="button" class="rf-btn rf-btn-magenta" style="padding: 1.2rem 4rem; font-size: 1.1rem; border-radius: 1.25rem;" onclick="location.reload()">
                            Entendido, Finalizar <i class="material-icons-round" style="margin-left: 0.5rem;">done_all</i>
                        </button>
                    </div>
                </div>
            `;
            
            // Ocultar Footer y Sidebar si existen
            document.querySelector('.rf-footer')?.style.setProperty('display', 'none', 'important');
            document.querySelector('.rf-sidebar')?.style.setProperty('display', 'none', 'important');

        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Error interno.' });
        }
    } catch(e) { Swal.fire({ icon: 'error', title: 'Error de Red' }); }
    finally { btn.disabled = false; btn.innerHTML = 'Finalizar Inscripción'; }
}

window.openReforzamientoModal = function() {
    const modal = document.getElementById('reforzamientoModal');
    if (modal) { modal.classList.add('modal-open'); currentStep = 1; updateWizardUI(); }
};

window.closeReforzamientoModal = function() {
    const modal = document.getElementById('reforzamientoModal');
    if (modal) modal.classList.remove('modal-open');
};
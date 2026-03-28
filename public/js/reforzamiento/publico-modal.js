/**
 * JS para el Modal de Inscripción de Reforzamiento - Premium v7.4
 * Restauración de versión completa con todas las funcionalidades Premium.
 */

let currentStep = 1;
const totalSteps = 5;
let apoderadoCount = 0;
let debounceTimer;
let slideDirection = 'forward';
let pagoDetectado = false; 

const stepHeaders = {
    1: { title: 'Datos del Estudiante', desc: 'Ingresa tu DNI o completa tus datos personales.' },
    2: { title: 'Apoderados y Tutores', desc: 'Registra los datos de tus padres o tutor legal.' },
    3: { title: 'Información Académica', desc: 'Grado, turno y centro educativo del estudiante.' },
    4: { title: 'Documentos Requeridos', desc: 'Sube tus documentos arrastrando o seleccionando archivos.' },
    5: { title: 'Confirmar y Enviar', desc: 'Revisa tus datos antes de finalizar la inscripción.' }
};

// Inicialización robusta
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOnLoad);
} else {
    initOnLoad();
}

function initOnLoad() {
    if (!window.reforzamientoInitialized) {
        initPremiumWizard();
        window.reforzamientoInitialized = true;
    }
}

function getBaseUrl() {
    let url = window.APP_URL || window.location.origin;
    if (url.endsWith('/')) {
        url = url.slice(0, -1);
    }
    return url;
}

function initPremiumWizard() {
    const modal = document.getElementById('reforzamientoModal');
    if (!modal) return;

    console.log("Reforzamiento Wizard Initialized. Base URL:", getBaseUrl());

    // Navigation
    const btnNext = document.getElementById('btnNext');
    if (btnNext) btnNext.addEventListener('click', nextStep);
    
    const btnPrev = document.getElementById('btnPrev');
    if (btnPrev) btnPrev.addEventListener('click', prevStep);
    
    // Core actions
    const btnVerifyDni = document.getElementById('btnVerifyDni');
    if (btnVerifyDni) btnVerifyDni.addEventListener('click', verifyDni);
    
    const btnAddApoderado = document.getElementById('btnAddApoderado');
    if (btnAddApoderado) btnAddApoderado.addEventListener('click', addApoderadoForm);
    
    // Desactivar NiceSelect en TODO el modal para evitar problemas visuales (oscuros, bloqueos de scroll)
    if (window.jQuery && jQuery.fn.niceSelect) {
        jQuery('#reforzamientoModal select').niceSelect('destroy');
    }

    // School search  
    const refDep = document.getElementById('ref_dep');
    const refProv = document.getElementById('ref_prov');
    const refDist = document.getElementById('ref_dist');

    if (refDep) refDep.addEventListener('change', handleDepChange);
    if (refProv) refProv.addEventListener('change', handleProvChange);
    if (refDist) refDist.addEventListener('change', handleDistChange);
    
    const searchInput = document.getElementById('ref_search_colegio');
    const btnSearch = document.getElementById('btnBuscarColegio');
    
    if (searchInput && btnSearch) {
        let debounceTimer;
        
        // Búsqueda al hacer clic
        btnSearch.addEventListener('click', () => {
            performSchoolSearch(searchInput.value);
        });

        // Búsqueda dinámica mientras se escribe (debounce)
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSchoolSearch(e.target.value);
            }, 600);
        });
    }

    // Form submit
    const form = document.getElementById('reforzamientoForm');
    if (form) {
        form.addEventListener('submit', handleFinalSubmit);
        // Prevent accidental browser validation on enter
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
            if (this.value.length === 8) {
                verifyDni();
            }
        });
    }

    // Inline validation setup
    setupInlineValidation();

    // Drag & Drop
    initDropzones();

    // One initial guardian if none
    if (apoderadoCount === 0) addApoderadoForm();
    
    // Load departments
    loadDepartamentos();
    updateProgressBar();
    updateWizardUI(); // Initial state
}



// ==========================================
// INLINE VALIDATION
// ==========================================
function setupInlineValidation() {
    const fieldsToValidate = [
        { id: 'ref_nombre', type: 'text', minLen: 2 },
        { id: 'ref_apellido_paterno', type: 'text', minLen: 2 },
        { id: 'ref_apellido_materno', type: 'text', minLen: 2 },
        { id: 'ref_telefono', type: 'phone', minLen: 9 },
    ];

    fieldsToValidate.forEach(field => {
        const el = document.getElementById(field.id);
        if (!el) return;
        el.addEventListener('blur', () => validateField(el, field));
        el.addEventListener('input', () => {
            el.classList.remove('is-valid', 'is-invalid');
            const icon = el.parentElement.querySelector('.rf-field-icon');
            if (icon) icon.classList.remove('show', 'valid', 'invalid');
        });
    });
}

function validateField(el, config) {
    const value = el.value.trim();
    const icon = el.parentElement.querySelector('.rf-field-icon');
    
    if (config.minLen === 0 && !value) return true;

    let isValid = value.length >= config.minLen;
    if (config.type === 'phone') isValid = /^[0-9]{9}$/.test(value);

    el.classList.toggle('is-valid', isValid);
    el.classList.toggle('is-invalid', !isValid && value.length > 0);

    if (icon) {
        icon.classList.add('show');
        icon.classList.toggle('valid', isValid);
        icon.classList.toggle('invalid', !isValid && value.length > 0);
        icon.textContent = isValid ? 'check_circle' : 'error';
    }
    return isValid;
}

// ==========================================
// STEP VALIDATION
// ==========================================
function validateCurrentStep() {
    let errors = [];

    if (currentStep === 1) {
        const nombre = document.getElementById('ref_nombre').value.trim();
        const paterno = document.getElementById('ref_apellido_paterno').value.trim();
        const materno = document.getElementById('ref_apellido_materno').value.trim();
        const telefono = document.getElementById('ref_telefono').value.trim();
        const dni = document.getElementById('ref_dni').value.trim();

        if (!dni || dni.length !== 8) errors.push('DNI del estudiante (8 dígitos)');
        if (!nombre || nombre.length < 2) errors.push('Nombres completos');
        if (!paterno || paterno.length < 2) errors.push('Apellido paterno');
        if (!materno || materno.length < 2) errors.push('Apellido materno');
        if (!telefono || !/^[0-9]{9}$/.test(telefono)) errors.push('Teléfono (9 dígitos)');
    }

    if (currentStep === 2) {
        const apoderados = document.querySelectorAll('#apoderadosContainer .rf-card');
        if (apoderados.length === 0) {
            errors.push('Debe registrar al menos un apoderado');
        }
        apoderados.forEach((card, i) => {
            const idx = i + 1;
            const dniEl = card.querySelector(`[name*="[dni]"]`);
            const nomEl = card.querySelector(`[name*="[nombre]"]`);
            const patEl = card.querySelector(`[name*="[apellido_paterno]"]`);
            const telEl = card.querySelector(`[name*="[telefono]"]`);

            if (dniEl && (!dniEl.value.trim() || dniEl.value.trim().length !== 8))
                errors.push(`Apoderado ${idx}: DNI invalido`);
            if (nomEl && (!nomEl.value.trim() || nomEl.value.trim().length < 2))
                errors.push(`Apoderado ${idx}: Nombres incompletos`);
            if (patEl && (!patEl.value.trim() || patEl.value.trim().length < 2))
                errors.push(`Apoderado ${idx}: Apellido Paterno incompleto`);
            if (telEl && (!telEl.value.trim() || telEl.value.trim().length < 7))
                errors.push(`Apoderado ${idx}: Teléfono`);
        });
    }

    if (currentStep === 3) {
        const dep = document.getElementById('ref_dep').value;
        const prov = document.getElementById('ref_prov').value;
        const dist = document.getElementById('ref_dist').value;
        const colId = document.getElementById('ref_colegio_id_hidden').value;
        const grado = document.querySelector('[name="grado"]').value;

        if (!dep) errors.push('Falta Departamento');
        if (!prov) errors.push('Falta Provincia');
        if (!dist) errors.push('Falta Distrito');
        if (!colId) errors.push('Debe seleccionar un Centro Educativo de la lista');
        if (!grado) errors.push('Falta Grado');
    }

    if (currentStep === 4) {
        const foto = document.querySelector('[name="foto"]');
        const dniFile = document.querySelector('[name="dni_pdf"]');
        
        if (!foto || !foto.files || foto.files.length === 0) errors.push('Falta subir la Foto');
        if (!dniFile || !dniFile.files || dniFile.files.length === 0) errors.push('Falta subir Copia de DNI');

        const esManual = document.getElementById('ref_es_manual').value === "1";
        if (!pagoDetectado || esManual) {
            const vFile = document.querySelector('[name="voucher_img"]');
            const vSeq = document.querySelector('[name="voucher_secuencia"]');
            const vMonto = document.querySelector('[name="monto_voucher"]').value;
            
            if (!vFile || !vFile.files || vFile.files.length === 0) errors.push('Falta adjuntar Voucher de pago');
            if (!vSeq || !vSeq.value.trim()) errors.push('Falta N° Secuencia del Recibo');
            if (!vMonto || parseFloat(vMonto) < 200) errors.push('Monto del voucher insuficiente (mín. S/. 200.00)');
        }
    }

    return errors;
}

// ==========================================
// DRAG & DROP
// ==========================================
function initDropzones() {
    document.querySelectorAll('.rf-dropzone').forEach(zone => {
        const fileInput = zone.querySelector('input[type="file"]');
        if (!fileInput) return;

        // zone.onclick = () => fileInput.click(); // Redundante, el input ya cubre toda la zona con inset:0 y abre nativamente el explorador

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('rf-dragover');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('rf-dragover'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('rf-dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelected(zone, fileInput);
            }
        });
        fileInput.addEventListener('change', () => handleFileSelected(zone, fileInput));
    });
}

function handleFileSelected(zone, fileInput) {
    const file = fileInput.files[0];
    if (!file) return;

    zone.classList.add('rf-has-file');
    const previewEl = zone.querySelector('.rf-file-preview');
    const textEl = zone.querySelector('.rf-dropzone-text');
    const iconEl = zone.querySelector('.rf-dropzone-icon');

    if (textEl) textEl.style.display = 'none';
    if (iconEl) {
        iconEl.textContent = 'check_circle';
        iconEl.style.color = 'var(--rf-green)';
    }

    if (previewEl) {
        previewEl.style.display = 'flex';
        previewEl.style.flexDirection = 'column';
        previewEl.style.gap = '0.5rem';
        
        const isImage = file.type.startsWith('image/');
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        
        let html = `
            <div style="display:flex; align-items:center; gap:0.6rem; width:100%; background:rgba(16,185,129,0.05); padding:0.6rem; border-radius:0.75rem; border:1px solid rgba(16,185,129,0.2); min-width:0;">
                <div id="thumb_${zone.id}" style="width:40px; height:40px; border-radius:0.5rem; background:#fff; display:flex; align-items:center; justify-content:center; overflow:hidden; border:1px solid #e2e8f0; flex-shrink:0;">
                    <span class="material-icons-round" style="color:#94a3b8; font-size:1.5rem;">${isImage ? 'image' : 'picture_as_pdf'}</span>
                </div>
                <div style="flex:1; text-align:left; overflow:hidden; min-width:0;">
                    <div style="font-size:0.8rem; font-weight:700; color:var(--rf-navy); white-space:nowrap; text-overflow:ellipsis; overflow:hidden;" title="${file.name}">${file.name}</div>
                    <div style="font-size:0.7rem; color:var(--rf-text-muted); font-weight:600;">${sizeMB} MB</div>
                </div>
                <button type="button" onclick="removeFile(event, '${zone.id}')" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:0.25rem; display:flex; align-items:center; justify-content:center; border-radius:50%; transition:all 0.2s; flex-shrink:0;" title="Quitar archivo">
                    <span class="material-icons-round" style="font-size:1.2rem;">cancel</span>
                </button>
            </div>
        `;
        
        previewEl.innerHTML = html;

        if (isImage) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const thumbCont = document.getElementById(`thumb_${zone.id}`);
                if (thumbCont) {
                    thumbCont.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                }
            };
            reader.readAsDataURL(file);
        }
    }
}

window.removeFile = function(e, zoneId) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const zone = document.getElementById(zoneId);
    if (!zone) return;
    
    const fileInput = zone.querySelector('input[type="file"]');
    const previewEl = zone.querySelector('.rf-file-preview');
    const textEl = zone.querySelector('.rf-dropzone-text');
    const iconEl = zone.querySelector('.rf-dropzone-icon');
    
    if (fileInput) fileInput.value = "";
    zone.classList.remove('rf-has-file');
    
    if (textEl) textEl.style.display = 'block';
    if (previewEl) {
        previewEl.style.display = 'none';
        previewEl.innerHTML = '';
    }
    if (iconEl) {
        iconEl.style.color = '';
        if (zoneId.includes('foto')) iconEl.textContent = 'add_a_photo';
        if (zoneId.includes('dni')) iconEl.textContent = 'credit_card';
        if (zoneId.includes('constancia')) iconEl.textContent = 'article';
        if (zoneId.includes('voucher')) iconEl.textContent = 'receipt_long';
    }
}

// ==========================================
// NAVIGATION
// ==========================================
function updateProgressBar() {
    const fill = document.getElementById('rfProgressFill');
    if (!fill) return;
    fill.style.height = `${((currentStep - 1) / (totalSteps - 1)) * 100}%`;
}

function updateWizardUI() {
    // Show current panel
    document.querySelectorAll('.rf-panel').forEach((p, idx) => {
        p.classList.toggle('active', (idx + 1) === currentStep);
    });

    // Update steps sidebar
    document.querySelectorAll('.rf-nav-item').forEach((nav, idx) => {
        const stepNum = idx + 1;
        const numEl = nav.querySelector('.rf-step-num');
        nav.classList.remove('active', 'completed');
        
        if (stepNum === currentStep) {
            nav.classList.add('active');
            numEl.innerHTML = (stepNum < 10 ? '0' : '') + stepNum;
        } else if (stepNum < currentStep) {
            nav.classList.add('completed');
            numEl.innerHTML = '<span class="material-icons-round" style="font-size:1.1rem;">check</span>';
        } else {
            numEl.innerHTML = (stepNum < 10 ? '0' : '') + stepNum;
        }
    });

    updateProgressBar();

    // Headers
    const header = stepHeaders[currentStep];
    const titleEl = document.getElementById('rfHeaderTitle');
    const descEl = document.getElementById('rfHeaderDesc');
    if (titleEl && header) titleEl.textContent = header.title;
    if (descEl && header) descEl.textContent = header.desc;

    // Buttons
    document.getElementById('btnPrev').classList.toggle('hidden', currentStep === 1);
    const isLast = currentStep === totalSteps;
    document.getElementById('btnNext').classList.toggle('hidden', isLast);
    document.getElementById('btnSubmit').classList.toggle('hidden', !isLast);
    document.getElementById('currentStepText').textContent = currentStep;

    if (isLast) generateSummary();
}

function nextStep() {
    const errors = validateCurrentStep();
    if (errors.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos Requeridos',
            html: `<div style="text-align:left; font-size:0.85rem;">
                <p>Por favor completa lo siguiente para continuar:</p>
                <ul style="padding-left:1.5rem; margin-top:0.5rem; color:var(--rf-red);">
                    ${errors.map(e => `<li>${e}</li>`).join('')}
                </ul>
            </div>`,
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
// DNI & API LOGIC
// ==========================================
async function verifyDni() {
    const dniInput = document.getElementById('ref_dni');
    const dni = dniInput.value.trim();
    const btn = document.getElementById('btnVerifyDni');
    const resultDiv = document.getElementById('dniVerifyResult');

    if (dni.length !== 8) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons-round rf-spin">refresh</span>';

    try {
        const response = await fetch(`${getBaseUrl()}/api/public-reforzamiento/verify-dni/${dni}`);
        const data = await response.json();

        if (response.ok) {
            const resData = data.data;
            document.getElementById('ref_ciclo_id').value = resData.ciclo.id;

            if (resData.pago_encontrado) {
                pagoDetectado = true;
                resultDiv.innerHTML = `
                    <div class="rf-alert rf-alert-success">
                        <span class="material-icons-round">verified</span>
                        <div><strong>Pago Verificado:</strong> S/. ${resData.pago_encontrado.monto_total}</div>
                    </div>`;
                const esManualEl = document.getElementById('ref_es_manual');
                if (esManualEl) esManualEl.value = "0";
                
                const vZone = document.getElementById('voucherZone');
                if (vZone) vZone.classList.add('hidden');
            } else {
                pagoDetectado = false;
                const esManualEl = document.getElementById('ref_es_manual');
                if (esManualEl) esManualEl.value = "1";

                resultDiv.innerHTML = `
                    <div class="rf-alert rf-alert-warning">
                        <span class="material-icons-round">payments</span>
                        <div><strong>Pago no detectado:</strong> Deberás adjuntar tu voucher en el paso de documentos.</div>
                    </div>`;
                
                const vZone = document.getElementById('voucherZone');
                if (vZone) vZone.classList.remove('hidden');
            }

            if (resData.estudiante_existente) {
                fillStudentFields(resData.estudiante_existente);
            } else {
                await fetchReniecStudent(dni);
            }
        }
    } catch (e) { console.error(e); } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons-round">search</span>';
    }
}

async function fetchReniecStudent(dni) {
    try {
        const r = await fetch(`${getBaseUrl()}/api/reniec/consultar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ dni })
        });
        const d = await r.json();
        if (d.success) fillStudentFields(d.data);
    } catch(e) {}
}

function fillStudentFields(est) {
    document.getElementById('ref_nombre').value = est.nombre || est.nombres || '';
    document.getElementById('ref_apellido_paterno').value = est.apellido_paterno || '';
    document.getElementById('ref_apellido_materno').value = est.apellido_materno || '';
}

function activatePlanB() {
    pagoDetectado = false;
    document.getElementById('ref_es_manual').value = "1";
    document.getElementById('voucherZone').classList.remove('hidden');
    document.getElementById('planBBox').style.display = 'none';
    Swal.fire({ icon: 'info', title: 'Modo Manual', text: 'Podrás adjuntar tu voucher de pago en el paso 4.' });
}

// ==========================================
// REGIONAL & SCHOOLS
// ==========================================
async function loadDepartamentos() {
    const sel = document.getElementById('ref_dep');
    if (!sel) return;

    // Hidratación: Usar datos inyectados por el servidor si están disponibles
    const depData = window.DEPARTAMENTOS_INICIALES || [];
    
    if (depData.length > 0) {
        console.log("Cargando departamentos desde Hydration...");
        renderDepartamentos(sel, depData);
        sel.disabled = false;
    } else {
        console.log("Iniciando fetch de departamentos (fallback)...");
        try {
            const r = await fetch('/api/public/departamentos');
            if (r.ok) {
                const d = await r.json();
                if (d.success) {
                    renderDepartamentos(sel, d.departamentos);
                    sel.disabled = false;
                } else {
                    sel.innerHTML = '<option value="">Sin datos</option>';
                    sel.disabled = true;
                }
            }
        } catch(e) { 
            console.error("Error cargando departamentos:", e);
            sel.innerHTML = '<option value="">Error conexión</option>'; 
            sel.disabled = true;
        }
    }
}

function renderDepartamentos(sel, list) {
    let h = '<option value="">— Seleccionar —</option>';
    list.forEach(x => {
        const id = (typeof x === 'object') ? (x.id || x.nombre) : x;
        const nombre = (typeof x === 'object') ? x.nombre : x;
        h += `<option value="${id}">${nombre}</option>`;
    });
    sel.innerHTML = h;
}

// Helper para actualizar selectores
function safeUpdateSelect(sel, html) {
    if (!sel) return;
    sel.innerHTML = html;
}

async function handleDepChange(e) {
    const dep = e.target.value;
    const ps = document.getElementById('ref_prov');
    const ds = document.getElementById('ref_dist');
    const searchInput = document.getElementById('ref_search_colegio');
    const searchBtn = document.getElementById('btnBuscarColegio');
    
    if (!ps) return;
    
    safeUpdateSelect(ps, '<option value="">Cargando...</option>');
    ps.disabled = true;
    if (ds) {
        safeUpdateSelect(ds, '<option value="">Distrito</option>');
        ds.disabled = true;
    }
    if (searchInput) searchInput.disabled = true;
    if (searchBtn) searchBtn.disabled = true;
    
    if (!dep) {
        ps.innerHTML = '<option value="">Provincia</option>';
        ps.disabled = true;
        if (ds) {
            ds.innerHTML = '<option value="">Distrito</option>';
            ds.disabled = true;
        }
        return;
    }

    try {
        console.log("Cargando provincias para:", dep);
        const r = await fetch(`/api/public/provincias/${encodeURIComponent(dep.trim())}`);
        if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
        
        const d = await r.json();
        console.log("Respuesta provincias:", d);
        
        if (d.success && d.provincias) {
            let h = '<option value="">— Seleccionar —</option>';
            d.provincias.forEach(x => {
                const id = (typeof x === 'object') ? (x.id || x.nombre) : x;
                const nombre = (typeof x === 'object') ? x.nombre : x;
                h += `<option value="${id}">${nombre}</option>`;
            });
            ps.disabled = false;
            safeUpdateSelect(ps, h);
        } else {
            ps.disabled = false;
            safeUpdateSelect(ps, '<option value="">Sin provincias</option>');
        }
    } catch(e) { 
        console.error("Error cargando provincias:", e);
        ps.disabled = false;
        safeUpdateSelect(ps, '<option value="">Error conexión</option>');
    }
}

async function handleProvChange(e) {
    const depEl = document.getElementById('ref_dep');
    if (!depEl) return;
    const dep = depEl.value;
    const prov = e.target.value;
    const ds = document.getElementById('ref_dist');
    const searchInput = document.getElementById('ref_search_colegio');
    const searchBtn = document.getElementById('btnBuscarColegio');
    
    if (!ds) return;
    
    safeUpdateSelect(ds, '<option value="">Cargando...</option>');
    ds.disabled = true;
    if (searchInput) searchInput.disabled = true;
    if (searchBtn) searchBtn.disabled = true;
    
    if (!prov) {
        safeUpdateSelect(ds, '<option value="">Distrito</option>');
        ds.disabled = true;
        return;
    }

    try {
        console.log("Cargando distritos para:", dep, prov);
        const r = await fetch(`/api/public/distritos/${encodeURIComponent(dep.trim())}/${encodeURIComponent(prov.trim())}`);
        if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);

        const d = await r.json();
        console.log("Respuesta distritos:", d);

        if (d.success && d.distritos) {
            let h = '<option value="">— Seleccionar —</option>';
            d.distritos.forEach(x => {
                const id = (typeof x === 'object') ? (x.id || x.nombre) : x;
                const nombre = (typeof x === 'object') ? x.nombre : x;
                h += `<option value="${id}">${nombre}</option>`;
            });
            ds.disabled = false;
            safeUpdateSelect(ds, h);
        } else {
            ds.disabled = false;
            safeUpdateSelect(ds, '<option value="">Sin distritos</option>');
        }
    } catch(e) { 
        console.error("Error cargando distritos:", e);
        ds.disabled = false;
        safeUpdateSelect(ds, '<option value="">Error conexión</option>');
    }
}

async function handleDistChange(e) {
    const dist = e.target.value;
    const searchInput = document.getElementById('ref_search_colegio');
    const searchBtn = document.getElementById('btnBuscarColegio');
    
    if (dist) {
        if (searchInput) searchInput.disabled = false;
        if (searchBtn) searchBtn.disabled = false;
        if (searchInput) searchInput.focus();
    } else {
        if (searchInput) {
            searchInput.disabled = true;
            searchInput.value = "";
        }
        if (searchBtn) searchBtn.disabled = true;
    }
}

async function performSchoolSearch(termino) {
    const dep = document.getElementById('ref_dep').value;
    const prov = document.getElementById('ref_prov').value;
    const dist = document.getElementById('ref_dist').value;
    const sugar = document.getElementById('sugerencias-colegios');
    if (!sugar) return;

    if (!dist) {
        sugar.innerHTML = "";
        sugar.style.display = 'none';
        return;
    }

    // Preparar datos para el servidor
    const payload = { 
        departamento: dep.trim(), 
        provincia: prov.trim(), 
        distrito: dist.trim() 
    };
    
    // Solo incluir término si es válido para el validator (min:2)
    if (termino && termino.trim().length >= 2) {
        payload.termino = termino.trim();
    }

    sugar.innerHTML = '<div class="rf-search-item" style="color:var(--rf-cyan); text-align:center;"><i class="rf-spin material-icons-round mr-2">cached</i> Buscando...</div>';
    sugar.style.display = 'block';

    try {
        const r = await fetch('/api/public/buscar-colegios', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (!r.ok) throw new Error('Error en la búsqueda');

        const d = await r.json();
        if (d.success && d.colegios && d.colegios.length) {
            let h = '';
            d.colegios.forEach(c => {
                const nombre = c.cen_edu || c.nombre || 'Sin nombre';
                const nivel = c.d_niv_mod || c.nivel || '';
                const direccion = c.dir_cen || c.direccion || '';
                
                h += `<div class="rf-search-item" onclick="seleccionarColegio(${c.id}, '${nombre.replace(/'/g, "\\'")}')">
                    <strong>${nombre}</strong><br><small>${nivel} • ${direccion}</small>
                </div>`;
            });
            sugar.innerHTML = h;
        } else {
            sugar.innerHTML = '<div class="rf-search-item" style="text-align:center; color:var(--rf-text-muted);">No se encontraron colegios en este distrito</div>';
        }
    } catch(e) { 
        console.error("Search error:", e);
        sugar.innerHTML = '<div class="rf-search-item" style="color:var(--rf-red); text-align:center;">Error al conectar con el servidor</div>';
    }
}

window.seleccionarColegio = function(id, name) {
    document.getElementById('ref_colegio_id_hidden').value = id;
    document.getElementById('nombre_colegio_sel').textContent = name;
    document.getElementById('colegio_seleccionado_box').style.display = 'block';
    document.getElementById('sugerencias-colegios').style.display = 'none';
};

window.clearColegio = function() {
    document.getElementById('ref_colegio_id_hidden').value = '';
    document.getElementById('colegio_seleccionado_box').style.display = 'none';
};

// ==========================================
// APODERADOS
// ==========================================
function addApoderadoForm() {
    apoderadoCount++;
    const container = document.getElementById('apoderadosContainer');
    const div = document.createElement('div');
    div.className = 'rf-card';
    div.id = `apoderado_card_${apoderadoCount}`;
    div.innerHTML = `
        <div class="rf-card-header">
            <strong>Apoderado N° ${apoderadoCount}</strong>
            ${apoderadoCount > 1 ? `<span class="material-icons-round rf-btn-icon-del" onclick="removeApoderado(${apoderadoCount})" title="Eliminar">delete_outline</span>` : ''}
        </div>
        <div class="rf-grid-2">
            <div class="rf-form-group">
                <label class="rf-label">DNI Apoderado</label>
                <div style="display:flex; gap:0.5rem;">
                    <input type="text" name="apoderados[${apoderadoCount}][dni]" maxlength="8" class="rf-input" id="ap_dni_${apoderadoCount}">
                    <button type="button" class="rf-btn rf-btn-magenta" style="width:50px; padding:0;" onclick="consultarApoderado(${apoderadoCount})">
                        <span class="material-icons-round">search</span>
                    </button>
                </div>
            </div>
            <div class="rf-form-group">
                <label class="rf-label">Parentesco</label>
                <select name="apoderados[${apoderadoCount}][parentesco]" class="rf-select">
                    <option value="PADRE">PADRE</option>
                    <option value="MADRE">MADRE</option>
                    <option value="TUTOR">TUTOR</option>
                </select>
            </div>
        </div>
        <div class="rf-form-group">
            <label class="rf-label">Nombres Completos</label>
            <input type="text" name="apoderados[${apoderadoCount}][nombre]" class="rf-input" id="ap_nom_${apoderadoCount}">
        </div>
        <div class="rf-grid-2">
            <div class="rf-form-group">
                <label class="rf-label">Ap. Paterno</label>
                <input type="text" name="apoderados[${apoderadoCount}][apellido_paterno]" class="rf-input" id="ap_pat_${apoderadoCount}">
            </div>
            <div class="rf-form-group">
                <label class="rf-label">Ap. Materno</label>
                <input type="text" name="apoderados[${apoderadoCount}][apellido_materno]" class="rf-input" id="ap_mat_${apoderadoCount}">
            </div>
        </div>
        <div class="rf-form-group" style="margin-bottom:0.5rem;">
            <label class="rf-label">Teléfono de contacto</label>
            <input type="text" name="apoderados[${apoderadoCount}][telefono]" class="rf-input" maxlength="9">
        </div>
    `;
    container.appendChild(div);
}

window.removeApoderado = function(idx) {
    const card = document.getElementById(`apoderado_card_${idx}`);
    if (card) {
        card.remove();
        // Opcional: Renegociar IDs si es necesario, pero para envío simple de FormData funciona así
    }
};

window.consultarApoderado = async function(idx) {
    const dni = document.getElementById(`ap_dni_${idx}`).value;
    if (dni.length !== 8) return;
    try {
        const r = await fetch(`${getBaseUrl()}/api/reniec/consultar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ dni })
        });
        const d = await r.json();
        if (d.success) {
            document.getElementById(`ap_nom_${idx}`).value = d.data.nombres;
            document.getElementById(`ap_pat_${idx}`).value = d.data.apellido_paterno;
            document.getElementById(`ap_mat_${idx}`).value = d.data.apellido_materno;
        }
    } catch(e) {}
};

// ==========================================
// FINAL SUBMIT
// ==========================================
function generateSummary() {
    const sum = document.getElementById('reforzamientoResumen');
    if (!sum) return;

    // Obtener datos
    const nom = document.getElementById('ref_nombre').value;
    const pat = document.getElementById('ref_apellido_paterno').value;
    const mat = document.getElementById('ref_apellido_materno').value;
    const dni = document.getElementById('ref_dni').value;
    
    const gradoSelect = document.querySelector('[name="grado"]');
    const gradoVal = gradoSelect.options[gradoSelect.selectedIndex].text;
    const turnoSelect = document.querySelector('[name="seccion"]');
    const turnoVal = turnoSelect.options[turnoSelect.selectedIndex].text;
    const colegioNom = document.getElementById('nombre_colegio_sel').textContent;

    // Detectar foto para preview
    const fotoInput = document.querySelector('[name="foto"]');
    let fotoUrl = '';
    if (fotoInput && fotoInput.files && fotoInput.files[0]) {
        fotoUrl = URL.createObjectURL(fotoInput.files[0]);
    }

    sum.innerHTML = `
        <div class="rf-summary-layout" style="display:grid; grid-template-columns: 240px 1fr; gap:1.5rem;">
            <!-- Perfil lateral -->
            <div style="display:flex; flex-direction:column; align-items:center; space-between; background:#fff; border-radius:1.5rem; padding:1.5rem; border:1px solid #e2e8f0; text-align:center;">
                <div style="width:130px; height:130px; border-radius:1rem; overflow:hidden; border:4px solid var(--rf-bg-light); box-shadow:0 10px 25px rgba(0,0,0,0.1); margin-bottom:1rem; background:#f1f5f9; display:flex; align-items:center; justify-content:center;">
                    ${fotoUrl ? `<img src="${fotoUrl}" style="width:100%; height:100%; object-fit:cover;">` : '<span class="material-icons-round" style="font-size:4rem; color:#cbd5e1;">person</span>'}
                </div>
                <h4 style="margin:0; color:var(--rf-navy); font-size:1.1rem; line-height:1.2;">${nom}<br>${pat} ${mat}</h4>
                <div style="margin-top:0.75rem; padding:0.4rem 1rem; background:var(--rf-bg-light); border-radius:2rem; font-size:0.85rem; font-weight:700; color:var(--rf-text-muted);">DNI: ${dni}</div>
                
                <div style="margin-top:auto; width:100%; pt:1rem;">
                    <div class="rf-badge ${pagoDetectado ? 'rf-badge-cyan' : 'rf-badge-magenta'}" style="width:100%; justify-content:center; padding:0.6rem;">
                        <span class="material-icons-round" style="font-size:1rem;">${pagoDetectado ? 'verified' : 'account_balance_wallet'}</span>
                        ${pagoDetectado ? 'PAGO DETECTADO' : 'PAGARÁS POR VOUCHER'}
                    </div>
                </div>
            </div>

            <!-- Detalles en Grid -->
            <div style="display:flex; flex-direction:column; gap:1.25rem;">
                <!-- Sección Académica -->
                <div class="rf-card" style="margin:0; padding:1.25rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem; color:var(--rf-cyan);">
                        <span class="material-icons-round">school</span>
                        <h4 style="margin:0; font-size:0.9rem; font-weight:800; text-transform:uppercase; letter-spacing:1px;">Datos Académicos</h4>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <div>
                            <div style="font-size:0.75rem; color:var(--rf-text-muted);">Grado / Nivel</div>
                            <div style="font-weight:700; color:var(--rf-navy);">${gradoVal}</div>
                        </div>
                        <div>
                            <div style="font-size:0.75rem; color:var(--rf-text-muted);">Turno</div>
                            <div style="font-weight:700; color:var(--rf-navy);">${turnoVal}</div>
                        </div>
                        <div style="grid-column: span 2;">
                            <div style="font-size:0.75rem; color:var(--rf-text-muted);">Institución Educativa</div>
                            <div style="font-weight:700; color:var(--rf-navy); display:flex; align-items:center; gap:0.4rem;">
                                <span class="material-icons-round" style="font-size:1.1rem; color:var(--rf-cyan);">domain</span>
                                ${colegioNom || 'No seleccionado'}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Apoderados -->
                <div class="rf-card" style="margin:0; padding:1.25rem; border-color:rgba(236,0,140,0.15);">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem; color:var(--rf-magenta);">
                        <span class="material-icons-round">family_restroom</span>
                        <h4 style="margin:0; font-size:0.9rem; font-weight:800; text-transform:uppercase; letter-spacing:1px;">Padres / Tutores</h4>
                    </div>
                    <div id="summaryApoderadosList" style="display:flex; flex-direction:column; gap:0.75rem;">
                        <!-- Se llena dinámicamente -->
                        ${Array.from(document.querySelectorAll('#apoderadosContainer .rf-card')).map(card => {
                            const name = card.querySelector('[name*="[nombre]"]').value;
                            const d = card.querySelector('[name*="[dni]"]').value;
                            const par = card.querySelector('[name*="[parentesco]"]').value;
                            return `
                                <div style="display:flex; justify-content:space-between; align-items:center; padding:0.6rem 1rem; background:rgba(236,0,140,0.03); border-radius:0.75rem;">
                                    <div>
                                        <div style="font-weight:700; color:var(--rf-navy); font-size:0.9rem;">${name}</div>
                                        <div style="font-size:0.75rem; color:var(--rf-text-muted);">DNI: ${d}</div>
                                    </div>
                                    <span class="rf-badge rf-badge-magenta" style="font-size:0.7rem;">${par}</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
}

async function handleFinalSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmit');
    const fd = new FormData(e.target);
    fd.set('dni', document.getElementById('ref_dni').value);

    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons-round rf-spin">cached</span> Enviando...';

    try {
        const r = await fetch(`${getBaseUrl()}/api/public-reforzamiento/register`, { method: 'POST', body: fd });
        const res = await r.json();
        if (r.ok) {
            Swal.fire({ icon: 'success', title: '¡Registro Exitoso!', text: 'Se ha enviado tu inscripción correctamente.' })
                .then(() => location.href = '/login');
        } else {
            Swal.fire({ icon: 'error', title: 'Error en el registro', text: res.message || 'Por favor verifica los datos e intenta nuevamente.' });
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error de conexión' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Finalizar Inscripción';
    }
}

window.openReforzamientoModal = function() {
    document.getElementById('reforzamientoModal').classList.add('modal-open');
    currentStep = 1;
    updateWizardUI();
    // Re-cargar departamentos por si acaso
    loadDepartamentos();
};

window.closeReforzamientoModal = function() {
    document.getElementById('reforzamientoModal').classList.remove('modal-open');
};
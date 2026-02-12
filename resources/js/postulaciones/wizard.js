// public/js/postulaciones/wizard.js
// JavaScript para el wizard de postulaciones

// Variables globales del wizard
let wizardCurrentStep = 1;
const wizardTotalSteps = 3;
let wizardFormData = {};

// Función para mostrar/ocultar contraseña
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const icon = passwordInput.nextElementSibling?.querySelector('svg');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (icon) {
            icon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            `;
        }
    } else {
        passwordInput.type = 'password';
        if (icon) {
            icon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
        }
    }
}

// Función para consultar DNI en RENIEC
async function consultarDNI(tipo) {
    let dniInput, btnBuscar;

    if (tipo === 'postulante') {
        dniInput = document.getElementById('numero_documento');
        btnBuscar = document.getElementById('btn_buscar_postulante');
    } else if (tipo === 'padre') {
        dniInput = document.getElementById('padre_numero_documento');
        btnBuscar = document.getElementById('btn_buscar_padre');
    } else if (tipo === 'madre') {
        dniInput = document.getElementById('madre_numero_documento');
        btnBuscar = document.getElementById('btn_buscar_madre');
    }

    if (!dniInput || !btnBuscar) return;

    const dni = dniInput.value.trim();

    if (dni.length !== 8 || !/^\d{8}$/.test(dni)) {
        alert('El DNI debe tener exactamente 8 dígitos numéricos');
        dniInput.focus();
        return;
    }

    const btnTextoOriginal = btnBuscar.innerHTML;
    btnBuscar.disabled = true;
    btnBuscar.classList.add('loading');
    btnBuscar.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const response = await fetch('/api/reniec/consultar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ dni: dni })
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const result = await response.json();

        if (result.success && result.data) {
            if (tipo === 'postulante') {
                autocompletarPostulante(result.data);
            } else if (tipo === 'padre') {
                autocompletarPadre(result.data);
            } else if (tipo === 'madre') {
                autocompletarMadre(result.data);
            }

            if (typeof toastr !== 'undefined') {
                toastr.success('Datos cargados desde RENIEC correctamente');
            } else {
                alert('Datos cargados desde RENIEC correctamente');
            }
            
            btnBuscar.classList.add('success-animation');
            setTimeout(() => btnBuscar.classList.remove('success-animation'), 1000);
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.info(result.message || 'No se encontraron datos para el DNI ingresado');
            } else {
                alert(result.message || 'No se encontraron datos para el DNI ingresado');
            }
        }
    } catch (error) {
        console.error('Error al consultar RENIEC:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('No se pudo consultar el servicio RENIEC. Intente nuevamente.');
        } else {
            alert('No se pudo consultar el servicio RENIEC. Intente nuevamente.');
        }
    } finally {
        btnBuscar.disabled = false;
        btnBuscar.classList.remove('loading');
        btnBuscar.innerHTML = btnTextoOriginal;
    }
}

function autocompletarPostulante(datos) {
    const fields = [
        { id: 'nombres', value: datos.nombres },
        { id: 'apellido_paterno', value: datos.apellido_paterno },
        { id: 'apellido_materno', value: datos.apellido_materno },
        { id: 'fecha_nacimiento', value: datos.fecha_nacimiento },
        { id: 'genero', value: datos.genero },
        { id: 'direccion', value: datos.direccion }
    ];

    fields.forEach((field, index) => {
        setTimeout(() => {
            const element = document.getElementById(field.id);
            if (element && field.value) {
                element.value = field.value;
                element.classList.add('auto-filled');
                setTimeout(() => element.classList.remove('auto-filled'), 500);
            }
        }, index * 100);
    });

    const tipoDoc = document.getElementById('tipo_documento');
    if (tipoDoc) tipoDoc.value = 'DNI';
}

function autocompletarPadre(datos) {
    const apellidos = (datos.apellido_paterno || '') + ' ' + (datos.apellido_materno || '');
    setTimeout(() => {
        if (datos.nombres) {
            const nombreField = document.getElementById('padre_nombres');
            if (nombreField) {
                nombreField.value = datos.nombres;
                nombreField.classList.add('auto-filled');
                setTimeout(() => nombreField.classList.remove('auto-filled'), 500);
            }
        }
        if (apellidos.trim()) {
            const apellidoField = document.getElementById('padre_apellidos');
            if (apellidoField) {
                apellidoField.value = apellidos.trim();
                apellidoField.classList.add('auto-filled');
                setTimeout(() => apellidoField.classList.remove('auto-filled'), 500);
            }
        }
        const tipoDoc = document.getElementById('padre_tipo_documento');
        if (tipoDoc) tipoDoc.value = 'DNI';
    }, 200);
}

function autocompletarMadre(datos) {
    const apellidos = (datos.apellido_paterno || '') + ' ' + (datos.apellido_materno || '');
    setTimeout(() => {
        if (datos.nombres) {
            const nombreField = document.getElementById('madre_nombres');
            if (nombreField) {
                nombreField.value = datos.nombres;
                nombreField.classList.add('auto-filled');
                setTimeout(() => nombreField.classList.remove('auto-filled'), 500);
            }
        }
        if (apellidos.trim()) {
            const apellidoField = document.getElementById('madre_apellidos');
            if (apellidoField) {
                apellidoField.value = apellidos.trim();
                apellidoField.classList.add('auto-filled');
                setTimeout(() => apellidoField.classList.remove('auto-filled'), 500);
            }
        }
        const tipoDoc = document.getElementById('madre_tipo_documento');
        if (tipoDoc) tipoDoc.value = 'DNI';
    }, 200);
}

function updatePasswordStrength(password) {
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');

    if (!strengthBar || !strengthText) return;

    let strength = 0;
    let feedback = '';

    if (password.length >= 8) strength += 20;
    if (/[a-z]/.test(password)) strength += 20;
    if (/[A-Z]/.test(password)) strength += 20;
    if (/[0-9]/.test(password)) strength += 20;
    if (/[^A-Za-z0-9]/.test(password)) strength += 20;

    if (strength === 0) {
        feedback = 'Ingrese una contraseña';
        strengthBar.className = 'strength-bar';
    } else if (strength <= 40) {
        feedback = 'Contraseña débil';
        strengthBar.className = 'strength-bar weak';
    } else if (strength <= 60) {
        feedback = 'Contraseña regular';
        strengthBar.className = 'strength-bar fair';
    } else if (strength <= 80) {
        feedback = 'Contraseña buena';
        strengthBar.className = 'strength-bar good';
    } else {
        feedback = 'Contraseña excelente';
        strengthBar.className = 'strength-bar strong';
    }

    strengthBar.style.width = Math.min(strength, 100) + '%';
    strengthText.textContent = feedback;
}

// Funciones del wizard
function updateWizardDisplay() {
    console.log('Actualizando wizard display, paso actual:', wizardCurrentStep);
    
    // Actualizar indicadores de paso
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        const stepNum = index + 1;
        indicator.classList.remove('active', 'completed');

        if (stepNum < wizardCurrentStep) {
            indicator.classList.add('completed');
            const stepNumber = indicator.querySelector('.step-number');
            const stepCheck = indicator.querySelector('.step-check');
            if (stepNumber) stepNumber.style.display = 'none';
            if (stepCheck) stepCheck.classList.remove('d-none');
        } else if (stepNum === wizardCurrentStep) {
            indicator.classList.add('active');
            const stepNumber = indicator.querySelector('.step-number');
            const stepCheck = indicator.querySelector('.step-check');
            if (stepNumber) stepNumber.style.display = 'block';
            if (stepCheck) stepCheck.classList.add('d-none');
        } else {
            const stepNumber = indicator.querySelector('.step-number');
            const stepCheck = indicator.querySelector('.step-check');
            if (stepNumber) stepNumber.style.display = 'block';
            if (stepCheck) stepCheck.classList.add('d-none');
        }
    });

    // Actualizar pasos del wizard
    document.querySelectorAll('.wizard-step').forEach((step, index) => {
        if (index + 1 === wizardCurrentStep) {
            step.style.display = 'block';
            step.classList.add('active');
            step.style.opacity = '0';
            step.style.transform = 'translateX(50px)';
            setTimeout(() => {
                step.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                step.style.opacity = '1';
                step.style.transform = 'translateX(0)';
            }, 50);
        } else {
            step.style.display = 'none';
            step.classList.remove('active');
        }
    });

    // Actualizar botones
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const stepCounter = document.getElementById('currentStep');

    if (stepCounter) {
        stepCounter.textContent = wizardCurrentStep;
    }

    if (prevBtn) {
        prevBtn.style.display = wizardCurrentStep === 1 ? 'none' : 'block';
    }

    if (wizardCurrentStep === wizardTotalSteps) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'block';
        generateConfirmationSummary();
    } else {
        if (nextBtn) nextBtn.style.display = 'block';
        if (submitBtn) submitBtn.style.display = 'none';
    }
}

function updateFieldProgress() {
    const currentStepElement = document.querySelector(`.wizard-step[data-step="${wizardCurrentStep}"]`);
    if (!currentStepElement) return;

    const allFields = currentStepElement.querySelectorAll('input, select');
    const completed = Array.from(allFields).filter(field => field.value.trim() !== '').length;

    const counter = document.getElementById(`step${wizardCurrentStep}Counter`);
    if (counter) {
        counter.textContent = `${completed} de ${allFields.length} campos completados`;
    }

    const miniProgressBar = document.querySelector(`.mini-progress-bar[data-step="${wizardCurrentStep}"]`);
    if (miniProgressBar) {
        const stepProgress = (completed / allFields.length) * 100;
        miniProgressBar.style.width = stepProgress + '%';
    }

    // Actualizar progreso general
    let totalFields = 0;
    let completedFields = 0;
    
    for (let step = 1; step <= wizardTotalSteps; step++) {
        const stepElement = document.querySelector(`.wizard-step[data-step="${step}"]`);
        if (stepElement) {
            const fields = stepElement.querySelectorAll('input, select');
            totalFields += fields.length;
            
            if (step < wizardCurrentStep) {
                completedFields += fields.length; // Pasos anteriores están completos
            } else if (step === wizardCurrentStep) {
                completedFields += Array.from(fields).filter(field => field.value.trim() !== '').length;
            }
        }
    }

    const overallProgress = totalFields > 0 ? (completedFields / totalFields) * 100 : 0;
    const overallProgressBar = document.getElementById('overallProgressBar');
    const overallPercentage = document.getElementById('overallPercentage');
    
    if (overallProgressBar) {
        overallProgressBar.style.width = overallProgress + '%';
    }
    if (overallPercentage) {
        overallPercentage.textContent = Math.round(overallProgress) + '%';
    }
}

function celebrateStepCompletion() {
    const currentStepIndicator = document.querySelector(`.step-indicator[data-step="${wizardCurrentStep - 1}"]`);
    if (currentStepIndicator) {
        currentStepIndicator.style.animation = 'pulse 0.6s ease-in-out';
        setTimeout(() => {
            currentStepIndicator.style.animation = '';
        }, 600);
    }
}

function validateWizardStep(step) {
    const currentStepElement = document.querySelector(`.wizard-step[data-step="${step}"]`);
    if (!currentStepElement) return false;
    
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    // Validaciones específicas por paso
    if (step === 1) {
        // Validar contraseñas coincidan
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirmation');
        
        if (password && passwordConfirm) {
            if (password.value !== passwordConfirm.value) {
                passwordConfirm.classList.add('is-invalid');
                isValid = false;
                if (typeof toastr !== 'undefined') {
                    toastr.error('Las contraseñas no coinciden');
                }
            } else {
                passwordConfirm.classList.remove('is-invalid');
            }
        }
    }

    if (step === 2 && !validateParentEmails()) {
        isValid = false;
    }

    return isValid;
}

function validateParentEmails() {
    const padreEmail = document.getElementById('padre_email');
    const madreEmail = document.getElementById('madre_email');
    
    if (!padreEmail || !madreEmail) return true;

    const padreValue = padreEmail.value.trim();
    const madreValue = madreEmail.value.trim();

    if (padreValue && madreValue && padreValue === madreValue) {
        padreEmail.classList.add('is-invalid');
        madreEmail.classList.add('is-invalid');
        if (typeof toastr !== 'undefined') {
            toastr.error('Los correos del padre y la madre deben ser diferentes');
        }
        return false;
    }

    padreEmail.classList.remove('is-invalid');
    madreEmail.classList.remove('is-invalid');
    return true;
}

function saveCurrentStepData() {
    const allInputs = document.querySelectorAll('#postulacionUnificadaForm input, #postulacionUnificadaForm select');
    
    allInputs.forEach(input => {
        if (input.type !== 'file') {
            wizardFormData[input.name] = input.value;
        }
    });
}

function generateConfirmationSummary() {
    const summaryContainer = document.getElementById('confirmationSummary');
    if (!summaryContainer) return;

    let html = '';

    // Datos del Postulante
    html += `
        <div class="confirmation-section">
            <h5 class="confirmation-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Datos del Postulante
            </h5>
            <div class="confirmation-data">
                <div class="data-row">
                    <span class="data-label">Tipo Documento:</span>
                    <span class="data-value">${document.getElementById('tipo_documento')?.value || 'No especificado'}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Número:</span>
                    <span class="data-value">${document.getElementById('numero_documento')?.value || 'No especificado'}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nombres:</span>
                    <span class="data-value">${document.getElementById('nombres')?.value || 'No especificado'}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Apellidos:</span>
                    <span class="data-value">${(document.getElementById('apellido_paterno')?.value || '') + ' ' + (document.getElementById('apellido_materno')?.value || '')}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Email:</span>
                    <span class="data-value">${document.getElementById('email')?.value || 'No especificado'}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Teléfono:</span>
                    <span class="data-value">${document.getElementById('telefono')?.value || 'No especificado'}</span>
                </div>
            </div>
        </div>
    `;

    // Datos del Padre
    html += `
        <div class="confirmation-section">
            <h5 class="confirmation-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                </svg>
                Datos del Padre
            </h5>
            <div class="confirmation-data">
                <div class="data-row">
                    <span class="data-label">Documento:</span>
                    <span class="data-value">${(document.getElementById('padre_tipo_documento')?.value || '') + ' ' + (document.getElementById('padre_numero_documento')?.value || '')}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nombres:</span>
                    <span class="data-value">${document.getElementById('padre_nombres')?.value || 'No especificado'}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Apellidos:</span>
                    <span class="data-value">${document.getElementById('padre_apellidos')?.value || 'No especificado'}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Teléfono:</span>
                    <span class="data-value">${document.getElementById('padre_telefono')?.value || 'No especificado'}</span>
                </div>
            </div>
        </div>
    `;

    // Datos de la Madre
    html += `
        <div class="confirmation-section">
            <h5 class="confirmation-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                </svg>
                Datos de la Madre
            </h5>
            <div class="confirmation-data">
                <div class="data-row">
                    <span class="data-label">Documento:</span>
                    <span class="data-value">${(document.getElementById('madre_tipo_documento')?.value || '') + ' ' + (document.getElementById('madre_numero_documento')?.value || '')}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nombres:</span>
                    <span class="data-value">${document.getElementById('madre_nombres')?.value || 'No especificado'}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Apellidos:</span>
                    <span class="data-value">${document.getElementById('madre_apellidos')?.value || 'No especificado'}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Teléfono:</span>
                    <span class="data-value">${document.getElementById('madre_telefono')?.value || 'No especificado'}</span>
                </div>
            </div>
        </div>
    `;

    summaryContainer.innerHTML = html;
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('Wizard inicializado correctamente');

    // Configurar variables globales
    window.default_server = window.default_server || document.querySelector('meta[name="app-url"]')?.getAttribute('content') || '';
    window.csrf_token = window.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Configurar eventos de navegación
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            console.log('Click en siguiente, paso actual:', wizardCurrentStep);
            
            if (validateWizardStep(wizardCurrentStep)) {
                saveCurrentStepData();
                celebrateStepCompletion();
                
                if (wizardCurrentStep < wizardTotalSteps) {
                    wizardCurrentStep++;
                    updateWizardDisplay();
                    updateFieldProgress();
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Por favor complete todos los campos requeridos');
                } else {
                    alert('Por favor complete todos los campos requeridos');
                }
            }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            console.log('Click en anterior, paso actual:', wizardCurrentStep);
            
            if (wizardCurrentStep > 1) {
                wizardCurrentStep--;
                updateWizardDisplay();
                updateFieldProgress();
            }
        });
    }

    // Agregar eventos a todos los campos del formulario
    document.querySelectorAll('.form-control-wizard, .form-select').forEach(field => {
        field.addEventListener('input', function() {
            if (this.id === 'password') {
                updatePasswordStrength(this.value);
            }
            updateFieldProgress();
        });

        field.addEventListener('change', function() {
            updateFieldProgress();
        });
    });

    // Eventos para buscar RENIEC con Enter
    ['numero_documento', 'padre_numero_documento', 'madre_numero_documento'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const tipo = id.includes('padre') ? 'padre' : id.includes('madre') ? 'madre' : 'postulante';
                    consultarDNI(tipo);
                }
            });
        }
    });

    // Inicializar display del wizard
    updateWizardDisplay();
    updateFieldProgress();
});

// Exponer funciones globalmente para uso en HTML
window.togglePassword = togglePassword;
window.consultarDNI = consultarDNI;
window.updatePasswordStrength = updatePasswordStrength;

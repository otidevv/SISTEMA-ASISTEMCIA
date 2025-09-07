/**
 * Wizard Simplificado para Postulantes Existentes
 * Solo 4 pasos: Confirmar datos, Datos académicos, Documentos, Confirmación
 */
(function() {
    let currentStep = 1;
    const totalSteps = 4;
    let postulanteData = null;
    
    // Elementos del DOM
    const form = document.getElementById('formPostulacionSimplificado');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnSiguiente = document.getElementById('btnSiguiente');
    const btnEnviar = document.getElementById('btnEnviar');
    const progressBar = document.getElementById('wizardProgress');
    
    // Función de inicialización que será llamada desde el parent
    window.initWizardSimplificado = function(userData) {
        postulanteData = userData;
        initializeWizard();
    };
    
    function initializeWizard() {
        console.log('Inicializando wizard simplificado para:', postulanteData);
        
        // Eventos de navegación
        btnSiguiente.addEventListener('click', nextStep);
        btnAnterior.addEventListener('click', previousStep);
        
        // Evento de envío del formulario
        form.addEventListener('submit', handleSubmit);
        
        // Actualizar estado inicial
        updateWizardState();
    }
    
    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                // Si estamos en el paso 3, generar resumen
                if (currentStep === 3) {
                    generateSummary();
                }
                
                currentStep++;
                updateWizardState();
            }
        }
    }
    
    function previousStep() {
        if (currentStep > 1) {
            currentStep--;
            updateWizardState();
        }
    }
    
    function updateWizardState() {
        // Ocultar todos los pasos
        document.querySelectorAll('.wizard-content').forEach(content => {
            content.style.display = 'none';
            content.classList.remove('active');
        });
        
        // Mostrar paso actual
        const currentContent = document.querySelector(`[data-step="${currentStep}"]`);
        if (currentContent) {
            currentContent.style.display = 'block';
            currentContent.classList.add('active');
        }
        
        // Actualizar indicadores de paso
        document.querySelectorAll('.wizard-step').forEach(step => {
            step.classList.remove('active', 'completed');
            const stepNum = parseInt(step.getAttribute('data-step'));
            
            if (stepNum < currentStep) {
                step.classList.add('completed');
            } else if (stepNum === currentStep) {
                step.classList.add('active');
            }
        });
        
        // Actualizar barra de progreso
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        progressBar.style.width = progress + '%';
        
        // Actualizar botones
        btnAnterior.style.display = currentStep === 1 ? 'none' : 'inline-block';
        btnSiguiente.style.display = currentStep === totalSteps ? 'none' : 'inline-block';
        btnEnviar.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
        
        // Scroll al inicio del contenedor
        const modalBody = document.querySelector('.modal-body');
        if (modalBody) {
            modalBody.scrollTop = 0;
        }
    }
    
    function validateCurrentStep() {
        const currentContent = document.querySelector(`.wizard-content[data-step="${currentStep}"]`);
        const requiredFields = currentContent.querySelectorAll('[required]');
        let isValid = true;
        
        // Paso 1: No requiere validación (solo confirmación)
        if (currentStep === 1) {
            return true;
        }
        
        // Validar campos requeridos
        requiredFields.forEach(field => {
            if (field.type === 'checkbox') {
                if (!field.checked) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            } else {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            }
        });
        
        // Validaciones específicas para paso 3 (documentos)
        if (currentStep === 3) {
            const fotoInput = document.getElementById('foto');
            const certificadoInput = document.getElementById('certificado_estudios');
            const voucherInput = document.getElementById('voucher_pago');
            
            if (!fotoInput.files.length) {
                fotoInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!certificadoInput.files.length) {
                certificadoInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!voucherInput.files.length) {
                voucherInput.classList.add('is-invalid');
                isValid = false;
            }
        }
        
        if (!isValid) {
            toastr.warning('Por favor, complete todos los campos obligatorios', 'Campos requeridos');
        }
        
        return isValid;
    }
    
    function generateSummary() {
        const resumenDiv = document.getElementById('resumenPostulacion');
        
        // Obtener datos del formulario
        const formData = new FormData(form);
        
        // Datos del estudiante (ya registrado)
        let resumenHTML = `
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Datos del Estudiante</h6>
                    <p><strong>Nombre:</strong> ${postulanteData.nombre} ${postulanteData.apellido_paterno} ${postulanteData.apellido_materno}</p>
                    <p><strong>DNI:</strong> ${postulanteData.numero_documento}</p>
                    <p><strong>Email:</strong> ${postulanteData.email}</p>
                </div>
            </div>
        `;
        
        // Datos académicos
        const carreraSelect = document.getElementById('carrera_id');
        const turnoSelect = document.getElementById('turno_id');
        const tipoSelect = document.getElementById('tipo_inscripcion');
        
        const carreraNombre = carreraSelect.options[carreraSelect.selectedIndex].text;
        const turnoNombre = turnoSelect.options[turnoSelect.selectedIndex].text;
        const tipoNombre = tipoSelect.options[tipoSelect.selectedIndex].text;
        
        resumenHTML += `
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Datos Académicos</h6>
                    <p><strong>Carrera:</strong> ${carreraNombre}</p>
                    <p><strong>Turno:</strong> ${turnoNombre}</p>
                    <p><strong>Tipo de Inscripción:</strong> ${tipoNombre}</p>
                    <p><strong>Colegio de Procedencia:</strong> ${formData.get('colegio_procedencia')}</p>
                    <p><strong>Año de Egreso:</strong> ${formData.get('año_egreso')}</p>
                </div>
            </div>
        `;
        
        // Documentos
        const documentos = [];
        if (document.getElementById('foto').files.length) {
            documentos.push('Foto actualizada');
        }
        if (document.getElementById('certificado_estudios').files.length) {
            documentos.push('Certificado de estudios');
        }
        if (document.getElementById('voucher_pago').files.length) {
            documentos.push('Voucher de pago');
        }
        if (document.getElementById('dni_pdf').files.length) {
            documentos.push('DNI actualizado');
        }
        
        resumenHTML += `
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Documentos a Cargar</h6>
                    <ul class="list-unstyled">
        `;
        
        documentos.forEach(doc => {
            resumenHTML += `<li><i class="mdi mdi-check-circle text-success"></i> ${doc}</li>`;
        });
        
        resumenHTML += `
                    </ul>
                </div>
            </div>
        `;
        
        resumenDiv.innerHTML = resumenHTML;
    }
    
    function handleSubmit(e) {
        e.preventDefault();
        
        // Validar que el checkbox esté marcado
        const confirmarCheckbox = document.getElementById('confirmarPostulacion');
        if (!confirmarCheckbox.checked) {
            toastr.error('Debe confirmar los términos y condiciones', 'Error');
            return;
        }
        
        // Deshabilitar botón de envío
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
        
        // Crear FormData con todos los datos
        const formData = new FormData(form);
        
        // Enviar formulario (usar el endpoint existente para postulaciones de usuarios existentes)
        fetch('/postulacion-unificada', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message || 'Postulación enviada exitosamente', 'Éxito');
                
                // Notificar al padre que se completó la postulación
                window.parent.postMessage({
                    type: 'postulacion-completada',
                    codigo_postulante: data.codigo_postulante
                }, '*');
                
                // Cerrar modal después de 2 segundos
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('nuevaPostulacionModal'));
                    if (modal) modal.hide();
                }, 2000);
            } else {
                // Mostrar errores
                if (data.errors) {
                    let errorMessages = '';
                    for (let field in data.errors) {
                        errorMessages += data.errors[field].join('<br>') + '<br>';
                    }
                    toastr.error(errorMessages, 'Errores de validación');
                } else {
                    toastr.error(data.message || 'Error al procesar la postulación', 'Error');
                }
                
                // Rehabilitar botón
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = '<i class="mdi mdi-send me-1"></i> Enviar Postulación';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error al enviar la postulación', 'Error');
            
            // Rehabilitar botón
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = '<i class="mdi mdi-send me-1"></i> Enviar Postulación';
        });
    }
})();
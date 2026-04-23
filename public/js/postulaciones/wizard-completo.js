/**
 * Wizard Completo para Registro + Postulación
 */
(function() {
    let currentStep = 1;
    const totalSteps = 6;
    
    // Elementos del DOM
    const form = document.getElementById('formRegistroCompleto');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnSiguiente = document.getElementById('btnSiguiente');
    const btnEnviar = document.getElementById('btnEnviar');
    const progressBar = document.getElementById('wizardProgress');
    
    // Inicializar wizard
    document.addEventListener('DOMContentLoaded', function() {
        initializeWizard();
    });
    
    function initializeWizard() {
        // Eventos de navegación
        btnSiguiente.addEventListener('click', nextStep);
        btnAnterior.addEventListener('click', previousStep);
        
        // Evento de envío del formulario
        form.addEventListener('submit', handleSubmit);
        
        // Validación de contraseñas
        document.getElementById('estudiante_password_confirmation').addEventListener('input', validatePasswords);
        
        // Actualizar estado inicial
        updateWizardState();
    }
    
    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                // Si estamos en el paso 5, generar resumen
                if (currentStep === 5) {
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
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
            
            // Validaciones específicas
            if (field.type === 'email' && !validateEmail(field.value)) {
                field.classList.add('is-invalid');
                isValid = false;
            }
            
            if (field.id === 'estudiante_dni' && field.value.length !== 8) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });
        
        // Validar contraseñas en paso 1
        if (currentStep === 1) {
            const password = document.getElementById('estudiante_password').value;
            const confirmPassword = document.getElementById('estudiante_password_confirmation').value;
            
            if (password !== confirmPassword) {
                document.getElementById('estudiante_password_confirmation').classList.add('is-invalid');
                toastr.error('Las contraseñas no coinciden', 'Error');
                isValid = false;
            }
        }
        
        if (!isValid) {
            toastr.warning('Por favor, complete todos los campos obligatorios', 'Campos requeridos');
        }
        
        return isValid;
    }
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function validatePasswords() {
        const password = document.getElementById('estudiante_password').value;
        const confirmPassword = document.getElementById('estudiante_password_confirmation').value;
        const confirmField = document.getElementById('estudiante_password_confirmation');
        
        if (confirmPassword && password !== confirmPassword) {
            confirmField.classList.add('is-invalid');
        } else {
            confirmField.classList.remove('is-invalid');
        }
    }
    
    function generateSummary() {
        const resumenDiv = document.getElementById('resumenDatos');
        
        // Recopilar datos del formulario
        const formData = new FormData(form);
        
        let resumenHTML = `
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Datos del Estudiante</h6>
                    <p><strong>Nombre:</strong> ${formData.get('estudiante_nombre')} ${formData.get('estudiante_apellido_paterno')} ${formData.get('estudiante_apellido_materno')}</p>
                    <p><strong>DNI:</strong> ${formData.get('estudiante_dni')}</p>
                    <p><strong>Email:</strong> ${formData.get('estudiante_email')}</p>
                    <p><strong>Teléfono:</strong> ${formData.get('estudiante_telefono')}</p>
                    <p><strong>Dirección:</strong> ${formData.get('estudiante_direccion')}</p>
                </div>
            </div>
        `;
        
        // Datos del padre si existen
        if (formData.get('padre_dni')) {
            resumenHTML += `
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Datos del Padre</h6>
                        <p><strong>Nombre:</strong> ${formData.get('padre_nombre')} ${formData.get('padre_apellido_paterno')} ${formData.get('padre_apellido_materno')}</p>
                        <p><strong>DNI:</strong> ${formData.get('padre_dni')}</p>
                        <p><strong>Teléfono:</strong> ${formData.get('padre_telefono')}</p>
                    </div>
                </div>
            `;
        }
        
        // Datos de la madre si existen
        if (formData.get('madre_dni')) {
            resumenHTML += `
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Datos de la Madre</h6>
                        <p><strong>Nombre:</strong> ${formData.get('madre_nombre')} ${formData.get('madre_apellido_paterno')} ${formData.get('madre_apellido_materno')}</p>
                        <p><strong>DNI:</strong> ${formData.get('madre_dni')}</p>
                        <p><strong>Teléfono:</strong> ${formData.get('madre_telefono')}</p>
                    </div>
                </div>
            `;
        }
        
        // Datos académicos
        const carreraSelect = document.getElementById('carrera_id');
        const turnoSelect = document.getElementById('turno_id');
        const carreraNombre = carreraSelect.options[carreraSelect.selectedIndex].text;
        const turnoNombre = turnoSelect.options[turnoSelect.selectedIndex].text;
        
        resumenHTML += `
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Datos Académicos</h6>
                    <p><strong>Carrera:</strong> ${carreraNombre}</p>
                    <p><strong>Turno:</strong> ${turnoNombre}</p>
                    <p><strong>Colegio de Procedencia:</strong> ${formData.get('colegio_procedencia')}</p>
                    <p><strong>Año de Egreso:</strong> ${formData.get('año_egreso')}</p>
                </div>
            </div>
        `;
        
        // Documentos
        resumenHTML += `
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Documentos Cargados</h6>
                    <ul class="list-unstyled">
                        <li><i class="mdi mdi-check-circle text-success"></i> Foto del estudiante</li>
                        <li><i class="mdi mdi-check-circle text-success"></i> DNI escaneado</li>
                        <li><i class="mdi mdi-check-circle text-success"></i> Certificado de estudios</li>
                        <li><i class="mdi mdi-check-circle text-success"></i> Voucher de pago</li>
                    </ul>
                </div>
            </div>
        `;
        
        resumenDiv.innerHTML = resumenHTML;
    }
    
    function handleSubmit(e) {
        e.preventDefault();
        
        // Validar que el checkbox esté marcado
        const confirmarCheckbox = document.getElementById('confirmarDatos');
        if (!confirmarCheckbox.checked) {
            toastr.error('Debe aceptar los términos y condiciones', 'Error');
            return;
        }
        
        // Deshabilitar botón de envío
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
        
        // Crear FormData con todos los datos
        const formData = new FormData(form);
        
        // Enviar formulario
        fetch('/postulacion-unificada/registro-completo', {
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
                toastr.success(data.message, 'Éxito');
                
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
    
    // Función para consultar DNI (RENIEC)
    window.consultarDNI = function(tipo) {
        const dniField = document.getElementById(`${tipo}_dni`);
        const dni = dniField.value;
        
        if (dni.length !== 8) {
            toastr.warning('El DNI debe tener 8 dígitos', 'Advertencia');
            return;
        }
        
        // Mostrar loading
        const btn = dniField.nextElementSibling.querySelector('button');
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        // Simular consulta RENIEC (aquí iría la llamada real al API)
        fetch(`/api/reniec/consultar/${dni}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Llenar campos con datos obtenidos
                    document.getElementById(`${tipo}_nombre`).value = data.nombres || '';
                    document.getElementById(`${tipo}_apellido_paterno`).value = data.apellidoPaterno || '';
                    document.getElementById(`${tipo}_apellido_materno`).value = data.apellidoMaterno || '';
                    
                    toastr.success('Datos obtenidos de RENIEC', 'Éxito');
                } else {
                    toastr.info('No se encontraron datos en RENIEC', 'Información');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.info('Servicio RENIEC no disponible', 'Información');
            })
            .finally(() => {
                // Restaurar botón
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            });
    };
})();
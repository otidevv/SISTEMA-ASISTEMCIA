/**
 * Editor Visual de Plantillas de Carnets - MEJORADO
 * Soporta: drag & drop, resize, fuentes, fondos de color
 */

class CarnetTemplateEditor {
    constructor() {
        this.canvas = document.getElementById('carnetCanvas');
        this.fields = {};
        this.selectedField = null;
        this.zoom = 1;
        this.isDragging = false;
        this.isResizing = false;
        this.dragOffset = { x: 0, y: 0 };
        this.resizeHandle = null;
        this.fondoPath = null;

        // Constantes de conversión
        this.MM_TO_PX_RATIO = 3.7795275591; // 1mm = 3.78px aproximadamente

        this.init();
    }

    init() {
        this.setupCanvas();
        this.setupEventListeners();
        this.loadExistingTemplate();
    }

    setupCanvas() {
        const anchoMm = parseFloat(document.getElementById('ancho_mm').value) || 53.98;
        const altoMm = parseFloat(document.getElementById('alto_mm').value) || 85.6;

        const anchoPx = anchoMm * this.MM_TO_PX_RATIO;
        const altoPx = altoMm * this.MM_TO_PX_RATIO;

        this.canvas.style.width = anchoPx + 'px';
        this.canvas.style.height = altoPx + 'px';
    }

    setupEventListeners() {
        // Cambios en dimensiones
        document.getElementById('ancho_mm').addEventListener('change', () => this.setupCanvas());
        document.getElementById('alto_mm').addEventListener('change', () => this.setupCanvas());

        // Agregar campos
        document.querySelectorAll('#fieldList li').forEach(item => {
            item.addEventListener('click', (e) => this.addField(e.currentTarget.dataset.field));
        });

        // Upload de imagen
        const uploadZone = document.getElementById('uploadZone');
        const fondoInput = document.getElementById('fondoInput');

        uploadZone.addEventListener('click', () => fondoInput.click());
        fondoInput.addEventListener('change', (e) => this.handleImageUpload(e));

        // Drag & drop de imagen
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                this.handleImageFile(e.dataTransfer.files[0]);
            }
        });

        // Remover imagen
        document.getElementById('removeImage').addEventListener('click', () => this.removeBackground());

        // Zoom controls
        document.getElementById('zoomIn').addEventListener('click', () => this.adjustZoom(0.1));
        document.getElementById('zoomOut').addEventListener('click', () => this.adjustZoom(-0.1));
        document.getElementById('zoomReset').addEventListener('click', () => this.setZoom(1));

        // Propiedades de campo
        document.getElementById('fieldWidth')?.addEventListener('change', (e) => this.updateFieldSize('width', e.target.value));
        document.getElementById('fieldHeight')?.addEventListener('change', (e) => this.updateFieldSize('height', e.target.value));
        document.getElementById('fontFamily')?.addEventListener('change', (e) => this.updateFieldProperty('fontFamily', e.target.value));
        document.getElementById('fontSize')?.addEventListener('change', (e) => this.updateFieldProperty('fontSize', e.target.value + 'pt'));
        document.getElementById('fontColor')?.addEventListener('change', (e) => this.updateFieldProperty('color', e.target.value));
        document.getElementById('fontWeight')?.addEventListener('change', (e) => this.updateFieldProperty('fontWeight', e.target.value));
        document.getElementById('backgroundColor')?.addEventListener('change', (e) => this.updateFieldBackground(e.target.value));
        document.getElementById('backgroundOpacity')?.addEventListener('input', (e) => {
            document.getElementById('opacityValue').textContent = e.target.value + '%';
            this.updateFieldBackgroundOpacity(e.target.value);
        });
        document.getElementById('clearBackground')?.addEventListener('click', () => this.clearFieldBackground());
        document.getElementById('removeField')?.addEventListener('click', () => this.removeSelectedField());

        // Submit form
        document.getElementById('templateForm').addEventListener('submit', (e) => this.handleSubmit(e));

        // Mouse move para mostrar coordenadas
        this.canvas.addEventListener('mousemove', (e) => this.showCoordinates(e));

        // Click fuera para deseleccionar
        this.canvas.addEventListener('click', (e) => {
            if (e.target === this.canvas) {
                this.deselectAll();
            }
        });
    }

    addField(fieldName) {
        if (this.fields[fieldName]) {
            toastr.warning('Este campo ya está agregado');
            return;
        }

        const fieldElement = document.createElement('div');
        fieldElement.className = 'field-element';
        fieldElement.dataset.field = fieldName;

        // Tamaño inicial
        const defaultConfig = this.getDefaultConfig(fieldName);
        let fieldWidth = 100; // ancho por defecto en px
        let fieldHeight = 40; // alto por defecto en px

        if (defaultConfig.width) {
            const widthMm = parseFloat(defaultConfig.width);
            fieldWidth = widthMm * this.MM_TO_PX_RATIO;
            fieldElement.style.width = defaultConfig.width;
        }
        if (defaultConfig.height) {
            const heightMm = parseFloat(defaultConfig.height);
            fieldHeight = heightMm * this.MM_TO_PX_RATIO;
            fieldElement.style.height = defaultConfig.height;
        }

        // Posición inicial - asegurar que esté visible
        const canvasRect = this.canvas.getBoundingClientRect();
        const canvasWidth = this.canvas.offsetWidth;
        const canvasHeight = this.canvas.offsetHeight;

        // Calcular posición centrada pero asegurando que esté dentro del canvas
        let leftPos = Math.max(10, Math.min((canvasWidth / 2) - (fieldWidth / 2), canvasWidth - fieldWidth - 10));
        let topPos = Math.max(10, Math.min((canvasHeight / 2) - (fieldHeight / 2), canvasHeight - fieldHeight - 10));

        // Si hay muchos campos, escalonar la posición para evitar superposición
        const fieldCount = Object.keys(this.fields).length;
        if (fieldCount > 0) {
            const offset = (fieldCount * 15) % 50; // Desplazamiento escalonado
            leftPos = Math.min(leftPos + offset, canvasWidth - fieldWidth - 10);
            topPos = Math.min(topPos + offset, canvasHeight - fieldHeight - 10);
        }

        fieldElement.style.left = leftPos + 'px';
        fieldElement.style.top = topPos + 'px';

        // Contenido del campo
        const label = document.createElement('div');
        label.className = 'field-label';
        label.textContent = fieldName.replace(/_/g, ' ');

        const value = document.createElement('div');
        value.className = 'field-value';
        value.textContent = this.getFieldSampleValue(fieldName);

        fieldElement.appendChild(label);
        fieldElement.appendChild(value);

        // Agregar handles de resize
        this.addResizeHandles(fieldElement);

        // Event listeners
        fieldElement.addEventListener('mousedown', (e) => {
            if (e.target.classList.contains('resize-handle')) {
                this.startResize(e, fieldElement, e.target);
            } else {
                this.startDrag(e, fieldElement);
            }
        });

        fieldElement.addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectField(fieldElement);
        });

        this.canvas.appendChild(fieldElement);
        this.fields[fieldName] = {
            element: fieldElement,
            config: defaultConfig
        };

        // Marcar como agregado en la lista
        document.querySelector(`#fieldList li[data-field="${fieldName}"]`).classList.add('added');

        // Seleccionar automáticamente el campo recién agregado
        this.selectField(fieldElement);

        toastr.success(`Campo "${fieldName}" agregado`);
    }

    addResizeHandles(element) {
        const handles = ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w'];
        handles.forEach(position => {
            const handle = document.createElement('div');
            handle.className = `resize-handle ${position}`;
            element.appendChild(handle);
        });
    }

    getFieldSampleValue(fieldName) {
        const samples = {
            foto: '[FOTO]',
            qr_code: '[QR]',
            codigo_postulante: '2000157',
            nombre_completo: 'APELLIDOS, NOMBRES',
            dni: '12345678',
            grupo: 'A-1',
            modalidad: 'PRESENCIAL',
            carrera: 'INGENIERÍA'
        };
        return samples[fieldName] || fieldName.toUpperCase();
    }

    getDefaultConfig(fieldName) {
        const defaults = {
            foto: { width: '24mm', height: '26mm', visible: true },
            qr_code: { width: '10mm', height: '10mm', visible: true },
            codigo_postulante: { fontSize: '11pt', fontWeight: 'bold', fontFamily: 'Arial', color: 'white', backgroundColor: 'transparent', visible: true },
            nombre_completo: { fontSize: '7pt', fontWeight: '100', fontFamily: 'Arial', color: 'white', backgroundColor: 'transparent', visible: true },
            dni: { fontSize: '8pt', fontFamily: 'Arial', color: '#003d7a', backgroundColor: 'rgba(255,255,255,0.9)', visible: true },
            grupo: { fontSize: '8pt', fontFamily: 'Arial', color: '#003d7a', backgroundColor: 'rgba(255,255,255,0.9)', visible: true },
            modalidad: { fontSize: '7pt', fontFamily: 'Arial', color: '#003d7a', backgroundColor: 'rgba(255,255,255,0.9)', visible: true },
            carrera: { fontSize: '7pt', fontWeight: 'bold', fontFamily: 'Arial', color: '#003d7a', backgroundColor: 'rgba(255,255,255,0.9)', visible: true }
        };
        return defaults[fieldName] || { fontSize: '8pt', fontFamily: 'Arial', color: '#003d7a', backgroundColor: 'rgba(255,255,255,0.9)', visible: true };
    }

    startDrag(e, element) {
        if (e.target.classList.contains('resize-handle')) return;

        e.preventDefault();
        this.isDragging = true;
        this.selectedField = element;

        const rect = element.getBoundingClientRect();
        const canvasRect = this.canvas.getBoundingClientRect();

        this.dragOffset = {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };

        element.classList.add('dragging');

        const onMouseMove = (e) => {
            if (!this.isDragging) return;

            const canvasRect = this.canvas.getBoundingClientRect();
            let newX = e.clientX - canvasRect.left - this.dragOffset.x;
            let newY = e.clientY - canvasRect.top - this.dragOffset.y;

            // Obtener dimensiones reales del elemento
            const elementWidth = element.offsetWidth;
            const elementHeight = element.offsetHeight;
            const canvasWidth = this.canvas.offsetWidth;
            const canvasHeight = this.canvas.offsetHeight;

            // Limitar al canvas con un margen mínimo visible
            const minVisible = 20; // píxeles mínimos que deben quedar visibles
            newX = Math.max(-elementWidth + minVisible, Math.min(newX, canvasWidth - minVisible));
            newY = Math.max(-elementHeight + minVisible, Math.min(newY, canvasHeight - minVisible));

            element.style.left = newX + 'px';
            element.style.top = newY + 'px';
        };

        const onMouseUp = () => {
            this.isDragging = false;
            element.classList.remove('dragging');
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    startResize(e, element, handle) {
        e.preventDefault();
        e.stopPropagation();

        this.isResizing = true;
        this.resizeHandle = handle.classList[1]; // nw, ne, sw, se, etc.

        const startX = e.clientX;
        const startY = e.clientY;
        const startWidth = element.offsetWidth;
        const startHeight = element.offsetHeight;
        const startLeft = element.offsetLeft;
        const startTop = element.offsetTop;

        // Guardar tamaño de fuente inicial
        const valueElement = element.querySelector('.field-value');
        const currentFontSize = parseInt(window.getComputedStyle(valueElement).fontSize) || 12;
        const initialFontSize = currentFontSize;

        const onMouseMove = (e) => {
            if (!this.isResizing) return;

            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;

            let newWidth = startWidth;
            let newHeight = startHeight;
            let newLeft = startLeft;
            let newTop = startTop;

            // Aplicar cambios según el handle
            if (this.resizeHandle.includes('e')) {
                newWidth = Math.max(20, startWidth + deltaX);
            }
            if (this.resizeHandle.includes('w')) {
                newWidth = Math.max(20, startWidth - deltaX);
                newLeft = startLeft + deltaX;
            }
            if (this.resizeHandle.includes('s')) {
                newHeight = Math.max(20, startHeight + deltaY);
            }
            if (this.resizeHandle.includes('n')) {
                newHeight = Math.max(20, startHeight - deltaY);
                newTop = startTop + deltaY;
            }

            element.style.width = newWidth + 'px';
            element.style.height = newHeight + 'px';
            element.style.left = newLeft + 'px';
            element.style.top = newTop + 'px';

            // Calcular nuevo tamaño de fuente proporcional
            const widthRatio = newWidth / startWidth;
            const heightRatio = newHeight / startHeight;
            const scaleRatio = Math.min(widthRatio, heightRatio); // Usar el menor para mantener proporción

            let newFontSize = Math.round(initialFontSize * scaleRatio);
            newFontSize = Math.max(6, Math.min(24, newFontSize)); // Limitar entre 6 y 24pt

            // Aplicar nuevo tamaño de fuente
            valueElement.style.fontSize = newFontSize + 'px';

            // Actualizar inputs
            document.getElementById('fieldWidth').value = this.pxToMmNumber(newWidth).toFixed(1);
            document.getElementById('fieldHeight').value = this.pxToMmNumber(newHeight).toFixed(1);
            document.getElementById('fontSize').value = newFontSize;
        };

        const onMouseUp = () => {
            this.isResizing = false;
            this.resizeHandle = null;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);

            // Guardar tamaño en config
            const fieldName = element.dataset.field;
            const finalFontSize = parseInt(window.getComputedStyle(valueElement).fontSize);

            this.fields[fieldName].config.width = this.pxToMm(element.offsetWidth);
            this.fields[fieldName].config.height = this.pxToMm(element.offsetHeight);
            this.fields[fieldName].config.fontSize = finalFontSize + 'pt';

            toastr.info(`Tamaño ajustado: ${finalFontSize}pt`, '', { timeOut: 1000 });
        };

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    selectField(element) {
        this.deselectAll();

        element.classList.add('selected');
        this.selectedField = element;

        const fieldName = element.dataset.field;
        const config = this.fields[fieldName].config;

        // Mostrar propiedades
        document.getElementById('fieldProperties').style.display = 'block';

        // Cargar valores
        document.getElementById('fieldWidth').value = this.pxToMmNumber(element.offsetWidth).toFixed(1);
        document.getElementById('fieldHeight').value = this.pxToMmNumber(element.offsetHeight).toFixed(1);
        document.getElementById('fontFamily').value = config.fontFamily || 'Arial';
        document.getElementById('fontSize').value = parseInt(config.fontSize) || 8;
        document.getElementById('fontColor').value = config.color || '#003d7a';
        document.getElementById('fontWeight').value = config.fontWeight || 'normal';

        // Color de fondo
        if (config.backgroundColor && config.backgroundColor !== 'transparent') {
            const rgb = config.backgroundColor.match(/\d+/g);
            if (rgb) {
                const hex = '#' + rgb.slice(0, 3).map(x => parseInt(x).toString(16).padStart(2, '0')).join('');
                document.getElementById('backgroundColor').value = hex;
                document.getElementById('backgroundOpacity').value = rgb[3] ? Math.round(rgb[3] * 100) : 90;
                document.getElementById('opacityValue').textContent = (rgb[3] ? Math.round(rgb[3] * 100) : 90) + '%';
            }
        }
    }

    deselectAll() {
        document.querySelectorAll('.field-element').forEach(el => el.classList.remove('selected'));
        this.selectedField = null;
        document.getElementById('fieldProperties').style.display = 'none';
    }

    updateFieldSize(dimension, value) {
        if (!this.selectedField) return;

        const valuePx = parseFloat(value) * this.MM_TO_PX_RATIO;
        const fieldName = this.selectedField.dataset.field;

        if (dimension === 'width') {
            this.selectedField.style.width = valuePx + 'px';
            this.fields[fieldName].config.width = value + 'mm';
        } else {
            this.selectedField.style.height = valuePx + 'px';
            this.fields[fieldName].config.height = value + 'mm';
        }
    }

    updateFieldProperty(property, value) {
        if (!this.selectedField) return;

        const fieldName = this.selectedField.dataset.field;
        this.fields[fieldName].config[property] = value;

        const valueElement = this.selectedField.querySelector('.field-value');

        // Aplicar cambio visual
        if (property === 'fontFamily') {
            valueElement.style.fontFamily = value;
        } else if (property === 'fontSize') {
            valueElement.style.fontSize = value;
        } else if (property === 'color') {
            valueElement.style.color = value;
        } else if (property === 'fontWeight') {
            valueElement.style.fontWeight = value;
        }
    }

    updateFieldBackground(color) {
        if (!this.selectedField) return;

        const opacity = document.getElementById('backgroundOpacity').value / 100;
        const rgb = this.hexToRgb(color);
        const rgba = `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${opacity})`;

        this.selectedField.style.background = rgba;

        const fieldName = this.selectedField.dataset.field;
        this.fields[fieldName].config.backgroundColor = rgba;
    }

    updateFieldBackgroundOpacity(opacity) {
        if (!this.selectedField) return;

        const color = document.getElementById('backgroundColor').value;
        const rgb = this.hexToRgb(color);
        const rgba = `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${opacity / 100})`;

        this.selectedField.style.background = rgba;

        const fieldName = this.selectedField.dataset.field;
        this.fields[fieldName].config.backgroundColor = rgba;
    }

    clearFieldBackground() {
        if (!this.selectedField) return;

        this.selectedField.style.background = 'transparent';

        const fieldName = this.selectedField.dataset.field;
        this.fields[fieldName].config.backgroundColor = 'transparent';
    }

    hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : { r: 0, g: 0, b: 0 };
    }

    removeSelectedField() {
        if (!this.selectedField) return;

        const fieldName = this.selectedField.dataset.field;
        this.selectedField.remove();
        delete this.fields[fieldName];

        document.querySelector(`#fieldList li[data-field="${fieldName}"]`).classList.remove('added');
        document.getElementById('fieldProperties').style.display = 'none';
        this.selectedField = null;

        toastr.info('Campo eliminado');
    }

    handleImageUpload(e) {
        const file = e.target.files[0];
        if (file) {
            this.handleImageFile(file);
        }
    }

    handleImageFile(file) {
        if (!file.type.startsWith('image/')) {
            toastr.error('Por favor selecciona una imagen válida');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            toastr.error('La imagen no debe superar 5MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('backgroundImage').src = e.target.result;
            document.getElementById('backgroundImage').style.display = 'block';
            document.getElementById('uploadZone').style.display = 'none';
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('previewImg').src = e.target.result;
        };
        reader.readAsDataURL(file);

        this.uploadBackground(file);
    }

    uploadBackground(file) {
        const formData = new FormData();
        formData.append('fondo', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch('/carnets/plantillas/upload-fondo', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.fondoPath = data.path;
                    toastr.success('Imagen subida correctamente');
                } else {
                    toastr.error(data.message || 'Error al subir imagen');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al subir imagen');
            });
    }

    removeBackground() {
        document.getElementById('backgroundImage').src = '';
        document.getElementById('backgroundImage').style.display = 'none';
        document.getElementById('uploadZone').style.display = 'block';
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('fondoInput').value = '';
        this.fondoPath = null;
    }

    adjustZoom(delta) {
        this.setZoom(this.zoom + delta);
    }

    setZoom(newZoom) {
        this.zoom = Math.max(0.5, Math.min(2, newZoom));
        document.getElementById('canvasWrapper').style.transform = `scale(${this.zoom})`;
        document.getElementById('zoomReset').textContent = Math.round(this.zoom * 100) + '%';
    }

    showCoordinates(e) {
        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const xMm = (x / this.MM_TO_PX_RATIO).toFixed(2);
        const yMm = (y / this.MM_TO_PX_RATIO).toFixed(2);

        document.getElementById('coordsDisplay').textContent = `X: ${xMm}mm, Y: ${yMm}mm`;
    }

    pxToMm(px) {
        return (px / this.MM_TO_PX_RATIO).toFixed(2) + 'mm';
    }

    pxToMmNumber(px) {
        return px / this.MM_TO_PX_RATIO;
    }

    getFieldsConfig() {
        const config = {};

        Object.keys(this.fields).forEach(fieldName => {
            const element = this.fields[fieldName].element;

            const left = element.style.left;
            const top = element.style.top;

            config[fieldName] = {
                ...this.fields[fieldName].config,
                left: this.pxToMm(parseFloat(left)),
                top: this.pxToMm(parseFloat(top)),
                width: this.pxToMm(element.offsetWidth),
                height: this.pxToMm(element.offsetHeight)
            };
        });

        return config;
    }

    handleSubmit(e) {
        e.preventDefault();

        const formData = {
            nombre: document.getElementById('nombre').value,
            tipo: document.getElementById('tipo').value,
            ancho_mm: document.getElementById('ancho_mm').value,
            alto_mm: document.getElementById('alto_mm').value,
            descripcion: document.getElementById('descripcion').value,
            fondo_path: this.fondoPath,
            campos_config: JSON.stringify(this.getFieldsConfig()),
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        const url = window.location.pathname.includes('/editar')
            ? window.location.pathname.replace('/editar', '')
            : '/carnets/plantillas';

        const method = window.location.pathname.includes('/editar') ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': formData._token
            },
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => {
                        window.location.href = '/carnets/plantillas';
                    }, 1500);
                } else {
                    toastr.error(data.message || 'Error al guardar plantilla');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al guardar plantilla');
            });
    }

    loadExistingTemplate() {
        // Implementar carga de plantilla existente si es necesario
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new CarnetTemplateEditor();
});

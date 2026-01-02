@extends('layouts.app')

@section('title', isset($template) ? 'Editar Plantilla' : 'Nueva Plantilla')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <style>
        .editor-container {
            display: flex;
            gap: 20px;
            min-height: 600px;
        }
        
        .canvas-panel {
            flex: 2;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            position: relative;
        }
        
        .controls-panel {
            flex: 1;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-height: 800px;
            overflow-y: auto;
        }
        
        .carnet-canvas {
            position: relative;
            background: white;
            margin: 0 auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .carnet-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            pointer-events: none;
        }
        
        .field-element {
            position: absolute;
            cursor: move;
            padding: 4px 8px;
            background: rgba(255, 255, 255, 0.9);
            border: 2px dashed #007bff;
            border-radius: 4px;
            user-select: none;
            min-width: 50px;
            min-height: 20px;
        }
        
        .field-element:hover {
            border-color: #0056b3;
            background: rgba(255, 255, 255, 1);
        }
        
        .field-element.selected {
            border-color: #28a745;
            border-style: solid;
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
        }
        
        .field-element.dragging {
            opacity: 0.7;
            z-index: 1000;
        }
        
        /* Resize handles */
        .resize-handle {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #28a745;
            border: 2px solid white;
            border-radius: 50%;
            display: none;
            z-index: 10;
        }
        
        .field-element.selected .resize-handle {
            display: block;
        }
        
        .resize-handle.nw { top: -5px; left: -5px; cursor: nw-resize; }
        .resize-handle.ne { top: -5px; right: -5px; cursor: ne-resize; }
        .resize-handle.sw { bottom: -5px; left: -5px; cursor: sw-resize; }
        .resize-handle.se { bottom: -5px; right: -5px; cursor: se-resize; }
        .resize-handle.n { top: -5px; left: 50%; transform: translateX(-50%); cursor: n-resize; }
        .resize-handle.s { bottom: -5px; left: 50%; transform: translateX(-50%); cursor: s-resize; }
        .resize-handle.e { top: 50%; right: -5px; transform: translateY(-50%); cursor: e-resize; }
        .resize-handle.w { top: 50%; left: -5px; transform: translateY(-50%); cursor: w-resize; }
        
        .field-label {
            font-size: 10px;
            color: #666;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        
        .field-value {
            color: #333;
        }
        
        .field-list {
            list-style: none;
            padding: 0;
        }
        
        .field-list li {
            padding: 10px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .field-list li:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .field-list li.added {
            background: #d4edda;
            border-left: 3px solid #28a745;
        }
        
        .zoom-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 5px;
            z-index: 100;
        }
        
        .coordinates-display {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-family: monospace;
        }
        
        .upload-zone {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-zone:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }
        
        .upload-zone.dragover {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
@endpush

@section('content')
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ isset($template) ? 'Editar Plantilla' : 'Nueva Plantilla' }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('carnets.templates.index') }}">Plantillas</a></li>
                        <li class="breadcrumb-item active">{{ isset($template) ? 'Editar' : 'Crear' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form id="templateForm">
        @csrf
        @if(isset($template))
            @method('PUT')
        @endif

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Nombre de la Plantilla <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="{{ $template->nombre ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="postulante" {{ (isset($template) && $template->tipo == 'postulante') ? 'selected' : '' }}>Postulante</option>
                                        <option value="estudiante" {{ (isset($template) && $template->tipo == 'estudiante') ? 'selected' : '' }}>Estudiante</option>
                                        <option value="docente" {{ (isset($template) && $template->tipo == 'docente') ? 'selected' : '' }}>Docente</option>
                                        <option value="administrativo" {{ (isset($template) && $template->tipo == 'administrativo') ? 'selected' : '' }}>Administrativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Ancho (mm)</label>
                                    <input type="number" class="form-control" id="ancho_mm" name="ancho_mm" 
                                           value="{{ $template->ancho_mm ?? 53.98 }}" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Alto (mm)</label>
                                    <input type="number" class="form-control" id="alto_mm" name="alto_mm" 
                                           value="{{ $template->alto_mm ?? 85.6 }}" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2">{{ $template->descripcion ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="editor-container">
                    <!-- Canvas Panel -->
                    <div class="canvas-panel">
                        <div class="zoom-controls">
                            <button type="button" class="btn btn-sm btn-light" id="zoomOut">
                                <i class="uil uil-minus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light" id="zoomReset">100%</button>
                            <button type="button" class="btn btn-sm btn-light" id="zoomIn">
                                <i class="uil uil-plus"></i>
                            </button>
                        </div>

                        <div id="canvasWrapper" style="transform-origin: top center;">
                            <div class="carnet-canvas" id="carnetCanvas">
                                <img id="backgroundImage" class="carnet-background" 
                                     src="{{ isset($template) && $template->fondo_path ? asset('storage/' . $template->fondo_path) : '' }}" 
                                     style="display: {{ isset($template) && $template->fondo_path ? 'block' : 'none' }}">
                                
                                <!-- Los campos se agregarán dinámicamente aquí -->
                            </div>
                        </div>

                        <div class="coordinates-display" id="coordsDisplay">
                            X: 0mm, Y: 0mm
                        </div>
                    </div>

                    <!-- Controls Panel -->
                    <div class="controls-panel">
                        <h5 class="mb-3">Configuración</h5>

                        <!-- Upload Background -->
                        <div class="mb-4">
                            <label class="form-label">Imagen de Fondo</label>
                            <div class="upload-zone" id="uploadZone">
                                <i class="uil uil-cloud-upload" style="font-size: 48px; color: #ccc;"></i>
                                <p class="mb-0">Arrastra una imagen o haz clic para seleccionar</p>
                                <small class="text-muted">JPG, PNG (Max 5MB)</small>
                                <input type="file" id="fondoInput" accept="image/*" style="display: none;">
                            </div>
                            <div id="imagePreview" style="display: none;">
                                <img id="previewImg" class="preview-image">
                                <button type="button" class="btn btn-sm btn-danger mt-2" id="removeImage">
                                    <i class="uil uil-trash"></i> Quitar imagen
                                </button>
                            </div>
                        </div>

                        <!-- Available Fields -->
                        <div class="mb-4">
                            <label class="form-label">Campos Disponibles</label>
                            <p class="text-muted small">Haz clic para agregar al carnet</p>
                            <ul class="field-list" id="fieldList">
                                <li data-field="foto">
                                    <i class="uil uil-image me-2"></i> Foto
                                </li>
                                <li data-field="qr_code">
                                    <i class="uil uil-qrcode-scan me-2"></i> Código QR
                                </li>
                                <li data-field="codigo_postulante">
                                    <i class="uil uil-hashtag me-2"></i> Código Postulante
                                </li>
                                <li data-field="nombre_completo">
                                    <i class="uil uil-user me-2"></i> Nombre Completo
                                </li>
                                <li data-field="dni">
                                    <i class="uil uil-id-card me-2"></i> DNI
                                </li>
                                <li data-field="grupo">
                                    <i class="uil uil-users-alt me-2"></i> Grupo
                                </li>
                                <li data-field="modalidad">
                                    <i class="uil uil-book-alt me-2"></i> Modalidad
                                </li>
                                <li data-field="carrera">
                                    <i class="uil uil-graduation-cap me-2"></i> Carrera
                                </li>
                            </ul>
                        </div>

                        <!-- Field Properties (shown when field is selected) -->
                        <div id="fieldProperties" style="display: none;">
                            <hr>
                            <h6 class="mb-3">Propiedades del Campo</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Ancho (mm)</label>
                                <input type="number" class="form-control form-control-sm" id="fieldWidth" min="5" max="100" step="0.1" value="30">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Alto (mm)</label>
                                <input type="number" class="form-control form-control-sm" id="fieldHeight" min="5" max="100" step="0.1" value="10">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tipo de Fuente</label>
                                <select class="form-select form-select-sm" id="fontFamily">
                                    <option value="Arial">Arial</option>
                                    <option value="Helvetica">Helvetica</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                    <option value="Courier New">Courier New</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Verdana">Verdana</option>
                                    <option value="Tahoma">Tahoma</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tamaño de Fuente (pt)</label>
                                <input type="number" class="form-control form-control-sm" id="fontSize" min="6" max="24" value="8">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Color de Texto</label>
                                <input type="color" class="form-control form-control-color" id="fontColor" value="#003d7a">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Peso de Fuente</label>
                                <select class="form-select form-select-sm" id="fontWeight">
                                    <option value="normal">Normal</option>
                                    <option value="bold">Negrita</option>
                                    <option value="100">Delgada</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Color de Fondo</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="backgroundColor" value="#ffffff">
                                    <button type="button" class="btn btn-outline-secondary" id="clearBackground">
                                        <i class="uil uil-times"></i> Sin fondo
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Opacidad de Fondo</label>
                                <input type="range" class="form-range" id="backgroundOpacity" min="0" max="100" value="90">
                                <small class="text-muted" id="opacityValue">90%</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Alineación de Texto</label>
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="alignLeft" title="Izquierda">
                                        <i class="uil uil-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="alignCenter" title="Centro">
                                        <i class="uil uil-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="alignRight" title="Derecha">
                                        <i class="uil uil-align-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Rotación</label>
                                <div class="row g-2 mb-2">
                                    <div class="col-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-rotation="0">0°</button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-rotation="90">90°</button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-rotation="180">180°</button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-rotation="270">270°</button>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" id="customRotation" min="0" max="360" value="0" placeholder="Ángulo personalizado">
                                    <span class="input-group-text">°</span>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-danger w-100" id="removeField">
                                <i class="uil uil-trash"></i> Eliminar Campo
                            </button>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="uil uil-save me-1"></i> Guardar Plantilla
                            </button>
                            <a href="{{ route('carnets.templates.index') }}" class="btn btn-secondary w-100">
                                <i class="uil uil-times me-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    @if(isset($template) && $template->campos_config)
    <script>
        // Pasar datos de plantilla existente al JavaScript
        window.templateData = {
            id: {{ $template->id }},
            nombre: "{{ $template->nombre }}",
            tipo: "{{ $template->tipo }}",
            fondo_path: "{{ $template->fondo_path ?? '' }}",
            campos_config: {!! json_encode($template->campos_config) !!}
        };
    </script>
    @endif
    
    <script src="{{ asset('js/carnets/template-editor.js') }}"></script>
@endpush

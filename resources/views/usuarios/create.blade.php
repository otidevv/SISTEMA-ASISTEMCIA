@extends('layouts.app')

@section('title', 'Crear Usuario')
@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Estilos para el botón de mostrar/ocultar contraseña */
        .password-toggle {
            border-left: 0;
            background-color: #fff;
        }

        .password-toggle:hover {
            background-color: #f7f7f7;
        }

        .password-toggle:focus,
        .password-toggle:active {
            box-shadow: none;
            outline: none;
        }

        .input-group>.form-control:focus {
            z-index: 3;
        }

        .is-loading {
            background-image: url('assets/images/loading.gif');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
        }

        #btn-buscar-dni {
            margin-left: 0px;
        }

        /* Estilos para validación */
        .was-validated .form-control:valid {
            border-color: #1abc9c;
            padding-right: calc(1.5em + 0.9rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%231abc9c' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.225rem) center;
            background-size: calc(0.75em + 0.45rem) calc(0.75em + 0.45rem);
        }

        .was-validated .form-control:invalid {
            border-color: #f1556c;
            padding-right: calc(1.5em + 0.9rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23f1556c'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23f1556c' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.225rem) center;
            background-size: calc(0.75em + 0.45rem) calc(0.75em + 0.45rem);
        }

        .valid-tooltip,
        .invalid-tooltip {
            position: absolute;
            top: 100%;
            z-index: 5;
            display: none;
            max-width: 100%;
            padding: 0.25rem 0.5rem;
            margin-top: 0.1rem;
            font-size: 0.75rem;
            line-height: 1.5;
            border-radius: 0.25rem;
        }

        .valid-tooltip {
            color: #fff;
            background-color: rgba(26, 188, 156, 0.9);
        }

        .invalid-tooltip {
            color: #fff;
            background-color: rgba(241, 85, 108, 0.9);
        }

        .was-validated .form-control:valid~.valid-tooltip,
        .was-validated .form-control:invalid~.invalid-tooltip {
            display: block;
        }

        .position-relative {
            position: relative !important;
        }
    </style>
@endpush

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
                            <li class="breadcrumb-item active">Crear Usuario</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Crear Nuevo Usuario</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mt-0 mb-1">Crear Nuevo Usuario</h4>
                        <p class="sub-header">Complete todos los campos obligatorios marcados con <span
                                class="text-danger">*</span></p>

                        <form action="{{ route('usuarios.store') }}" method="POST" class="needs-validation" novalidate>
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Información de Cuenta</h5>
                                    <div class="position-relative mb-3">
                                        <label for="username" class="form-label">Nombre de Usuario <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('username') is-invalid @enderror"
                                            id="username" name="username" value="{{ old('username') }}" required>
                                        <div class="valid-tooltip">
                                            ¡Perfecto!
                                        </div>
                                        <div class="invalid-tooltip">
                                            Por favor ingrese un nombre de usuario.
                                        </div>
                                        @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="position-relative mb-3">
                                        <label for="email" class="form-label">Correo Electrónico <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email') }}" required>
                                        <div class="valid-tooltip">
                                            ¡Perfecto!
                                        </div>
                                        <div class="invalid-tooltip">
                                            Por favor ingrese un correo electrónico válido.
                                        </div>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="position-relative mb-3">
                                        <label for="password" class="form-label">Contraseña <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror" id="password"
                                                name="password" required>
                                            <button class="btn btn-light password-toggle" type="button"
                                                data-target="password">
                                                <i class="uil uil-eye"></i>
                                            </button>
                                        </div>
                                        <div class="valid-tooltip">
                                            ¡Perfecto!
                                        </div>
                                        <div class="invalid-tooltip">
                                            Por favor ingrese una contraseña.
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="position-relative mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmar Contraseña <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password_confirmation"
                                                name="password_confirmation" required>
                                            <button class="btn btn-light password-toggle" type="button"
                                                data-target="password_confirmation">
                                                <i class="uil uil-eye"></i>
                                            </button>
                                        </div>
                                        <div class="valid-tooltip password-match-valid">
                                            ¡Las contraseñas coinciden!
                                        </div>
                                        <div class="invalid-tooltip password-match-invalid">
                                            Las contraseñas no coinciden.
                                        </div>
                                    </div>

                                    <div class="position-relative mb-3" data-select2-id="select2-data-15-njaq">
                                        <label for="roles" class="form-label">Roles <span
                                                class="text-danger">*</span></label>
                                        <select
                                            class="form-select select2-hidden-accessible @error('roles') is-invalid @enderror"
                                            data-plugin="customselect" id="roles" name="roles[]" multiple required
                                            data-select2-id="select2-data-roles" tabindex="-1" aria-hidden="true">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ in_array($role->id, old('roles', [])) ? 'selected' : '' }}
                                                    data-select2-id="select2-data-role-{{ $role->id }}">
                                                    {{ $role->nombre }} - {{ $role->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-tooltip">
                                            Por favor seleccione al menos un rol.
                                        </div>
                                        @error('roles')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="mb-3">Información Personal</h5>
                                    <div class="position-relative mb-3">
                                        <label for="nombre" class="form-label">Nombre <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                            id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                                        <div class="valid-tooltip">
                                            ¡Perfecto!
                                        </div>
                                        <div class="invalid-tooltip">
                                            Por favor ingrese su nombre.
                                        </div>
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="position-relative mb-3">
                                        <label for="apellido_paterno" class="form-label">Apellido Paterno <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('apellido_paterno') is-invalid @enderror"
                                            id="apellido_paterno" name="apellido_paterno"
                                            value="{{ old('apellido_paterno') }}" required>
                                        <div class="valid-tooltip">
                                            ¡Perfecto!
                                        </div>
                                        <div class="invalid-tooltip">
                                            Por favor ingrese su apellido paterno.
                                        </div>
                                        @error('apellido_paterno')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="position-relative mb-3">
                                        <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                        <input type="text"
                                            class="form-control @error('apellido_materno') is-invalid @enderror"
                                            id="apellido_materno" name="apellido_materno"
                                            value="{{ old('apellido_materno') }}">
                                        <div class="valid-tooltip">
                                            ¡Perfecto!
                                        </div>
                                        @error('apellido_materno')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="position-relative mb-3">
                                                <label for="tipo_documento" class="form-label">Tipo Documento <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control @error('tipo_documento') is-invalid @enderror"
                                                    id="tipo_documento" name="tipo_documento" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="DNI"
                                                        {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                    <option value="Pasaporte"
                                                        {{ old('tipo_documento') == 'Pasaporte' ? 'selected' : '' }}>
                                                        Pasaporte</option>
                                                    <option value="CE"
                                                        {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carné de
                                                        Extranjería</option>
                                                </select>
                                                <div class="invalid-tooltip">
                                                    Por favor seleccione un tipo de documento.
                                                </div>
                                                @error('tipo_documento')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative mb-3">
                                                <label for="numero_documento" class="form-label">Número Documento <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="text"
                                                        class="form-control @error('numero_documento') is-invalid @enderror"
                                                        id="numero_documento" name="numero_documento"
                                                        value="{{ old('numero_documento') }}" required>
                                                    <button type="button" id="btn-buscar-dni" class="btn btn-info">
                                                        <i class="uil uil-search"></i>
                                                    </button>
                                                    <div class="invalid-tooltip">
                                                        Por favor ingrese el número de documento.
                                                    </div>
                                                </div>
                                                @error('numero_documento')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="position-relative mb-3">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="text"
                                                    class="form-control @error('telefono') is-invalid @enderror"
                                                    id="telefono" name="telefono" value="{{ old('telefono') }}">
                                                @error('telefono')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="position-relative mb-3">
                                                <label for="fecha_nacimiento" class="form-label">Fecha de
                                                    Nacimiento</label>
                                                <input type="date"
                                                    class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                                    id="fecha_nacimiento" name="fecha_nacimiento"
                                                    value="{{ old('fecha_nacimiento') }}">
                                                @error('fecha_nacimiento')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="position-relative mb-3">
                                        <label for="genero" class="form-label">Género</label>
                                        <select class="form-control @error('genero') is-invalid @enderror" id="genero"
                                            name="genero">
                                            <option value="">Seleccione</option>
                                            <option value="Masculino"
                                                {{ old('genero') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                            <option value="Femenino" {{ old('genero') == 'Femenino' ? 'selected' : '' }}>
                                                Femenino</option>
                                            <option value="Otro" {{ old('genero') == 'Otro' ? 'selected' : '' }}>Otro
                                            </option>
                                            <option value="Prefiero no decir"
                                                {{ old('genero') == 'Prefiero no decir' ? 'selected' : '' }}>Prefiero no
                                                decir</option>
                                        </select>
                                        @error('genero')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="position-relative mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <textarea class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion"
                                            rows="2">{{ old('direccion') }}</textarea>
                                        @error('direccion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                            </div>
                        </form>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bootstrap Validation JS -->
    <script>
        // Activar validación Bootstrap
        (function() {
            'use strict'

            // Fetch all forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>

    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                placeholder: "Seleccione roles",
                allowClear: true
            });

            // Variable para controlar si ya se realizó una consulta
            let consultaRealizada = false;

            // Reposicionar el botón de búsqueda para que aparezca junto al campo
            // Esta línea ya no es necesaria porque lo hemos estructurado como input-group en el HTML

            // Evento para detectar cuando se escriben 8 dígitos
            $('#numero_documento').on('keyup', function() {
                const tipoDoc = $('#tipo_documento').val();
                const numDoc = $(this).val().trim();

                // Consultar automáticamente al escribir 8 dígitos
                if (tipoDoc === 'DNI' && numDoc.length === 8 && !consultaRealizada) {
                    consultarDNI(numDoc);
                }
            });

            // Evento cuando se pierde el foco del campo
            $('#numero_documento').on('blur', function() {
                const tipoDoc = $('#tipo_documento').val();
                const numDoc = $(this).val().trim();

                if (tipoDoc === 'DNI' && numDoc.length === 8 && !consultaRealizada) {
                    consultarDNI(numDoc);
                }
            });

            // Reiniciar la variable cuando se cambie el tipo de documento o se modifique el número
            $('#tipo_documento').on('change', function() {
                consultaRealizada = false;
            });

            $('#numero_documento').on('input', function() {
                if ($(this).val().trim().length !== 8) {
                    consultaRealizada = false;
                }
            });

            // Evento para el botón de búsqueda
            $(document).on('click', '#btn-buscar-dni', function() {
                const tipoDoc = $('#tipo_documento').val();
                const numDoc = $('#numero_documento').val().trim();

                if (tipoDoc === 'DNI' && numDoc.length === 8) {
                    consultaRealizada = false; // Permitir nueva consulta
                    consultarDNI(numDoc);
                } else if (tipoDoc !== 'DNI') {
                    alert('Seleccione tipo de documento DNI');
                } else {
                    alert('Ingrese un DNI válido de 8 dígitos');
                }
            });
            // Función para consultar la API y llenar el formulario
            function consultarDNI(numeroDNI) {
                // Mostrar indicador de carga
                $('#numero_documento').addClass('is-loading');
                $('#btn-buscar-dni').prop('disabled', true).html('<i class="uil uil-spinner-alt spin"></i>');

                // Realizar la consulta a la API A TRAVÉS DEL PROXY
                $.ajax({
                    url: `/api/consulta/${numeroDNI}`, // URL del proxy local
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log("Datos recibidos:", data); // Verificar datos

                        // Llenar los campos del formulario con los datos obtenidos
                        $('#nombre').val(data.NOMBRES);
                        $('#apellido_paterno').val(data.AP_PAT);
                        $('#apellido_materno').val(data.AP_MAT);

                        // Generar username a partir del nombre (opcional)
                        const primerNombre = data.NOMBRES.split(' ')[0].toLowerCase();
                        const username = primerNombre + '.' + data.AP_PAT.toLowerCase();
                        $('#username').val(username);

                        // Convertir formato de fecha si es necesario (YYYY-MM-DD)
                        if (data.FECHA_NAC) {
                            $('#fecha_nacimiento').val(data.FECHA_NAC);
                        }

                        // Convertir código de género a texto seleccionable
                        if (data.SEXO === "1") {
                            $('#genero').val('Masculino');
                        } else if (data.SEXO === "2") {
                            $('#genero').val('Femenino');
                        }

                        // Dirección
                        if (data.DIRECCION) {
                            $('#direccion').val(data.DIRECCION);
                        }

                        // Marcar que ya se realizó la consulta
                        consultaRealizada = true;

                        // Mostrar notificación de éxito (verificamos si SweetAlert está disponible)
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: '¡Datos encontrados!',
                                text: 'Se han completado los campos automáticamente',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Datos encontrados: Se han completado los campos automáticamente');
                        }

                        // Actualizar validaciones visuales
                        $('#nombre, #apellido_paterno, #apellido_materno').trigger('change');

                        // Marcar formulario como validado si se completaron los campos requeridos
                        const form = document.querySelector('.needs-validation');
                        if (form.checkValidity()) {
                            form.classList.add('was-validated');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en la consulta:", error);
                        console.log("Respuesta:", xhr.responseText);

                        // Mostrar mensaje de error (verificamos si SweetAlert está disponible)
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Error',
                                text: 'No se pudieron obtener los datos. Verifique el número de documento.',
                                icon: 'error'
                            });
                        } else {
                            alert(
                                'Error: No se pudieron obtener los datos. Verifique el número de documento.'
                            );
                        }
                    },
                    complete: function() {
                        // Quitar indicador de carga
                        $('#numero_documento').removeClass('is-loading');
                        $('#btn-buscar-dni').prop('disabled', false).html(
                            '<i class="uil uil-search"></i>');
                    }
                });
            }
        });

        // Funcionalidad para mostrar/ocultar contraseña
        $('.password-toggle').on('click', function() {
            const targetId = $(this).data('target');
            const passwordInput = $('#' + targetId);
            const icon = $(this).find('i');

            // Cambiar tipo de input
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('uil-eye').addClass('uil-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('uil-eye-slash').addClass('uil-eye');
            }
        });

        // Validación de coincidencia de contraseñas
        function validatePasswordMatch() {
            const password = $('#password').val();
            const confirmPassword = $('#password_confirmation').val();

            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    $('.password-match-valid').show();
                    $('.password-match-invalid').hide();
                    $('#password_confirmation').removeClass('is-invalid').addClass('is-valid');
                    return true;
                } else {
                    $('.password-match-valid').hide();
                    $('.password-match-invalid').show();
                    $('#password_confirmation').removeClass('is-valid').addClass('is-invalid');
                    return false;
                }
            }
            return true; // Si está vacío, no mostrar error
        }

        // Eventos para validar coincidencia
        $('#password, #password_confirmation').on('keyup', validatePasswordMatch);

        // Validación adicional en el submit del formulario
        $('form.needs-validation').on('submit', function(event) {
            if (!validatePasswordMatch()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    </script>
@endpush

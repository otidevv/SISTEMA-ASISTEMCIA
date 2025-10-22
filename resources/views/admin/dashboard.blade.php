@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Loading indicators -->
    <div id="dashboard-loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p class="mt-2">Cargando datos del dashboard...</p>
    </div>

    <!-- Dashboard content (initially hidden) -->
    <div id="dashboard-content" style="display: none;">
        <!-- Stats cards -->
        <div class="row" id="stats-cards">
            <!-- Cards will be loaded dynamically -->
        </div>

        <!-- Additional sections -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-3">Anuncios Activos</h4>
                        <div id="anuncios-loading" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="sr-only">Cargando...</span>
                            </div>
                        </div>
                        <div id="anuncios-content" style="display: none;">
                            <!-- Anuncios will be loaded dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin specific content -->
        <div class="row mt-4" id="admin-section" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-3">Estadísticas del Sistema</h4>
                        <div id="admin-loading" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="sr-only">Cargando...</span>
                            </div>
                        </div>
                        <div id="admin-stats" style="display: none;">
                            <!-- Admin stats will be loaded dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // API Token configuration
    const apiToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Dashboard loading functions
    async function loadDashboardData() {
        const startTime = Date.now();
        console.log('Iniciando carga del dashboard...');

        try {
            // Show loading
            document.getElementById('dashboard-loading').style.display = 'block';
            document.getElementById('dashboard-content').style.display = 'none';

            // Load all data in parallel for better performance
            console.log('Cargando todos los datos en paralelo...');
            const promises = [
                loadGeneralData(),
                loadAnuncios(),
                checkAndLoadAdminData()
            ];

            await Promise.all(promises);
            console.log(`Todos los datos cargados en paralelo en ${Date.now() - startTime}ms`);

            // Hide loading and show content
            document.getElementById('dashboard-loading').style.display = 'none';
            document.getElementById('dashboard-content').style.display = 'block';

            console.log(`Dashboard completamente cargado en ${Date.now() - startTime}ms`);

        } catch (error) {
            console.error('Error loading dashboard:', error);
            document.getElementById('dashboard-loading').innerHTML =
                '<div class="alert alert-danger">Error al cargar el dashboard. Por favor, recarga la página.</div>';
        }
    }
    
    async function loadGeneralData() {
        try {
            const response = await fetch('/api/dashboard/datos-generales', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': apiToken
                }
            });
            
            if (!response.ok) throw new Error('Error loading general data');
            
            const data = await response.json();
            
            // Render stats cards
            const statsHtml = `
                <div class="col-lg-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-account-multiple widget-icon bg-primary-lighten text-primary"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Total Usuarios</h5>
                            <h3 class="mt-3 mb-3">${data.totalUsuarios}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-school widget-icon bg-success-lighten text-success"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Estudiantes</h5>
                            <h3 class="mt-3 mb-3">${data.totalEstudiantes}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-human-male-board widget-icon bg-info-lighten text-info"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Profesores</h5>
                            <h3 class="mt-3 mb-3">${data.totalProfesores}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card widget-flat">
                        <div class="card-body">
                            <div class="float-end">
                                <i class="mdi mdi-account-child widget-icon bg-warning-lighten text-warning"></i>
                            </div>
                            <h5 class="text-muted fw-normal mt-0">Padres</h5>
                            <h3 class="mt-3 mb-3">${data.totalPadres}</h3>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('stats-cards').innerHTML = statsHtml;
            
        } catch (error) {
            console.error('Error loading general data:', error);
        }
    }
    
    async function loadAnuncios() {
        try {
            const response = await fetch('/api/dashboard/anuncios', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': apiToken
                }
            });
            
            if (!response.ok) throw new Error('Error loading anuncios');
            
            const anuncios = await response.json();
            
            document.getElementById('anuncios-loading').style.display = 'none';
            
            if (anuncios.length === 0) {
                document.getElementById('anuncios-content').innerHTML = 
                    '<p class="text-muted">No hay anuncios activos</p>';
            } else {
                let anunciosHtml = '';
                anuncios.forEach(anuncio => {
                    const fecha = new Date(anuncio.fecha_publicacion).toLocaleDateString('es-ES');
                    anunciosHtml += `
                        <div class="border-bottom pb-2 mb-2">
                            <h5 class="mb-1">${anuncio.titulo}</h5>
                            <p class="text-muted small mb-1">${fecha}</p>
                            <p class="mb-0">${anuncio.contenido.substring(0, 100)}...</p>
                        </div>
                    `;
                });
                document.getElementById('anuncios-content').innerHTML = anunciosHtml;
            }
            
            document.getElementById('anuncios-content').style.display = 'block';
            
        } catch (error) {
            console.error('Error loading anuncios:', error);
            document.getElementById('anuncios-loading').innerHTML = 
                '<div class="alert alert-warning">Error al cargar anuncios</div>';
        }
    }
    
    async function checkAndLoadAdminData() {
        try {
            const response = await fetch('/api/dashboard/admin', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': apiToken
                }
            });
            
            if (response.status === 403) {
                // User is not admin, hide admin section
                document.getElementById('admin-section').style.display = 'none';
                return;
            }
            
            if (!response.ok) throw new Error('Error loading admin data');
            
            const data = await response.json();
            
            document.getElementById('admin-section').style.display = 'block';
            document.getElementById('admin-loading').style.display = 'none';
            
            // Render admin stats
            let adminHtml = '<div class="row">';
            
            if (data.cicloActivo) {
                adminHtml += `
                    <div class="col-md-12 mb-3">
                        <h5>Ciclo Activo: ${data.cicloActivo.nombre}</h5>
                    </div>
                `;
            }
            
            adminHtml += `
                <div class="col-md-3">
                    <div class="card-body bg-light rounded">
                        <h6>Total Inscripciones</h6>
                        <h4>${data.totalInscripciones}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-body bg-light rounded">
                        <h6>Total Carreras</h6>
                        <h4>${data.totalCarreras}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-body bg-light rounded">
                        <h6>Total Aulas</h6>
                        <h4>${data.totalAulas}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-body bg-light rounded">
                        <h6>Total Cursos</h6>
                        <h4>${data.totalCursos}</h4>
                    </div>
                </div>
            `;
            
            if (data.asistenciaHoy) {
                adminHtml += `
                    <div class="col-md-12 mt-3">
                        <h5>Asistencia de Hoy</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <p>Total Registros: <strong>${data.asistenciaHoy.total_registros}</strong></p>
                            </div>
                            <div class="col-md-4">
                                <p>Presentes: <strong class="text-success">${data.asistenciaHoy.presentes}</strong></p>
                            </div>
                            <div class="col-md-4">
                                <p>Ausentes: <strong class="text-danger">${data.asistenciaHoy.ausentes}</strong></p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            if (data.estadisticasAsistencia) {
                const stats = data.estadisticasAsistencia;
                adminHtml += `
                    <div class="col-md-12 mt-3">
                        <h5>Estadísticas de Asistencia del Ciclo</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <p>Regulares: <strong class="text-success">${stats.regulares} (${stats.porcentaje_regulares}%)</strong></p>
                            </div>
                            <div class="col-md-3">
                                <p>Amonestados: <strong class="text-warning">${stats.amonestados} (${stats.porcentaje_amonestados}%)</strong></p>
                            </div>
                            <div class="col-md-3">
                                <p>Inhabilitados: <strong class="text-danger">${stats.inhabilitados} (${stats.porcentaje_inhabilitados}%)</strong></p>
                            </div>
                            <div class="col-md-3">
                                <p>Total: <strong>${stats.total_estudiantes}</strong></p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            adminHtml += '</div>';
            
            document.getElementById('admin-stats').innerHTML = adminHtml;
            document.getElementById('admin-stats').style.display = 'block';
            
        } catch (error) {
            console.error('Error loading admin data:', error);
        }
    }
    
    // Initialize dashboard on page load
    loadDashboardData();
});
</script>
@endpush
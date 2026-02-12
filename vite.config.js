import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/brand-cepre.css',
                'resources/js/csrf-handler.js',
                'resources/js/inscripciones/index.js',
                'resources/js/usuarios/index.js',
                'resources/js/turnos/index.js',
                'resources/js/postulaciones/index.js',
                'resources/js/postulaciones/wizard-completo.js',
                'resources/js/postulaciones/wizard-simplificado.js',
                'resources/js/postulaciones/publico-modal.js',
                'resources/js/perfil/index.js',
                'resources/js/parentescos/index.js',
                'resources/js/carreras/index.js',
                'resources/js/carga-horaria/index.js',
                'resources/js/carnets/index.js',
                'resources/js/aulas/index.js',
                'resources/js/asistencia/editar_index.js',
                'resources/js/dashboard-progressive-loading.js',
                'resources/js/ciclos/index.js',
                'resources/js/dashboardestudiante/index.js',
                'resources/js/reportes/index.js',
                'resources/js/carnets/template-editor.js',
                'resources/js/postulaciones/unificado.js',
                'resources/js/postulaciones/wizard.js',
                'resources/js/horarios-grid.js',
                'resources/css/modal-fixes.css'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});

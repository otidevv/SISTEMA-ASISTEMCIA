import './bootstrap';

// Código específico para la vista de monitor de asistencia
if (document.getElementById('registros-container')) {
    // Funciones auxiliares
    // Funciones auxiliares - defínelas ANTES de usarlas
    function createPhotoHtml(registro) {
        if (registro.foto_url) {
            return `<img src="${registro.foto_url}" alt="Foto" class="student-photo">`;
        } else {
            const inicial = registro.iniciales || 'U';
            return `<div class="student-photo-initial">${inicial}</div>`;
        }
    }

    function createSmallPhotoHtml(registro) {
        if (registro.foto_url) {
            return `<img src="${registro.foto_url}" alt="Foto" width="40" height="40" class="rounded-circle">`;
        } else {
            const inicial = registro.iniciales || 'U';
            return `<div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">${inicial}</div>`;
        }
    }

    // Configurar el canal
    window.Echo.channel('asistencia-channel')
        .listen('NuevoRegistroAsistencia', (e) => {
            console.log('Nuevo registro recibido:', e);
            // Resto de tu código...
        });
}



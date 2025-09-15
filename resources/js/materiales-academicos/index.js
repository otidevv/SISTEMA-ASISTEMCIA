document.addEventListener('DOMContentLoaded', function () {
    // Inicializar DataTables
    const materialesTable = $('#materiales-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.default_server + '/materiales-academicos',
            type: 'GET',
            data: function (d) {
                // Puedes añadir parámetros adicionales si es necesario
            }
        },
        columns: [
            { data: 'titulo', name: 'titulo' },
            { data: 'curso.nombre', name: 'curso.nombre', defaultContent: 'N/A' },
            { data: 'profesor.nombre_completo', name: 'profesor.nombre_completo', defaultContent: 'N/A' },
            { data: 'semana', name: 'semana' },
            { data: 'tipo', name: 'tipo' },
            { data: 'created_at', name: 'created_at' },
            { data: 'url_debug', name: 'url_debug', title: 'URL de Depuración' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-es.json'
        },
        order: [[5, 'desc']] // Ordenar por fecha de subida descendente
    });

    // Manejar eliminación de material
    $(document).on('click', '.delete-material', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminarlo!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

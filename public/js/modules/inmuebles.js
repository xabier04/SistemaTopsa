/**
 * Módulo JS: Inmuebles
 * Búsqueda interactiva y eliminación AJAX
 */
document.addEventListener('DOMContentLoaded', () => {
    // Búsqueda en tiempo real con debounce
    const searchInput = document.getElementById('searchInmuebles');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            const term = e.target.value.trim().toLowerCase();
            const rows = document.querySelectorAll('#tablaInmuebles tbody tr[data-id]');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        }, 200));
    }

    // Eliminar inmueble
    document.querySelectorAll('#tablaInmuebles .btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const baseUrl = App.baseUrl.replace(/\/$/, '');
            
            Modal.confirm(
                'Eliminar Inmueble',
                `¿Está seguro que desea eliminar el inmueble con matrícula <strong>${name}</strong>? Esta acción no se puede deshacer.`,
                async () => {
                    try {
                        const result = await App.post(`${baseUrl}/inmueble/delete/${id}`, new FormData());
                        Toast.success(result.message || 'Inmueble eliminado');
                        const row = btn.closest('tr');
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.95)';
                        row.style.transition = 'all 0.3s ease';
                        setTimeout(() => row.remove(), 300);
                    } catch (error) {
                        Toast.error(error.message || 'No se pudo eliminar el inmueble');
                    }
                },
                'Eliminar'
            );
        });
    });
});

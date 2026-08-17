/**
 * Módulo JS: Clientes
 */
document.addEventListener('DOMContentLoaded', () => {
    // Búsqueda con debounce
    const searchInput = document.getElementById('searchClientes');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            const term = e.target.value.trim();
            const rows = document.querySelectorAll('#tablaClientes tbody tr[data-id]');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term.toLowerCase()) ? '' : 'none';
            });
        }, 250));
    }

    // Eliminar cliente
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            
            Modal.confirm(
                'Eliminar Cliente',
                `¿Está seguro que desea eliminar al cliente <strong>${name}</strong>? Esta acción no se puede deshacer.`,
                async () => {
                    try {
                        const result = await App.post(`${window.location.origin}/cliente/delete/${id}`, new FormData());
                        Toast.success(result.message);
                        btn.closest('tr').remove();
                    } catch (error) {
                        Toast.error(error.message || 'Error al eliminar');
                    }
                },
                'Eliminar'
            );
        });
    });
});

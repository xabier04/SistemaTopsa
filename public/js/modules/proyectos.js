/**
 * Módulo JS: Proyectos
 */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchProyectos');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('table tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        }, 250));
    }

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            
            Modal.confirm(
                'Eliminar Proyecto',
                `¿Está seguro que desea eliminar el proyecto <strong>${name}</strong>? Se eliminarán todos los registros asociados.`,
                async () => {
                    try {
                        const result = await App.post(`${window.location.origin}/proyecto/delete/${id}`, new FormData());
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

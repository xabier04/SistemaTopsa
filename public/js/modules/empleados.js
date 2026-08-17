/**
 * Módulo JS: Empleados
 */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchEmpleados');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('#tablaEmpleados tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        }, 250));
    }

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            
            Modal.confirm(
                'Eliminar Empleado',
                `¿Está seguro que desea eliminar a <strong>${name}</strong>?`,
                async () => {
                    try {
                        const result = await App.post(`${window.location.origin}/empleado/delete/${id}`, new FormData());
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

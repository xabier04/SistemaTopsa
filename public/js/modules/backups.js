/**
 * Módulo JS: Backups
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-delete-backup').forEach(btn => {
        btn.addEventListener('click', () => {
            const name = btn.dataset.name;
            
            Modal.confirm(
                'Eliminar Backup',
                `¿Está seguro que desea eliminar el backup <strong>${name}</strong>?`,
                async () => {
                    try {
                        const fd = new FormData();
                        fd.append('nombre', name);
                        const result = await App.post(`${window.location.origin}/backup/eliminar`, fd);
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

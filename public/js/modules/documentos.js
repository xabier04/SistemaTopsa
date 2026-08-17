/**
 * Módulo JS: Documentos
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-delete-doc').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            
            Modal.confirm(
                'Eliminar Documento',
                `¿Está seguro que desea eliminar <strong>${name}</strong>? El archivo será eliminado permanentemente.`,
                async () => {
                    try {
                        const result = await App.post(`${window.location.origin}/documento/delete/${id}`, new FormData());
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

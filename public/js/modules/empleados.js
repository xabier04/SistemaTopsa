/**
 * Módulo JS: Empleados
 */
document.addEventListener('DOMContentLoaded', () => {
    // ─── Búsqueda interactiva ─────────────────────────────────────
    const searchInput = document.getElementById('searchEmpleados');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('#tablaEmpleados tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        }, 250));
    }

    // ─── Acción Eliminar Empleado ────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const baseUrl = App.baseUrl.replace(/\/$/, '');
            
            Modal.confirm(
                'Eliminar Empleado',
                `¿Está seguro que desea eliminar a <strong>${name}</strong>? Esta acción no se puede deshacer.`,
                async () => {
                    try {
                        const fd = new FormData();
                        const csrf = App.getCsrfToken();
                        if (csrf) fd.append('_csrf_token', csrf);

                        const result = await App.post(`${baseUrl}/empleado/delete/${id}`, fd);
                        if (result.success) {
                            Toast.success(result.message || 'Empleado eliminado exitosamente.');
                            const row = btn.closest('tr');
                            if (row) {
                                row.style.transition = 'all 0.3s ease';
                                row.style.opacity = '0';
                                setTimeout(() => row.remove(), 300);
                            }
                        } else {
                            Toast.error(result.message || 'No se pudo eliminar el empleado.');
                        }
                    } catch (error) {
                        Toast.error(error.message || 'Error de conexión al eliminar.');
                    }
                },
                'Eliminar'
            );
        });
    });

    // ─── Control Visual de Estado (Radio Cards) ─────────────────
    const statusPillGroup = document.querySelector('.status-pill-group');
    if (statusPillGroup) {
        const updateStatusCards = () => {
            const cards = statusPillGroup.querySelectorAll('.status-pill-card');
            cards.forEach(card => {
                const radio = card.querySelector('input[name="estado"]');
                if (radio && radio.checked) {
                    card.classList.add('is-selected');
                } else {
                    card.classList.remove('is-selected');
                }
            });
        };

        statusPillGroup.addEventListener('change', (e) => {
            if (e.target && e.target.name === 'estado') {
                updateStatusCards();
            }
        });

        statusPillGroup.querySelectorAll('.status-pill-card').forEach(card => {
            card.addEventListener('click', () => {
                setTimeout(updateStatusCards, 0);
            });
        });

        updateStatusCards();
    }

    // ─── Validación previa de Formulario de Empleados ───────────
    const formEmpleado = document.getElementById('formEmpleado');
    if (formEmpleado) {
        formEmpleado.addEventListener('submit', (e) => {
            const nombreInput = document.getElementById('nombre_completo');
            if (nombreInput) {
                const val = nombreInput.value;
                if (/[0-9]/.test(val)) {
                    e.preventDefault();
                    Toast.error('El Nombre Completo no puede contener números.');
                    nombreInput.focus();
                    return;
                }
                if (/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\'\-]/.test(val)) {
                    e.preventDefault();
                    Toast.error('El Nombre Completo contiene símbolos especiales no permitidos.');
                    nombreInput.focus();
                    return;
                }
            }

            const telInput = document.getElementById('telefono');
            if (telInput && telInput.value.trim()) {
                const telVal = telInput.value.trim();
                const isValid = (typeof libphonenumber !== 'undefined')
                    ? libphonenumber.isValidPhoneNumber(telVal, 'SV')
                    : !/^(\d)\1{7,}$/.test(telVal.replace(/\D/g, ''));

                if (!isValid) {
                    e.preventDefault();
                    Toast.error('Ingrese un número de teléfono real válido (ej. 7000-0000). Se rechazan números ficticios como 0000-0000.');
                    telInput.focus();
                    return;
                }
            }
        });
    }
});

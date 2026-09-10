/**
 * Sistema TOPSA — JavaScript Global (app.js)
 * Helpers globales: Fetch API, modales, toasts, sidebar, utilidades
 */

// ─── Configuración Base ─────────────────────────────────────
const App = {
    baseUrl: document.querySelector('meta[name="base-url"]')?.content || window.location.origin,

    /**
     * Realizar petición AJAX con Fetch API
     */
    async fetch(url, options = {}) {
        const defaults = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        // Si es FormData, no establecer Content-Type (el navegador lo hace)
        if (!(options.body instanceof FormData)) {
            defaults.headers['Content-Type'] = 'application/json';
        }

        const config = { ...defaults, ...options };
        config.headers = { ...defaults.headers, ...options.headers };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `Error ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    },

    /**
     * GET request
     */
    async get(url) {
        return this.fetch(url);
    },

    /**
     * POST request con FormData
     */
    async post(url, formData) {
        return this.fetch(url, {
            method: 'POST',
            body: formData,
        });
    },

    /**
     * POST request con JSON
     */
    async postJson(url, data) {
        return this.fetch(url, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    },
};

// ─── Toast Notifications ────────────────────────────────────
const Toast = {
    container: null,

    init() {
        this.container = document.getElementById('toastContainer');
    },

    show(message, type = 'info', duration = 4000) {
        if (!this.container) this.init();

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle',
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="${icons[type] || icons.info}"></i>
            <span>${message}</span>
        `;

        this.container.appendChild(toast);

        // Auto-remover
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    success(msg) { this.show(msg, 'success'); },
    error(msg)   { this.show(msg, 'error'); },
    warning(msg) { this.show(msg, 'warning'); },
    info(msg)    { this.show(msg, 'info'); },
};

// ─── Modal Manager ──────────────────────────────────────────
const Modal = {
    backdrop: null,
    modal: null,
    titleEl: null,
    bodyEl: null,
    footerEl: null,

    init() {
        this.backdrop = document.getElementById('modalBackdrop');
        this.modal = document.getElementById('globalModal');
        this.titleEl = document.getElementById('modalTitle');
        this.bodyEl = document.getElementById('modalBody');
        this.footerEl = document.getElementById('modalFooter');

        // Close handlers
        document.getElementById('modalClose')?.addEventListener('click', () => this.close());
        this.backdrop?.addEventListener('click', (e) => {
            if (e.target === this.backdrop) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.backdrop?.style.display !== 'none') {
                this.close();
            }
        });
    },

    open(title, bodyHtml, footerHtml = '') {
        if (!this.backdrop) this.init();

        this.titleEl.textContent = title;
        this.bodyEl.innerHTML = bodyHtml;
        this.footerEl.innerHTML = footerHtml;
        this.backdrop.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    },

    close() {
        if (this.backdrop) {
            this.backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }
    },

    /**
     * Modal de confirmación
     */
    confirm(title, message, onConfirm, confirmText = 'Confirmar') {
        const body = `<p style="font-size: 0.9375rem; color: #4a5568;">${message}</p>`;
        const footer = `
            <button class="btn btn-outline" onclick="Modal.close()">Cancelar</button>
            <button class="btn btn-danger" id="modalConfirmBtn">${confirmText}</button>
        `;

        this.open(title, body, footer);

        document.getElementById('modalConfirmBtn')?.addEventListener('click', () => {
            this.close();
            if (typeof onConfirm === 'function') onConfirm();
        });
    },
};

// ─── Sidebar Logic ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const overlay = document.getElementById('sidebarOverlay');

    // Desktop: Toggle collapsed state
    sidebarToggle?.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
    });

    // Restore sidebar state
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        sidebar?.classList.add('collapsed');
    }

    // Mobile: Toggle open state
    mobileMenuToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('mobile-open');
        overlay?.classList.toggle('active');
    });

    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('mobile-open');
        overlay?.classList.remove('active');
    });

    // Initialize modules
    Toast.init();
    Modal.init();
    FormInteractivity.init();

    // Auto-dismiss flash alerts
    const flashAlert = document.getElementById('flashAlert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.opacity = '0';
            flashAlert.style.transform = 'translateY(-10px)';
            flashAlert.style.transition = 'all 0.3s ease';
            setTimeout(() => flashAlert.remove(), 300);
        }, 5000);
    }
});

// ─── Input Masks & Interactive Validations ──────────────────
const FormInteractivity = {
    init() {
        this.initDuiMask();
        this.initPhoneMask();
        this.initMatriculaMask();
        this.initEmailValidation();
        this.initDatepickers();
        this.initFormSubmissions();
    },

    /**
     * Máscara automática de DUI salvadoreño: ########-#
     */
    initDuiMask() {
        const inputs = document.querySelectorAll('input[data-mask="dui"], #dui');
        inputs.forEach(input => {
            const hint = document.getElementById('duiHint') || input.parentElement.querySelector('.field-hint');

            const updateDui = () => {
                let digits = input.value.replace(/\D/g, '').slice(0, 9);
                let formatted = digits;

                if (digits.length > 8) {
                    formatted = digits.slice(0, 8) + '-' + digits.slice(8, 9);
                }

                input.value = formatted;

                // Validación visual interactiva
                if (digits.length === 9) {
                    input.classList.add('is-valid');
                    input.classList.remove('is-invalid');
                    if (hint) {
                        hint.innerHTML = '<i class="fas fa-check-circle" style="color: #166534;"></i> DUI completo y válido';
                        hint.style.color = '#166534';
                    }
                } else if (digits.length > 0) {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                    if (hint) {
                        hint.innerHTML = `<i class="fas fa-exclamation-circle" style="color: #dc2626;"></i> Ingrese 9 dígitos (${digits.length}/9)`;
                        hint.style.color = '#dc2626';
                    }
                } else {
                    input.classList.remove('is-valid', 'is-invalid');
                    if (hint) {
                        hint.innerHTML = '<i class="fas fa-magic"></i> Guion automático (00000000-0)';
                        hint.style.color = '';
                    }
                }
            };

            input.addEventListener('input', updateDui);
            input.addEventListener('paste', () => setTimeout(updateDui, 10));
            if (input.value) updateDui();
        });
    },

    /**
     * Máscara automática de teléfono: ####-####
     */
    initPhoneMask() {
        const inputs = document.querySelectorAll('input[data-mask="phone"], #telefono');
        inputs.forEach(input => {
            const hint = document.getElementById('telefonoHint') || input.parentElement.querySelector('.field-hint');

            const updatePhone = () => {
                let digits = input.value.replace(/\D/g, '').slice(0, 8);
                let formatted = digits;

                if (digits.length > 4) {
                    formatted = digits.slice(0, 4) + '-' + digits.slice(4, 8);
                }

                input.value = formatted;

                // Validación visual interactiva
                if (digits.length === 8) {
                    input.classList.add('is-valid');
                    input.classList.remove('is-invalid');
                    if (hint) {
                        hint.innerHTML = '<i class="fas fa-check-circle" style="color: #166534;"></i> Teléfono completo y válido';
                        hint.style.color = '#166534';
                    }
                } else if (digits.length > 0) {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                    if (hint) {
                        hint.innerHTML = `<i class="fas fa-exclamation-circle" style="color: #dc2626;"></i> Ingrese 8 dígitos (${digits.length}/8)`;
                        hint.style.color = '#dc2626';
                    }
                } else {
                    input.classList.remove('is-valid', 'is-invalid');
                    if (hint) {
                        hint.innerHTML = '<i class="fas fa-magic"></i> Guion automático (0000-0000)';
                        hint.style.color = '';
                    }
                }
            };

            input.addEventListener('input', updatePhone);
            input.addEventListener('paste', () => setTimeout(updatePhone, 10));
            if (input.value) updatePhone();
        });
    },

    /**
     * Matrícula de inmuebles: Solo exactamente 8 números
     */
    initMatriculaMask() {
        const inputs = document.querySelectorAll('input[data-mask="matricula"], #matricula');
        inputs.forEach(input => {
            const hint = document.getElementById('matriculaHint') || input.parentElement.querySelector('.field-hint');

            const updateMatricula = () => {
                // Eliminar estrictamente todo carácter que NO sea número
                let digits = input.value.replace(/\D/g, '').slice(0, 8);
                input.value = digits;

                if (digits.length === 8) {
                    input.classList.add('is-valid');
                    input.classList.remove('is-invalid');
                    if (hint) {
                        hint.innerHTML = '<i class="fas fa-check-circle" style="color: #166534;"></i> Matrícula completa (8 de 8 números)';
                        hint.style.color = '#166534';
                    }
                } else if (digits.length > 0) {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                    if (hint) {
                        hint.innerHTML = `<i class="fas fa-exclamation-circle" style="color: #dc2626;"></i> Debe contener exactamente 8 números (${digits.length}/8)`;
                        hint.style.color = '#dc2626';
                    }
                } else {
                    input.classList.remove('is-valid', 'is-invalid');
                    if (hint) {
                        hint.innerHTML = '<i class="fas fa-fingerprint"></i> Solo 8 números (sin letras ni guiones)';
                        hint.style.color = '';
                    }
                }
            };

            input.addEventListener('input', updateMatricula);
            input.addEventListener('paste', () => setTimeout(updateMatricula, 10));
            input.addEventListener('keydown', (e) => {
                const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];
                if (allowed.includes(e.key) || (e.ctrlKey && ['a', 'c', 'v', 'x'].includes(e.key.toLowerCase()))) {
                    return;
                }
                if (!/^\d$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            if (input.value) updateMatricula();
        });
    },

    /**
     * Validación de correos electrónicos reales
     */
    initEmailValidation() {
        const inputs = document.querySelectorAll('input[type="email"], input[data-validate="email"]');
        const fakeDomains = [
            'test.com', 'example.com', 'fake.com', 'correo.com', 
            'prueba.com', 'temporal.com', 'mailinator.com', 'demo.com', 
            'nada.com', 'test.test', 'email.com', 'temp.com'
        ];

        inputs.forEach(input => {
            const hint = document.getElementById('emailHint') || input.parentElement.querySelector('.field-hint');

            const validateEmail = () => {
                const val = input.value.trim();
                if (!val) {
                    input.classList.remove('is-valid', 'is-invalid');
                    if (hint) {
                        hint.innerHTML = '<i class="fas fa-shield-alt"></i> Debe ser un correo real (ej. nombre@empresa.com)';
                        hint.style.color = '';
                    }
                    return;
                }

                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                const isValidFormat = emailRegex.test(val);
                const domain = val.includes('@') ? val.split('@')[1].toLowerCase() : '';
                const isFake = fakeDomains.includes(domain);

                if (isValidFormat && !isFake) {
                    input.classList.add('is-valid');
                    input.classList.remove('is-invalid');
                    if (hint) {
                        hint.innerHTML = '<i class="fas fa-check-circle" style="color: #166534;"></i> Correo electrónico real y válido';
                        hint.style.color = '#166534';
                    }
                } else {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                    if (hint) {
                        const msg = isFake ? 'Ingrese un dominio de correo real' : 'Formato de correo incompleto o inválido';
                        hint.innerHTML = `<i class="fas fa-exclamation-circle" style="color: #dc2626;"></i> ${msg}`;
                        hint.style.color = '#dc2626';
                    }
                }
            };

            input.addEventListener('input', validateEmail);
            input.addEventListener('blur', validateEmail);
            if (input.value) validateEmail();
        });
    },

    /**
     * Calendarios interactivos (Flatpickr con español)
     */
    initDatepickers() {
        if (typeof flatpickr !== 'undefined') {
            const dateInputs = document.querySelectorAll('.datepicker, input[type="date"]');
            dateInputs.forEach(el => {
                const fp = flatpickr(el, {
                    locale: (flatpickr.l10ns && flatpickr.l10ns.es) ? flatpickr.l10ns.es : 'es',
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    allowInput: false,
                    animate: true,
                    disableMobile: true,
                });

                // Si hay un botón/icono disparador al lado
                const trigger = el.parentElement?.querySelector('.datepicker-trigger');
                if (trigger) {
                    trigger.addEventListener('click', () => fp.open());
                }
            });
        }
    },

    /**
     * Manejo interactivo del envío de formularios con validación previa y spinner
     */
    initFormSubmissions() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                // Validar DUI si existe en el formulario
                const dui = form.querySelector('input[data-mask="dui"], #dui');
                if (dui && dui.hasAttribute('required')) {
                    const duiDigits = dui.value.replace(/\D/g, '');
                    if (duiDigits.length !== 9 || !/^\d{8}-\d$/.test(dui.value)) {
                        e.preventDefault();
                        Toast.error('El DUI debe contener 9 dígitos con su guion (00000000-0)');
                        dui.focus();
                        return;
                    }
                }

                // Validar Teléfono si existe en el formulario
                const tel = form.querySelector('input[data-mask="phone"], #telefono');
                if (tel && tel.hasAttribute('required')) {
                    const telDigits = tel.value.replace(/\D/g, '');
                    if (telDigits.length !== 8 || !/^\d{4}-\d{4}$/.test(tel.value)) {
                        e.preventDefault();
                        Toast.error('El Teléfono debe contener 8 dígitos con su guion (0000-0000)');
                        tel.focus();
                        return;
                    }
                }

                // Validar Matrícula si existe en el formulario
                const mat = form.querySelector('input[data-mask="matricula"], #matricula');
                if (mat) {
                    const matDigits = mat.value.replace(/\D/g, '');
                    if (matDigits.length !== 8) {
                        e.preventDefault();
                        Toast.error('La Matrícula debe contener exactamente 8 números');
                        mat.focus();
                        return;
                    }
                }

                // Validar Correo si existe
                const email = form.querySelector('input[type="email"], input[data-validate="email"]');
                if (email && email.hasAttribute('required')) {
                    const emailVal = email.value.trim();
                    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                    if (!emailRegex.test(emailVal)) {
                        e.preventDefault();
                        Toast.error('Ingrese un correo electrónico real con formato válido');
                        email.focus();
                        return;
                    }
                }

                // Deshabilitar botón de envío y mostrar spinner de carga
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    toggleButtonLoading(submitBtn, true);
                }
            });
        });
    }
};

// ─── Utility Functions ──────────────────────────────────────

/**
 * Formatear monto como moneda
 */
function formatMoney(amount) {
    return '$' + parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Formatear fecha ISO a dd/mm/yyyy
 */
function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
}

/**
 * Debounce function para búsquedas
 */
function debounce(fn, delay = 300) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

/**
 * Serializar un formulario a FormData
 */
function serializeForm(formId) {
    const form = document.getElementById(formId);
    return form ? new FormData(form) : new FormData();
}

/**
 * Inhabilitar/habilitar botón con spinner
 */
function toggleButtonLoading(btn, loading) {
    if (loading) {
        btn.dataset.originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
    }
}

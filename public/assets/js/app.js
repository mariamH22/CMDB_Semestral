document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const doc = document.documentElement;
    const qs = (selector, root = document) => root.querySelector(selector);
    const qsa = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    doc.classList.add('is-enhanced');

    const focusableSelector = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])'
    ].join(',');

    const sidebar = qs('#app-sidebar');
    const openSidebar = qs('[data-sidebar-open]');
    const closeSidebarTargets = qsa('[data-sidebar-close]');
    let lastFocusedElement = null;

    const setSidebarState = (open) => {
        if (!sidebar || !openSidebar) return;

        body.classList.toggle('is-sidebar-open', open);
        openSidebar.setAttribute('aria-expanded', open ? 'true' : 'false');
        closeSidebarTargets.forEach((target) => {
            target.hidden = !open;
        });

        if (open) {
            lastFocusedElement = document.activeElement;
            const firstTarget = qs(focusableSelector, sidebar);
            if (firstTarget) firstTarget.focus();
        } else if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
        }
    };

    if (openSidebar) {
        openSidebar.addEventListener('click', () => {
            setSidebarState(!body.classList.contains('is-sidebar-open'));
        });
    }

    closeSidebarTargets.forEach((target) => {
        target.addEventListener('click', () => setSidebarState(false));
    });

    document.addEventListener('keydown', (event) => {
        const sidebarOpen = body.classList.contains('is-sidebar-open');

        if (event.key === 'Escape' && sidebarOpen) {
            setSidebarState(false);
        }

        if (event.key !== 'Tab' || !sidebarOpen || !sidebar) return;

        const focusable = qsa(focusableSelector, sidebar);
        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    const createElement = (tag, className, text) => {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text) element.textContent = text;
        return element;
    };

    const updateModalState = () => {
        const modalOpen = Boolean(qs('.confirm-modal:not([hidden]), .gallery-modal:not([hidden])'));
        body.classList.toggle('is-modal-open', modalOpen);
    };

    const addWorkspaceBreadcrumb = () => {
        const main = qs('.app-main');
        if (!main || qs('.workspace-breadcrumb', main)) return;

        const current = qs('.nav-item[aria-current="page"]');
        const title = qs('.app-topbar strong');
        if (!current || !title) return;

        const nav = createElement('nav', 'workspace-breadcrumb');
        nav.setAttribute('aria-label', 'Ruta de navegación');

        const root = createElement('span', '', 'CMDB');
        const separator = createElement('span', 'breadcrumb-separator', '/');
        separator.setAttribute('aria-hidden', 'true');
        const page = createElement('span', '', current.textContent.trim() || title.textContent.trim());

        nav.appendChild(root);
        nav.appendChild(separator);
        nav.appendChild(page);
        main.prepend(nav);
    };

    addWorkspaceBreadcrumb();

    const firstAlert = qs('.alert');
    if (firstAlert) {
        firstAlert.tabIndex = -1;
        firstAlert.focus({ preventScroll: true });
        firstAlert.scrollIntoView({ block: 'nearest' });
    }

    qsa('[data-alert-close]').forEach((button) => {
        button.addEventListener('click', () => {
            const alert = button.closest('.alert');
            if (!alert) return;
            alert.classList.add('is-dismissing');
            window.setTimeout(() => alert.remove(), 180);
        });
    });

    const confirmModal = createElement('div', 'confirm-modal');
    confirmModal.hidden = true;
    confirmModal.setAttribute('role', 'dialog');
    confirmModal.setAttribute('aria-modal', 'true');
    confirmModal.setAttribute('aria-labelledby', 'confirm-title');
    confirmModal.setAttribute('aria-describedby', 'confirm-message');

    const confirmDialog = createElement('div', 'confirm-dialog');
    const confirmTitle = createElement('h2', '', 'Confirmar acción');
    confirmTitle.id = 'confirm-title';
    const confirmMessage = createElement('p');
    confirmMessage.id = 'confirm-message';
    const confirmActions = createElement('div', 'confirm-actions');
    const cancelConfirm = createElement('button', 'btn btn-light', 'Cancelar');
    cancelConfirm.type = 'button';
    const acceptConfirm = createElement('button', 'btn btn-danger', 'Confirmar');
    acceptConfirm.type = 'button';

    confirmActions.appendChild(cancelConfirm);
    confirmActions.appendChild(acceptConfirm);
    confirmDialog.appendChild(confirmTitle);
    confirmDialog.appendChild(confirmMessage);
    confirmDialog.appendChild(confirmActions);
    confirmModal.appendChild(confirmDialog);
    document.body.appendChild(confirmModal);

    let pendingAction = null;
    let confirmReturnFocus = null;

    const closeConfirm = () => {
        confirmModal.hidden = true;
        pendingAction = null;
        updateModalState();
        if (confirmReturnFocus && typeof confirmReturnFocus.focus === 'function') {
            confirmReturnFocus.focus();
        }
        confirmReturnFocus = null;
    };

    const openConfirm = (message, onAccept, trigger) => {
        confirmMessage.textContent = message || 'Desea continuar con esta acción?';
        pendingAction = onAccept;
        confirmReturnFocus = trigger || document.activeElement;
        confirmModal.hidden = false;
        updateModalState();
        cancelConfirm.focus();
    };

    cancelConfirm.addEventListener('click', closeConfirm);
    acceptConfirm.addEventListener('click', () => {
        const action = pendingAction;
        closeConfirm();
        if (typeof action === 'function') action();
    });
    confirmModal.addEventListener('click', (event) => {
        if (event.target === confirmModal) closeConfirm();
    });

    qsa('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();

            openConfirm(element.dataset.confirm || 'Desea continuar con esta acción?', () => {
                const href = element.getAttribute('href');
                const form = element.form || element.closest('form');

                if (href) {
                    window.location.assign(href);
                    return;
                }

                if (form && typeof form.requestSubmit === 'function') {
                    form.requestSubmit(element.matches('button, input') ? element : undefined);
                    return;
                }

                if (form) form.submit();
            }, element);
        });
    });

    qsa('[data-print-page]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });

    qsa('input[type="password"]').forEach((input) => {
        if (input.closest('.password-control')) return;

        const wrapper = createElement('div', 'password-control');
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const button = createElement('button', 'password-toggle', 'Mostrar');
        button.type = 'button';
        button.setAttribute('aria-label', 'Mostrar contraseña');
        button.setAttribute('aria-pressed', 'false');
        wrapper.appendChild(button);

        button.addEventListener('click', () => {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.textContent = visible ? 'Mostrar' : 'Ocultar';
            button.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            button.setAttribute('aria-pressed', visible ? 'false' : 'true');
        });
    });

    const setFieldError = (field, message) => {
        const group = field.closest('.form-group') || field.parentElement;
        if (!group) return;

        field.classList.toggle('is-invalid', Boolean(message));

        let error = qs('.inline-error[data-generated-error="true"]', group);
        if (!message) {
            if (error) error.remove();
            field.removeAttribute('aria-invalid');
            return;
        }

        if (!error) {
            error = createElement('span', 'inline-error');
            error.dataset.generatedError = 'true';
            error.id = `field-error-${Math.random().toString(36).slice(2, 9)}`;
            group.appendChild(error);
        }

        error.textContent = message;
        field.setAttribute('aria-invalid', 'true');

        const describedBy = field.getAttribute('aria-describedby');
        if (!describedBy || !describedBy.includes(error.id)) {
            field.setAttribute('aria-describedby', describedBy ? `${describedBy} ${error.id}` : error.id);
        }
    };

    const updateFieldState = (field) => {
        const group = field.closest('.form-group');
        if (!group) return;

        const hasValue = field.type === 'checkbox' || field.type === 'radio'
            ? field.checked
            : String(field.value || '').trim() !== '';
        group.classList.toggle('has-value', hasValue);

        if (field.classList.contains('is-invalid') && field.validity && field.validity.valid) {
            setFieldError(field, '');
        }
    };

    qsa('form').forEach((form) => {
        qsa('input, select, textarea', form).forEach(updateFieldState);

        form.addEventListener('input', (event) => {
            if (event.target.matches('input, select, textarea')) {
                updateFieldState(event.target);
            }
        });

        form.addEventListener('change', (event) => {
            if (event.target.matches('input, select, textarea')) {
                updateFieldState(event.target);
            }
        });

        form.addEventListener('invalid', (event) => {
            const field = event.target;
            if (!field.matches('input, select, textarea')) return;
            form.classList.add('was-validated');
            setFieldError(field, field.validationMessage || 'Revise este campo.');
        }, true);

        form.addEventListener('submit', (event) => {
            form.classList.add('was-validated');

            if (!form.checkValidity()) {
                event.preventDefault();
                const firstInvalid = qs('input:invalid, select:invalid, textarea:invalid', form);
                if (firstInvalid) firstInvalid.focus();
                return;
            }

            const submitter = event.submitter || qs('button[type="submit"], input[type="submit"]', form);
            if (!submitter || submitter.dataset.loading === 'true') return;
            submitter.dataset.loading = 'true';
            submitter.dataset.originalText = submitter.value || submitter.textContent || '';

            if (submitter.tagName === 'INPUT') {
                submitter.value = 'Procesando...';
            } else {
                submitter.textContent = 'Procesando...';
            }
        });
    });

    qsa('textarea').forEach((textarea) => {
        const resize = () => {
            textarea.style.height = 'auto';
            textarea.style.height = `${textarea.scrollHeight}px`;
        };

        textarea.addEventListener('input', resize);
        resize();
    });

    qsa('textarea[maxlength], input[maxlength]:not([type="hidden"])').forEach((field) => {
        const max = Number(field.getAttribute('maxlength'));
        if (!Number.isFinite(max) || max <= 0) return;

        const counter = createElement('span', 'field-counter');
        field.insertAdjacentElement('afterend', counter);

        const updateCounter = () => {
            const count = field.value.length;
            counter.textContent = `${count}/${max}`;
            counter.classList.toggle('is-near-limit', count >= Math.floor(max * 0.85) && count < max);
            counter.classList.toggle('is-at-limit', count >= max);
        };

        field.addEventListener('input', updateCounter);
        updateCounter();
    });

    const licenseCheck = qs('#es_licencia');
    const licenseFields = qsa('.license-field');
    const updateLicenseFields = () => {
        if (!licenseCheck) return;
        licenseFields.forEach((field) => {
            field.hidden = !licenseCheck.checked;
        });
    };

    if (licenseCheck) {
        licenseCheck.addEventListener('change', updateLicenseFields);
        updateLicenseFields();
    }

    const inventoryForm = qs('[data-inventory-form]');
    if (inventoryForm) {
        const typeSelect = qs('select[name="tipo_activo"]', inventoryForm);
        const imageInputs = qsa('[data-hardware-image]', inventoryForm);
        const isEditing = inventoryForm.dataset.editing === '1';
        const updateHardwareImageRequirements = () => {
            const requiresImages = !isEditing && typeSelect && typeSelect.value === 'HARDWARE';
            imageInputs.forEach((input) => {
                input.required = Boolean(requiresImages);
            });
        };

        if (typeSelect) {
            typeSelect.addEventListener('change', updateHardwareImageRequirements);
            updateHardwareImageRequirements();
        }
    }

    qsa('.table-wrap table').forEach((table) => {
        const headings = qsa('thead th', table).map((th) => th.textContent.trim());
        if (!headings.length) return;

        table.classList.add('responsive-table');
        qsa('tbody tr', table).forEach((row) => {
            qsa('td', row).forEach((cell, index) => {
                if (!cell.hasAttribute('data-label') && headings[index]) {
                    cell.setAttribute('data-label', headings[index]);
                }
            });
        });

        qsa('tbody tr', table).forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('a, button, input, select, textarea, label')) return;

                qsa('tbody tr.is-row-selected', table).forEach((selected) => {
                    selected.classList.remove('is-row-selected');
                });

                row.classList.add('is-row-selected');
                const wrap = table.closest('.table-wrap');
                if (wrap) wrap.classList.add('has-selected-row');
            });
        });
    });

    qsa('.table-wrap').forEach((wrap) => {
        const updateScrollState = () => {
            const maxScroll = wrap.scrollWidth - wrap.clientWidth;
            const scrollable = maxScroll > 1;
            wrap.classList.toggle('is-scrollable', scrollable);
            wrap.classList.toggle('can-scroll-left', scrollable && wrap.scrollLeft > 1);
            wrap.classList.toggle('can-scroll-right', scrollable && wrap.scrollLeft < maxScroll - 1);
        };

        wrap.addEventListener('scroll', updateScrollState, { passive: true });
        window.addEventListener('resize', updateScrollState);
        updateScrollState();
    });

    qsa('input[type="file"][accept*="image"], input[type="file"][accept*=".jpg"], input[type="file"][accept*=".jpeg"], input[type="file"][accept*=".png"], input[type="file"][accept*=".webp"]').forEach((input) => {
        const status = createElement('span', 'field-help', 'Formatos permitidos: JPG, PNG o WEBP. Máximo 2 MB.');
        input.insertAdjacentElement('afterend', status);

        const preview = createElement('div', 'file-preview');
        preview.hidden = true;
        const previewImage = document.createElement('img');
        previewImage.alt = 'Vista previa de imagen seleccionada';
        const previewText = createElement('span', 'small muted');
        preview.appendChild(previewImage);
        preview.appendChild(previewText);
        status.insertAdjacentElement('afterend', preview);

        let objectUrl = '';

        input.addEventListener('change', () => {
            const file = input.files && input.files[0] ? input.files[0] : null;
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = '';
            }

            if (!file) {
                status.textContent = 'Formatos permitidos: JPG, PNG o WEBP. Máximo 2 MB.';
                preview.hidden = true;
                previewImage.removeAttribute('src');
                previewText.textContent = '';
                return;
            }

            status.textContent = `Archivo seleccionado: ${file.name}`;
            previewText.textContent = `${file.name} · ${Math.ceil(file.size / 1024)} KB`;

            if (file.type && file.type.startsWith('image/')) {
                objectUrl = URL.createObjectURL(file);
                previewImage.src = objectUrl;
                preview.hidden = false;
            } else {
                preview.hidden = true;
            }
        });
    });

    const galleryImages = qsa('.gallery img, .image-large');
    const modal = createElement('div', 'gallery-modal');
    modal.hidden = true;
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-label', 'Vista ampliada de imagen');

    const modalContent = createElement('div', 'gallery-modal-content');
    const modalButton = createElement('button', 'gallery-modal-close', 'Cerrar');
    modalButton.type = 'button';
    modalButton.setAttribute('aria-label', 'Cerrar imagen ampliada');
    const modalImage = document.createElement('img');
    modalImage.alt = '';
    const modalCaption = createElement('p', 'gallery-modal-caption');
    const modalActions = createElement('div', 'confirm-actions');
    const previousImage = createElement('button', 'btn btn-light btn-small', 'Anterior');
    const nextImage = createElement('button', 'btn btn-light btn-small', 'Siguiente');
    previousImage.type = 'button';
    nextImage.type = 'button';

    modalActions.appendChild(previousImage);
    modalActions.appendChild(nextImage);
    modalContent.appendChild(modalButton);
    modalContent.appendChild(modalImage);
    modalContent.appendChild(modalCaption);
    modalContent.appendChild(modalActions);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);

    let activeImageIndex = -1;

    const renderGalleryImage = (index) => {
        if (!galleryImages.length) return;
        activeImageIndex = (index + galleryImages.length) % galleryImages.length;
        const image = galleryImages[activeImageIndex];
        const source = image.getAttribute('src');
        if (!source) return;

        modalImage.src = source;
        modalImage.alt = image.alt || 'Imagen del activo';
        modalCaption.textContent = image.alt || 'Imagen del activo';
        previousImage.hidden = galleryImages.length < 2;
        nextImage.hidden = galleryImages.length < 2;
    };

    const closeModal = () => {
        modal.hidden = true;
        modalImage.removeAttribute('src');
        updateModalState();
        if (activeImageIndex >= 0 && galleryImages[activeImageIndex]) {
            galleryImages[activeImageIndex].focus();
        }
        activeImageIndex = -1;
    };

    galleryImages.forEach((image, index) => {
        image.loading = image.loading || 'lazy';
        image.tabIndex = image.tabIndex >= 0 ? image.tabIndex : 0;
        image.setAttribute('role', 'button');
        image.setAttribute('aria-label', image.alt ? `Ampliar ${image.alt}` : 'Ampliar imagen');

        const openImage = () => {
            renderGalleryImage(index);
            modal.hidden = false;
            updateModalState();
            modalButton.focus();
        };

        image.addEventListener('click', openImage);
        image.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openImage();
            }
        });
    });

    modalButton.addEventListener('click', closeModal);
    previousImage.addEventListener('click', () => renderGalleryImage(activeImageIndex - 1));
    nextImage.addEventListener('click', () => renderGalleryImage(activeImageIndex + 1));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (!confirmModal.hidden && event.key === 'Escape') {
            closeConfirm();
        }

        if (modal.hidden) return;

        if (event.key === 'Escape') closeModal();
        if (event.key === 'ArrowLeft') renderGalleryImage(activeImageIndex - 1);
        if (event.key === 'ArrowRight') renderGalleryImage(activeImageIndex + 1);
    });

    const backToTop = createElement('button', 'back-to-top', '↑');
    backToTop.type = 'button';
    backToTop.setAttribute('aria-label', 'Volver arriba');
    document.body.appendChild(backToTop);

    const updateBackToTop = () => {
        backToTop.classList.toggle('is-visible', window.scrollY > 520);
    };

    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('scroll', updateBackToTop, { passive: true });
    updateBackToTop();
});

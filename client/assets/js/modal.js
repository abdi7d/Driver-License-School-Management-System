
const Modal = {
    open: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            modal.classList.add('open');
            // Also show any .modal child elements
            const modalChild = modal.querySelector('.modal');
            if (modalChild) {
                modalChild.style.display = 'flex';
                modalChild.classList.add('active');
            }
            document.body.style.overflow = 'hidden';
        }
    },

    close: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            modal.classList.remove('open');
            // Also hide any .modal child elements
            const modalChild = modal.querySelector('.modal');
            if (modalChild) {
                modalChild.style.display = 'none';
                modalChild.classList.remove('active');
            }
            document.body.style.overflow = '';
        }
    },

    closeAll: function() {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            modal.classList.remove('open');
        });
        document.body.style.overflow = '';
    },

    confirm: function(options) {
        const {
            title = 'Confirm',
            message = 'Are you sure?',
            icon = '⚠️',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            onConfirm = () => {},
            onCancel = () => {},
            type = 'warning'
        } = options;

        const modalId = 'confirmModal_' + Date.now();
        const confirmBtnColor = type === 'error' ? '#dc2626' : '#1E40AF';

        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = `modal-overlay modal-${type}`;
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="text-align:center;">
                        <div style="font-size:48px; margin-bottom:1rem;">${icon}</div>
                        <p style="font-size:16px; font-weight:600; color:#1e293b; margin:0 0 8px 0;">${title}</p>
                        <p style="font-size:14px; color:#64748b; margin:0;">${message}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary cancel-btn">${cancelText}</button>
                    <button class="btn-primary confirm-btn" style="background:${confirmBtnColor};">${confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        const closeBtn = modal.querySelector('.modal-close');
        const cancelBtn = modal.querySelector('.cancel-btn');
        const confirmBtn = modal.querySelector('.confirm-btn');

        const closeModal = () => {
            this.close(modalId);
            setTimeout(() => modal.remove(), 300);
        };

        closeBtn.onclick = closeModal;
        cancelBtn.onclick = () => {
            onCancel();
            closeModal();
        };
        confirmBtn.onclick = async () => {
            await onConfirm();
            closeModal();
        };

        this.open(modalId);
    },

    alert: function(options) {
        const {
            title = 'Alert',
            message = 'Message',
            icon = 'ℹ️',
            type = 'info',
            onClose = () => {}
        } = options;

        const modalId = 'alertModal_' + Date.now();

        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = `modal-overlay modal-${type}`;
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="display:flex; gap:16px;">
                        <div style="font-size:32px; flex-shrink:0;">${icon}</div>
                        <div style="flex:1;">
                            <p style="font-size:14px; color:#64748b; margin:0;">${message}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-primary ok-btn">OK</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        const closeBtn = modal.querySelector('.modal-close');
        const okBtn = modal.querySelector('.ok-btn');

        const closeModal = () => {
            this.close(modalId);
            setTimeout(() => modal.remove(), 300);
        };

        closeBtn.onclick = closeModal;
        okBtn.onclick = () => {
            onClose();
            closeModal();
        };

        this.open(modalId);
    },

    form: function(options) {
        const {
            title = 'Form',
            fields = [],
            submitText = 'Submit',
            onSubmit = () => {}
        } = options;

        const modalId = 'formModal_' + Date.now();
        const formId = 'form_' + Date.now();

        let fieldsHTML = fields.map(field => `
            <div class="modal-form-group">
                <label>${field.label}</label>
                <input type="${field.type || 'text'}" 
                       name="${field.name}" 
                       placeholder="${field.placeholder || ''}"
                       ${field.required ? 'required' : ''}
                       value="${field.value || ''}">
            </div>
        `).join('');

        const modalHTML = `
            <div id="${modalId}" class="modal-overlay">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">${title}</h3>
                        <button class="modal-close" onclick="Modal.close('${modalId}')">&times;</button>
                    </div>
                    <form id="${formId}">
                        <div class="modal-body">
                            ${fieldsHTML}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary" onclick="Modal.close('${modalId}')">Cancel</button>
                            <button type="submit" class="btn-primary">${submitText}</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        const container = document.createElement('div');
        container.innerHTML = modalHTML;
        document.body.appendChild(container.firstElementChild);

        const form = document.getElementById(formId);
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            onSubmit(data);
            Modal.close(modalId);
        });

        this.open(modalId);

        const modal = document.getElementById(modalId);
        const observer = new MutationObserver(() => {
            if (modal.style.display === 'none') {
                modal.remove();
                observer.disconnect();
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    }
};

window.Modal = Modal;


document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        Modal.closeAll();
    }
});

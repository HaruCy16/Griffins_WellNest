<!-- Modal Popup Styles and HTML -->
<style>
    /* Modal Overlay */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease;
    }

    .modal-overlay.show {
        display: flex;
    }

    /* Modal Container */
    .modal-popup {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 450px;
        width: 90%;
        padding: 32px;
        animation: slideUp 0.3s ease;
        text-align: center;
    }

    /* Modal Header */
    .modal-header {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .modal-title {
        font-size: 24px;
        font-weight: bold;
        color: #8B6F47;
        margin-bottom: 12px;
    }

    .modal-message {
        color: #666;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 24px;
        white-space: pre-wrap;
    }

    /* Modal Actions */
    .modal-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .modal-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        min-width: 120px;
    }

    .modal-btn-primary {
        background: linear-gradient(135deg, #8B6F47, #D4A574);
        color: white;
    }

    .modal-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(139, 111, 71, 0.3);
    }

    .modal-btn-secondary {
        background: #f0f0f0;
        color: #333;
        border: 2px solid #ddd;
    }

    .modal-btn-secondary:hover {
        background: #e8e8e8;
        border-color: #bbb;
    }

    .modal-btn-danger {
        background: #ef4444;
        color: white;
    }

    .modal-btn-danger:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(239, 68, 68, 0.3);
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Single button layout */
    .modal-actions.single-action .modal-btn {
        width: 100%;
    }

    /* Small text for additional info */
    .modal-subtext {
        font-size: 12px;
        color: #999;
        margin-top: 16px;
    }
</style>

<!-- Modal HTML Structure -->
<div id="modalOverlay" class="modal-overlay">
    <div class="modal-popup">
        <div class="modal-header" id="modalIcon"></div>
        <h2 class="modal-title" id="modalTitle"></h2>
        <p class="modal-message" id="modalMessage"></p>
        <div class="modal-actions" id="modalActions"></div>
        <p class="modal-subtext" id="modalSubtext"></p>
    </div>
</div>

<script>
    /**
     * Show a styled alert modal
     * @param {string} message Message to display
     * @param {string} title Optional title (default: "Notice")
     * @param {string} icon Optional emoji icon (default: "ℹ️")
     * @param {function} callback Optional callback when OK is clicked
     */
    function showModal(message, title = 'Notice', icon = 'ℹ️', callback = null) {
        const overlay = document.getElementById('modalOverlay');
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const actionsEl = document.getElementById('modalActions');
        const iconEl = document.getElementById('modalIcon');
        const subtextEl = document.getElementById('modalSubtext');

        // Set content
        titleEl.textContent = title;
        messageEl.textContent = message;
        iconEl.textContent = icon;
        subtextEl.textContent = '';

        // Create OK button
        actionsEl.innerHTML = '';
        actionsEl.className = 'modal-actions single-action';
        
        const okBtn = document.createElement('button');
        okBtn.className = 'modal-btn modal-btn-primary';
        okBtn.textContent = 'OK';
        okBtn.onclick = function() {
            closeModal();
            if (callback) callback();
        };
        
        actionsEl.appendChild(okBtn);

        // Show modal
        overlay.classList.add('show');

        // Close on overlay click
        overlay.onclick = function(e) {
            if (e.target === overlay) closeModal();
        };
    }

    /**
     * Show a styled confirm modal
     * @param {string} message Message to display
     * @param {function} onConfirm Callback if user clicks Yes
     * @param {function} onCancel Optional callback if user clicks No
     * @param {string} title Optional title (default: "Confirmation")
     * @param {string} icon Optional emoji icon (default: "❓")
     */
    function showConfirm(message, onConfirm, onCancel = null, title = 'Confirmation', icon = '❓') {
        const overlay = document.getElementById('modalOverlay');
        const titleEl = document.getElementById('modalTitle');
        const messageEl = document.getElementById('modalMessage');
        const actionsEl = document.getElementById('modalActions');
        const iconEl = document.getElementById('modalIcon');
        const subtextEl = document.getElementById('modalSubtext');

        // Set content
        titleEl.textContent = title;
        messageEl.textContent = message;
        iconEl.textContent = icon;
        subtextEl.textContent = '';

        // Create buttons
        actionsEl.innerHTML = '';
        actionsEl.className = 'modal-actions';

        const yesBtn = document.createElement('button');
        yesBtn.className = 'modal-btn modal-btn-primary';
        yesBtn.textContent = 'Yes';
        yesBtn.onclick = function() {
            closeModal();
            if (onConfirm) onConfirm();
        };

        const noBtn = document.createElement('button');
        noBtn.className = 'modal-btn modal-btn-secondary';
        noBtn.textContent = 'No';
        noBtn.onclick = function() {
            closeModal();
            if (onCancel) onCancel();
        };

        actionsEl.appendChild(yesBtn);
        actionsEl.appendChild(noBtn);

        // Show modal
        overlay.classList.add('show');

        // Close on overlay click
        overlay.onclick = function(e) {
            if (e.target === overlay) closeModal();
        };
    }

    /**
     * Show a success modal
     * @param {string} message Message to display
     * @param {function} callback Optional callback
     */
    function showSuccess(message, callback = null) {
        showModal(message, 'Success', '✓', callback);
    }

    /**
     * Show an error modal
     * @param {string} message Message to display
     * @param {function} callback Optional callback
     */
    function showError(message, callback = null) {
        showModal(message, 'Error', '✕', callback);
    }

    /**
     * Show a warning modal
     * @param {string} message Message to display
     * @param {function} callback Optional callback
     */
    function showWarning(message, callback = null) {
        showModal(message, 'Warning', '⚠️', callback);
    }

    /**
     * Close the modal
     */
    function closeModal() {
        const overlay = document.getElementById('modalOverlay');
        overlay.classList.remove('show');
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>

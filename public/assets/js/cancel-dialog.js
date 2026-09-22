(function () {
    const forms = Array.from(document.querySelectorAll('[data-cancel-form]'));
    const dialog = document.querySelector('#cancel-dialog');
    const opener = document.querySelector('[data-open-cancel]');

    function bind(form) {
        const submit = form.querySelector('[data-confirm-cancel]');
        const modes = Array.from(form.querySelectorAll('input[name="mode"]'));
        const reason = form.querySelector('#motif');

        function refresh() {
            const chosen = modes.some(function (mode) {
                return mode.checked;
            });
            submit.disabled = !(chosen && reason.value.trim().length >= 10);
        }

        modes.forEach(function (mode) {
            mode.addEventListener('change', refresh);
        });
        reason.addEventListener('input', refresh);
        refresh();
    }

    forms.forEach(bind);

    document.querySelectorAll('[data-close-dialog]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (dialog && typeof dialog.close === 'function') {
                dialog.close();
            } else {
                window.history.back();
            }
        });
    });

    if (dialog && opener && typeof dialog.showModal === 'function') {
        opener.addEventListener('click', function (event) {
            event.preventDefault();
            dialog.showModal();
            const first = dialog.querySelector('input[name="mode"]');
            if (first) {
                first.focus();
            }
        });

        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    }
})();

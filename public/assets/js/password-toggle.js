(function () {
    const openIcon = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
    const closedIcon = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 3l18 18"/><path d="M10.6 6.1A9.9 9.9 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4"/><path d="M6.6 6.7C3.9 8.4 2 12 2 12s3.6 7 10 7c1.5 0 2.9-.4 4.1-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        if (!input) {
            return;
        }

        button.addEventListener('click', function () {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.setAttribute('aria-pressed', String(!visible));
            button.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
            button.innerHTML = visible ? openIcon : closedIcon;
        });
    });
})();

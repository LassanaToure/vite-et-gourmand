(function () {
    const desktop = window.matchMedia('(min-width: 900px)');

    function setup(toggle, panel) {
        if (!toggle || !panel) {
            return null;
        }

        function set(open) {
            toggle.setAttribute('aria-expanded', String(open));
            panel.classList.toggle('is-open', open);
        }

        toggle.addEventListener('click', function () {
            set(toggle.getAttribute('aria-expanded') !== 'true');
        });

        return { set: set, toggle: toggle, panel: panel };
    }

    const nav = setup(
        document.querySelector('[data-nav-toggle]'),
        document.querySelector('[data-nav]')
    );
    const account = setup(
        document.querySelector('[data-account-toggle]'),
        document.querySelector('#account-menu')
    );
    const widgets = [nav, account].filter(Boolean);

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }
        widgets.forEach(function (widget) {
            if (widget.toggle.getAttribute('aria-expanded') === 'true') {
                widget.set(false);
                widget.toggle.focus();
            }
        });
    });

    document.addEventListener('click', function (event) {
        if (account && !account.toggle.parentElement.contains(event.target)) {
            account.set(false);
        }
    });

    desktop.addEventListener('change', function () {
        if (nav) {
            nav.set(false);
        }
    });
})();

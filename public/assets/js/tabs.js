(function () {
    const tablist = document.querySelector('[data-tabs]');
    const panel = document.querySelector('[data-panel]');
    if (!tablist || !panel) {
        return;
    }

    const tabs = Array.from(tablist.querySelectorAll('[role="tab"]'));
    const items = Array.from(panel.querySelectorAll('[data-tab-item]'));
    const empty = panel.querySelector('[data-tab-empty]');

    tablist.hidden = false;

    function select(tab, focus) {
        const filter = tab.dataset.filter;

        tabs.forEach(function (other) {
            const active = other === tab;
            other.setAttribute('aria-selected', String(active));
            other.tabIndex = active ? 0 : -1;
        });

        let visible = 0;
        items.forEach(function (item) {
            const show = filter === 'all' || item.dataset.group === filter;
            item.hidden = !show;
            if (show) {
                visible += 1;
            }
        });

        if (empty) {
            empty.hidden = visible !== 0;
        }
        panel.setAttribute('aria-labelledby', tab.id);

        if (focus) {
            tab.focus();
        }
    }

    tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () {
            select(tab, false);
        });

        tab.addEventListener('keydown', function (event) {
            const last = tabs.length - 1;
            let target = null;

            if (event.key === 'ArrowRight') {
                target = tabs[index === last ? 0 : index + 1];
            } else if (event.key === 'ArrowLeft') {
                target = tabs[index === 0 ? last : index - 1];
            } else if (event.key === 'Home') {
                target = tabs[0];
            } else if (event.key === 'End') {
                target = tabs[last];
            }

            if (target) {
                event.preventDefault();
                select(target, true);
            }
        });
    });

    select(tabs.find(function (tab) {
        return tab.getAttribute('aria-selected') === 'true';
    }) || tabs[0], false);
})();

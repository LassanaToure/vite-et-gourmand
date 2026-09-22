(function () {
    const table = document.querySelector('[data-orders-table]');
    if (!table) {
        return;
    }

    const toolbar = document.querySelector('[data-toolbar]');
    const search = document.querySelector('[data-client-search]');
    const counter = document.querySelector('[data-result-count]');
    const empty = document.querySelector('[data-empty]');
    const chips = Array.from(document.querySelectorAll('[data-status-filter]'));
    const body = table.tBodies[0];
    const rows = Array.from(body.querySelectorAll('[data-order-row]'));
    const numeric = { id: true, people: true, total: true, 'status': true };
    const state = { status: 'all', term: '', key: 'id', direction: 'desc' };

    function normalize(text) {
        return text.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
    }

    function apply() {
        const term = normalize(state.term);
        let visible = 0;

        rows.forEach(function (row) {
            const matchesStatus = state.status === 'all' || row.dataset.status === state.status;
            const matchesTerm = term === '' || normalize(row.dataset.search).indexOf(term) !== -1;
            row.hidden = !(matchesStatus && matchesTerm);
            if (!row.hidden) {
                visible += 1;
            }
        });

        counter.textContent = visible + (visible > 1 ? ' commandes affichées' : ' commande affichée');
        empty.hidden = visible !== 0;
    }

    function sortRows() {
        const attribute = state.key === 'status' ? 'statusOrder' : state.key;
        const factor = state.direction === 'asc' ? 1 : -1;

        rows.sort(function (a, b) {
            const left = a.dataset[attribute];
            const right = b.dataset[attribute];
            if (numeric[state.key]) {
                return (Number(left) - Number(right)) * factor;
            }
            return left.localeCompare(right, 'fr') * factor;
        });
        rows.forEach(function (row) {
            body.appendChild(row);
        });

        table.querySelectorAll('th[aria-sort]').forEach(function (header) {
            const button = header.querySelector('[data-sort-key]');
            const active = button && button.dataset.sortKey === state.key;
            header.setAttribute('aria-sort', active ? (state.direction === 'asc' ? 'ascending' : 'descending') : 'none');
        });
    }

    toolbar.hidden = false;

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            state.status = chip.dataset.statusFilter;
            chips.forEach(function (other) {
                other.setAttribute('aria-pressed', String(other === chip));
            });
            apply();
        });
    });

    search.addEventListener('input', function () {
        state.term = search.value;
        apply();
    });

    table.querySelectorAll('[data-sort-key]').forEach(function (button) {
        button.addEventListener('click', function () {
            const key = button.dataset.sortKey;
            state.direction = state.key === key && state.direction === 'asc' ? 'desc' : 'asc';
            state.key = key;
            sortRows();
        });
    });

    sortRows();
    apply();
})();

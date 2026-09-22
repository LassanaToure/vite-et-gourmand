(function () {
    const form = document.querySelector('[data-order]');
    if (!form) {
        return;
    }

    const rules = JSON.parse(form.dataset.rules);
    const endpoint = form.dataset.endpoint;
    const menuSelect = form.querySelector('[data-menu]');
    const people = form.querySelector('#nombre-personne');
    const address = form.querySelector('#adresse');
    const postcode = form.querySelector('#code-postal');
    const city = form.querySelector('#ville');
    const date = form.querySelector('#date-prestation');
    const status = form.querySelector('[data-delivery-status]');
    const summary = form.querySelector('[data-summary]');
    const conditions = form.querySelector('[data-conditions]');
    const submit = form.querySelector('[data-submit]');
    const minus = form.querySelector('[data-step="-1"]');
    const euro = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });

    let delivery = null;
    let request = null;
    let timer = null;

    function money(cents) {
        return euro.format(cents / 100);
    }

    function field(root, name) {
        return root.querySelector('[data-field="' + name + '"]');
    }

    function currentMenu() {
        const option = menuSelect.selectedOptions[0];
        if (!option || option.value === '') {
            return null;
        }
        return {
            price: Number(option.dataset.price),
            min: Number(option.dataset.min),
            delay: Number(option.dataset.delay),
            conditions: option.dataset.conditions
        };
    }

    function count(menu) {
        const value = parseInt(people.value, 10);
        return Math.max(menu ? menu.min : 1, Number.isNaN(value) ? 0 : value);
    }

    function isoDate(offsetDays) {
        const day = new Date();
        day.setDate(day.getDate() + offsetDays);
        return [day.getFullYear(), String(day.getMonth() + 1).padStart(2, '0'), String(day.getDate()).padStart(2, '0')].join('-');
    }

    function applyMenu() {
        const menu = currentMenu();
        conditions.hidden = menu === null;
        if (menu === null) {
            people.min = '1';
            return;
        }

        people.min = String(menu.min);
        if (parseInt(people.value, 10) < menu.min || people.value === '') {
            people.value = String(menu.min);
        }
        field(form, 'minimum').textContent = menu.min;
        field(form, 'discount-from').textContent = menu.min + rules.discountExtraPeople;
        field(conditions, 'conditions').textContent = menu.conditions;
        date.min = isoDate(menu.delay);
        date.max = isoDate(rules.maxDays);
    }

    function render() {
        const menu = currentMenu();
        minus.disabled = menu !== null && count(menu) <= menu.min;

        if (menu === null) {
            return;
        }

        const persons = count(menu);
        const base = menu.price * persons;
        const eligible = persons >= menu.min + rules.discountExtraPeople;
        const discount = eligible ? Math.floor((base * rules.discountPercent + 50) / 100) : 0;
        const discountRow = summary.querySelector('[data-row="discount"]');

        field(summary, 'menu-label').textContent = 'Menu : ' + money(menu.price) + ' × ' + persons + ' personnes';
        field(summary, 'menu-amount').textContent = money(base);
        discountRow.hidden = discount === 0;
        field(summary, 'discount-amount').textContent = discount === 0 ? '' : '−' + money(discount);

        let detail = 'Renseignez l\'adresse de livraison pour calculer les frais.';
        let amount = '—';
        let total = '—';

        if (delivery !== null) {
            const menuTotal = base - discount;
            if (delivery.source === 'bordeaux') {
                detail = 'Livraison offerte à Bordeaux.';
                amount = 'Offerte';
            } else {
                detail = money(rules.deliveryFeeCents) + ' + ' + delivery.km.toFixed(1).replace('.', ',') + ' km × ' + money(rules.perKmCents);
                amount = money(delivery.fee);
            }
            total = money(menuTotal + delivery.fee);
        }

        field(summary, 'delivery-detail').textContent = detail;
        field(summary, 'delivery-amount').textContent = amount;
        field(summary, 'total-amount').textContent = total;
    }

    function setStatus(text, state) {
        status.textContent = text;
        if (state) {
            status.dataset.state = state;
        } else {
            delete status.dataset.state;
        }
    }

    function addressComplete() {
        return address.value.trim().length >= 5 && /^\d{5}$/.test(postcode.value.trim()) && city.value.trim().length >= 2;
    }

    function loadQuote() {
        if (request) {
            request.abort();
        }
        if (!addressComplete()) {
            delivery = null;
            setStatus('', null);
            render();
            return;
        }

        request = new AbortController();
        const current = request;
        const query = new URLSearchParams({
            adresse: address.value.trim(),
            code_postal: postcode.value.trim(),
            ville: city.value.trim()
        });

        setStatus('Calcul des frais de livraison en cours…', null);

        fetch(endpoint + '?' + query.toString(), {
            signal: current.signal,
            credentials: 'same-origin',
            headers: { Accept: 'application/json' }
        })
            .then(function (response) {
                const type = response.headers.get('Content-Type') || '';
                if (type.indexOf('application/json') === -1) {
                    throw new Error('non-json');
                }
                return response.json();
            })
            .then(function (data) {
                if (data.ok) {
                    delivery = { km: data.km, source: data.source, fee: data.fee_cents };
                    setStatus(data.source === 'bordeaux' ? 'Livraison offerte à Bordeaux.' : 'Distance estimée depuis Bordeaux : ' + data.km.toFixed(1).replace('.', ',') + ' km.', null);
                } else {
                    delivery = null;
                    setStatus(data.message, 'error');
                }
                render();
            })
            .catch(function (failure) {
                if (failure.name === 'AbortError') {
                    return;
                }
                delivery = null;
                setStatus('Impossible de calculer les frais de livraison pour le moment. Vous pouvez tout de même valider : le calcul sera refait par nos serveurs.', 'error');
                render();
            });
    }

    function scheduleQuote() {
        delivery = null;
        render();
        window.clearTimeout(timer);
        timer = window.setTimeout(loadQuote, 600);
    }

    function step(direction) {
        const menu = currentMenu();
        people.value = String(Math.max(menu ? menu.min : 1, count(menu) + direction));
        render();
    }

    form.querySelectorAll('[data-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            step(Number(button.dataset.step));
        });
    });

    menuSelect.addEventListener('change', function () {
        applyMenu();
        render();
    });

    people.addEventListener('input', render);
    people.addEventListener('change', function () {
        const menu = currentMenu();
        people.value = String(count(menu));
        render();
    });

    [address, postcode, city].forEach(function (input) {
        input.addEventListener('input', scheduleQuote);
    });

    form.addEventListener('submit', function () {
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
    });

    window.addEventListener('pageshow', function () {
        submit.disabled = false;
        submit.removeAttribute('aria-busy');
    });

    applyMenu();
    render();
    if (addressComplete()) {
        loadQuote();
    }
})();

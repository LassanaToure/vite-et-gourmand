(function () {
    const form = document.querySelector('[data-filters]');
    const list = document.querySelector('[data-menu-list]');
    const template = document.querySelector('[data-menu-template]');
    const count = document.querySelector('[data-count]');
    const empty = document.querySelector('[data-empty]');
    const error = document.querySelector('[data-error]');

    if (!form || !list || !template) {
        return;
    }

    const endpoint = form.dataset.endpoint;
    let request = null;
    let timer = null;

    function params() {
        const query = new URLSearchParams();
        new FormData(form).forEach(function (value, key) {
            const clean = String(value).trim();
            if (clean !== '') {
                query.append(key, clean);
            }
        });
        return query;
    }

    function field(node, name) {
        return node.querySelector('[data-field="' + name + '"]');
    }

    function build(menu) {
        const item = template.content.firstElementChild.cloneNode(true);
        const image = field(item, 'image');

        field(item, 'titre').textContent = menu.titre;
        field(item, 'description').textContent = menu.description;
        field(item, 'theme').textContent = menu.theme;
        field(item, 'regime').textContent = menu.regime;
        field(item, 'prix').textContent = menu.prix_affiche;
        field(item, 'minimum').textContent = menu.nombre_personne_minimum;
        field(item, 'link').href = menu.url;

        if (menu.image_url) {
            image.src = menu.image_url;
            image.hidden = false;
        } else {
            image.hidden = true;
        }

        return item;
    }

    function label(total) {
        if (total === 0) {
            return 'Aucun menu';
        }
        return total + (total > 1 ? ' menus' : ' menu');
    }

    function render(menus) {
        list.replaceChildren.apply(list, menus.map(build));
        count.textContent = label(menus.length);
        empty.hidden = menus.length !== 0;
        error.hidden = true;
    }

    function load() {
        const query = params();

        if (request) {
            request.abort();
        }
        request = new AbortController();
        const current = request;

        list.setAttribute('aria-busy', 'true');

        fetch(endpoint + '?' + query.toString(), {
            signal: current.signal,
            headers: { Accept: 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('http ' + response.status);
                }
                return response.json();
            })
            .then(function (data) {
                render(data.menus);
                const search = query.toString();
                history.replaceState(null, '', window.location.pathname + (search ? '?' + search : ''));
            })
            .catch(function (failure) {
                if (failure.name !== 'AbortError') {
                    error.hidden = false;
                }
            })
            .finally(function () {
                if (request === current) {
                    list.removeAttribute('aria-busy');
                }
            });
    }

    function schedule() {
        window.clearTimeout(timer);
        timer = window.setTimeout(load, 250);
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        window.clearTimeout(timer);
        load();
    });

    form.addEventListener('input', schedule);
    form.addEventListener('change', schedule);

    form.addEventListener('reset', function (event) {
        event.preventDefault();
        form.querySelectorAll('input').forEach(function (input) {
            input.value = '';
        });
        form.querySelectorAll('select').forEach(function (select) {
            select.selectedIndex = 0;
        });
        window.clearTimeout(timer);
        load();
    });
})();

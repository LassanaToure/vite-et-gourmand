(function () {
    const gallery = document.querySelector('[data-gallery]');
    if (!gallery) {
        return;
    }

    const main = gallery.querySelector('[data-gallery-main]');
    const thumbs = Array.from(gallery.querySelectorAll('.gallery__thumb'));

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            main.src = thumb.dataset.src;
            main.alt = thumb.dataset.alt;
            thumbs.forEach(function (other) {
                other.setAttribute('aria-pressed', String(other === thumb));
            });
        });
    });
})();

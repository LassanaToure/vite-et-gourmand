<?php $site = site(); ?>
<footer class="site-footer">
    <div class="container">
        <div class="site-footer__grid">
            <div>
                <p class="site-footer__logo"><?= e($site['name']) ?></p>
                <p class="site-footer__muted"><?= e($site['tagline']) ?><br><?= e($site['baseline']) ?></p>
            </div>
            <section aria-labelledby="footer-hours">
                <h2 class="footer-title" id="footer-hours">Horaires</h2>
                <dl class="hours">
                    <?php foreach (group_hours(opening_hours()) as $group): ?>
                        <div class="hours__row">
                            <dt><?= e($group['name']) ?></dt>
                            <dd><?= e($group['label']) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </section>
            <nav aria-labelledby="footer-info">
                <h2 class="footer-title" id="footer-info">Informations</h2>
                <ul class="footer-links">
                    <li><a href="<?= e(url('/mentions-legales')) ?>">Mentions légales</a></li>
                    <li><a href="<?= e(url('/cgv')) ?>">CGV</a></li>
                    <li><a href="<?= e(url('/contact')) ?>">Contact</a></li>
                </ul>
            </nav>
            <section aria-labelledby="footer-find">
                <h2 class="footer-title" id="footer-find">Nous trouver</h2>
                <address class="footer-address">
                    <?php foreach ($site['address'] as $line): ?>
                        <span><?= e($line) ?></span>
                    <?php endforeach; ?>
                    <a href="tel:<?= e($site['phone_href']) ?>"><?= e($site['phone']) ?></a>
                    <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>
                </address>
            </section>
        </div>
        <p class="site-footer__legal">© 2026 <?= e($site['name']) ?> — Tous droits réservés</p>
    </div>
</footer>

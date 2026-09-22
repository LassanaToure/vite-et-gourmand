<?php $site = site(); ?>
<article class="section legal" aria-labelledby="legal-title">
    <div class="container container--narrow prose">
        <h1 id="legal-title">Mentions légales</h1>

        <section>
            <h2>Éditeur du site</h2>
            <p><strong>Vite &amp; Gourmand SARL</strong>, société à responsabilité limitée au capital de 20 000 €, immatriculée au RCS de Bordeaux.</p>
            <p>Siège social : <?= e(implode(', ', $site['address'])) ?>.<br>
            Téléphone : <a href="tel:<?= e($site['phone_href']) ?>"><?= e($site['phone']) ?></a><br>
            Email : <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></p>
            <p>Directeurs de la publication : Julie et José, cogérants.</p>
        </section>

        <section>
            <h2>Hébergement</h2>
            <p>Le site est hébergé par un prestataire d'hébergement établi dans l'Union européenne. Les coordonnées de l'hébergeur seront précisées lors de la mise en production.</p>
        </section>

        <section>
            <h2>Propriété intellectuelle</h2>
            <p>L'ensemble des contenus du site (textes, photographies, logotypes, recettes) est la propriété de Vite &amp; Gourmand ou de ses partenaires. Toute reproduction sans autorisation écrite préalable est interdite.</p>
        </section>

        <section>
            <h2>Données personnelles</h2>
            <p>Les informations recueillies lors de la création de votre compte, d'une commande ou d'un message sont nécessaires au traitement de vos demandes. Elles ne sont ni cédées ni vendues à des tiers.</p>
            <p>Conformément au Règlement général sur la protection des données, vous disposez d'un droit d'accès, de rectification, d'effacement et d'opposition en écrivant à <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>.</p>
        </section>

        <section>
            <h2>Cookies</h2>
            <p>Ce site n'utilise que des cookies strictement nécessaires à son fonctionnement, comme le maintien de votre session de connexion. Aucun cookie publicitaire n'est déposé.</p>
        </section>

        <p class="prose__note">Mentions légales mises à jour le 1er janvier 2026.</p>
    </div>
</article>

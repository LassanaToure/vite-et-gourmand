<?php
$skills = [
    ['icon' => 'leaf', 'title' => 'Produits frais & locaux', 'text' => "Approvisionnement quotidien au marché des Capucins. Nous travaillons en direct avec des producteurs girondins sélectionnés pour leur engagement."],
    ['icon' => 'star', 'title' => "25 ans d'expérience", 'text' => "Un quart de siècle à sublimer vos tables de fête. Des centaines de mariages, Noëls et réceptions qui nous ont formés à chaque défi."],
    ['icon' => 'sparkle', 'title' => 'Menus sur mesure', 'text' => "Chaque événement est unique. Nous adaptons nos menus à vos goûts, vos contraintes alimentaires et vos envies les plus précises."],
];
?>
<section class="hero" aria-labelledby="hero-title">
    <img class="hero__image" src="<?= e(asset('img/hero.jpg')) ?>" alt="" width="1600" height="1067" fetchpriority="high">
    <div class="container hero__content">
        <p class="eyebrow eyebrow--light">Traiteur événementiel · Bordeaux</p>
        <h1 id="hero-title">L'art de recevoir depuis 25 ans</h1>
        <p class="hero__text">Menus raffinés pour mariages, Noël, Pâques et réceptions. Produits frais, savoir-faire artisanal.</p>
        <a class="btn" href="<?= e(url('/menus')) ?>">Découvrir nos menus <span aria-hidden="true">→</span></a>
    </div>
    <a class="hero__scroll" href="#maison"><span lang="en">Scroll</span></a>
</section>

<section class="section about" id="maison" aria-labelledby="about-title">
    <div class="container about__grid">
        <img class="about__image" src="<?= e(asset('img/maison.jpg')) ?>" alt="Un couple prépare un plat ensemble dans une cuisine lumineuse" width="1325" height="1102" loading="lazy">
        <div class="about__text">
            <p class="eyebrow">Notre maison</p>
            <h2 id="about-title">25 ans de passion bordelaise</h2>
            <p>Fondée en 2001 par Julie et José, Vite &amp; Gourmand est née d'une conviction simple : la cuisine de fête mérite le même soin qu'un grand restaurant. Depuis lors, nous mettons notre savoir-faire artisanal au service de vos moments les plus précieux.</p>
            <p>Chaque menu est élaboré avec des produits frais sélectionnés au marché des Capucins, préparé le matin même et livré dans nos contenants isothermes. Rien n'est préfabriqué, tout est fait maison avec amour.</p>
            <p class="signature">Julie &amp; José</p>
        </div>
    </div>
</section>

<section class="section section--card" aria-labelledby="skills-title">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow">Engagement qualité</p>
            <h2 id="skills-title">Notre savoir-faire</h2>
        </div>
        <ul class="grid grid--3 list-reset">
            <?php foreach ($skills as $skill): ?>
                <li>
                    <article class="card feature-card">
                        <?php icon($skill['icon'], 'icon icon--lg feature-card__icon') ?>
                        <h3><?= e($skill['title']) ?></h3>
                        <p><?= e($skill['text']) ?></p>
                    </article>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php if ($reviews !== []): ?>
<section class="section" aria-labelledby="reviews-title">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow">Témoignages</p>
            <h2 id="reviews-title">Ils nous ont fait confiance</h2>
        </div>
        <ul class="grid grid--3 list-reset">
            <?php foreach ($reviews as $review): ?>
                <li>
                    <figure class="card review-card">
                        <p class="review-card__stars">
                            <?php for ($i = 0; $i < $review['note']; $i++) { icon('star', 'icon icon--sm icon--fill'); } ?>
                            <span class="sr-only">Note : <?= (int) $review['note'] ?> sur 5</span>
                        </p>
                        <blockquote class="review-card__quote"><p>« <?= e($review['texte']) ?> »</p></blockquote>
                        <figcaption>
                            <span class="review-card__author"><?= e($review['auteur']) ?></span>
                            <span class="review-card__since">Client depuis <?= (int) $review['depuis'] ?></span>
                        </figcaption>
                    </figure>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>


<section class="section section--bordeaux cta" aria-labelledby="cta-title">
    <div class="container cta__inner">
        <p class="eyebrow eyebrow--light">Passez à l'action</p>
        <h2 id="cta-title">Un événement à organiser ?</h2>
        <a class="btn btn--light" href="<?= e(url('/menus')) ?>">Voir nos menus <span aria-hidden="true">→</span></a>
    </div>
</section>

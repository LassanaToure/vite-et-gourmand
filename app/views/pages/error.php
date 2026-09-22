<section class="section legal" aria-labelledby="error-title">
    <div class="container container--narrow">
        <p class="eyebrow">Erreur <?= (int) $code ?></p>
        <h1 id="error-title"><?= e($heading) ?></h1>
        <p class="lead"><?= e($message) ?></p>
        <a class="btn" href="<?= e(url('/')) ?>">Retour à l'accueil</a>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/header.php';
?>
<section class="container hero">
    <div>
        <h1><?= t('landing.hero_title') ?></h1>
        <p><?= t('landing.hero_subtitle') ?></p>
        <div style="margin-top:2rem; display:flex; gap:1rem;">
            <a class="btn btn-primary" href="login.php"><?= t('landing.cta_login') ?></a>
            <a class="btn btn-outline" href="register.php"><?= t('landing.cta_register') ?></a>
        </div>
    </div>
    <div class="card">
        <h3><?= t('app.title') ?></h3>
        <p><?= t('app.description') ?></p>
        <ul style="list-style: none; padding:0; margin:1.5rem 0;">
            <li>✔️ <?= t('nav.courses') ?> (PL / EN / RU / ID / VI)</li>
            <li>✔️ <?= t('nav.tests') ?> – <?= t('test.minimum_score', ['score' => '80']) ?></li>
            <li>✔️ <?= t('nav.certificates') ?> PDF</li>
        </ul>
        <a class="btn btn-primary" href="register.php"><?= t('landing.cta_register') ?></a>
    </div>
</section>
<section class="container">
    <div class="card-grid">
        <div class="card">
            <h3>📚 <?= t('nav.courses') ?></h3>
            <p>Responsywne materiały wideo, PDF i prezentacje dopasowane do języka kursanta.</p>
        </div>
        <div class="card">
            <h3>📝 <?= t('nav.tests') ?></h3>
            <p>Losowe pytania jednokrotnego wyboru zapisywane w historii prób.</p>
        </div>
        <div class="card">
            <h3>📄 <?= t('nav.certificates') ?></h3>
            <p>Automatycznie numerowane certyfikaty PDF z miejscem na podpis i datę.</p>
        </div>
    </div>
</section>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>

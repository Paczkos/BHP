<?php
require_once __DIR__ . '/../includes/header.php';
?>
<section class="container hero">
    <div class="hero-copy">
        <span class="hero-badge"><?= t('landing.hero_badge') ?></span>
        <h1><?= t('landing.hero_title') ?></h1>
        <p><?= t('landing.hero_subtitle') ?></p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="login.php"><?= t('landing.cta_login') ?></a>
            <a class="btn btn-outline" href="register.php"><?= t('landing.cta_register') ?></a>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <span><?= t('landing.stat_users') ?></span>
                <strong>1 200+</strong>
            </div>
            <div class="stat-card">
                <span><?= t('landing.stat_courses') ?></span>
                <strong>24</strong>
            </div>
            <div class="stat-card">
                <span><?= t('landing.stat_languages') ?></span>
                <strong>5</strong>
            </div>
        </div>
    </div>
    <div class="hero-card">
        <h3><?= t('landing.hero_card_title') ?></h3>
        <p><?= t('app.description') ?></p>
        <ul>
            <li>✔️ <?= t('landing.feature_courses') ?></li>
            <li>✔️ <?= t('landing.feature_tests') ?></li>
            <li>✔️ <?= t('landing.feature_certificates') ?></li>
        </ul>
        <a class="btn btn-primary" href="register.php"><?= t('landing.cta_register') ?></a>
    </div>
</section>

<section class="container section">
    <div class="section-header">
        <h2><?= t('landing.section_title') ?></h2>
        <a href="register.php" class="btn btn-outline"><?= t('landing.cta_register') ?></a>
    </div>
    <div class="feature-grid">
        <div class="card">
            <span class="feature-icon">📚</span>
            <h3><?= t('nav.courses') ?></h3>
            <p><?= t('landing.feature_courses_desc') ?></p>
        </div>
        <div class="card">
            <span class="feature-icon">📝</span>
            <h3><?= t('nav.tests') ?></h3>
            <p><?= t('landing.feature_tests_desc') ?></p>
        </div>
        <div class="card">
            <span class="feature-icon">📄</span>
            <h3><?= t('nav.certificates') ?></h3>
            <p><?= t('landing.feature_certificates_desc') ?></p>
        </div>
        <div class="card">
            <span class="feature-icon">🛡️</span>
            <h3><?= t('landing.feature_admin_title') ?></h3>
            <p><?= t('landing.feature_admin_desc') ?></p>
        </div>
    </div>
</section>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>

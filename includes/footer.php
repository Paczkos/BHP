</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> <?= APP_NAME ?></span>
        <label class="dark-toggle" for="dark-mode-toggle">
            <input type="checkbox" id="dark-mode-toggle" aria-label="<?= t('branding.dark_mode') ?>">
            <span><?= t('branding.dark_mode') ?></span>
        </label>
    </div>
</footer>
<script src="<?= asset_url('assets/js/app.js') ?>" defer></script>
</body>
</html>

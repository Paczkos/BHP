</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <strong>AGAT GROUP Sp. z o.o.</strong>
            <span><?= t('branding.tagline') ?></span>
        </div>
        <span>&copy; <?= date('Y') ?> AGAT GROUP Sp. z o.o. · <?= t('branding.footer_rights') ?></span>
        <label class="dark-toggle" for="dark-mode-toggle">
            <input type="checkbox" id="dark-mode-toggle" aria-label="<?= t('branding.dark_mode') ?>">
            <span><?= t('branding.dark_mode') ?></span>
        </label>
    </div>
</footer>
<script src="<?= asset_url('assets/js/app.js') ?>" defer></script>
</body>
</html>

<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/course.php';

require_login();
$user = current_user();
$courseId = (int)($_GET['id'] ?? 0);
$course = get_course($courseId);

if (!$course) {
    flash('error', t('alerts.course_not_found'));
    redirect('dashboard.php');
}

$materials = get_course_materials($courseId);
?>
<div class="container">
    <a href="dashboard.php" class="btn btn-outline">&larr; <?= t('nav.dashboard') ?></a>
    <div class="card" style="margin-top:2rem;">
        <h1><?= htmlspecialchars($course['title']) ?></h1>
        <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
        <h3 style="margin-top:2rem;"><?= t('dashboard.materials') ?></h3>
        <?php if (empty($materials)): ?>
            <p class="empty-state"><?= t('dashboard.no_materials') ?></p>
        <?php else: ?>
            <div class="materials-grid">
                <?php foreach ($materials as $material): ?>
                    <?php
                    if (empty($material['file_path'])) {
                        continue;
                    }

                    $fileUrl = asset_url($material['file_path']);
                    $extension = strtolower(pathinfo($material['file_path'], PATHINFO_EXTENSION));
                    $isPrimary = !empty($material['is_primary']);
                    $displayTitle = $material['title'] ?? '';

                    if ($isPrimary) {
                        $displayTitle = trim($displayTitle) !== ''
                            ? sprintf('%s — %s', $displayTitle, t('dashboard.primary_material'))
                            : t('dashboard.primary_material');
                    }
                    ?>
                    <article class="material-card">
                        <header class="material-card__header">
                            <h4><?= htmlspecialchars($displayTitle) ?></h4>
                        </header>
                        <div class="material-preview">
                            <?php if (in_array($extension, ['mp4', 'webm', 'ogg'])): ?>
                                <video controls preload="metadata">
                                    <source src="<?= htmlspecialchars($fileUrl) ?>" type="video/<?= htmlspecialchars($extension) ?>">
                                    <?= t('dashboard.material_preview_unavailable') ?>
                                </video>
                            <?php elseif (in_array($extension, ['mp3', 'wav', 'aac', 'oga', 'm4a'])): ?>
                                <audio controls preload="metadata">
                                    <source src="<?= htmlspecialchars($fileUrl) ?>" type="audio/<?= htmlspecialchars($extension) ?>">
                                    <?= t('dashboard.material_preview_unavailable') ?>
                                </audio>
                            <?php elseif ($extension === 'pdf'): ?>
                                <iframe src="<?= htmlspecialchars($fileUrl) ?>" title="<?= htmlspecialchars($displayTitle) ?>" loading="lazy"></iframe>
                            <?php elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])): ?>
                                <img src="<?= htmlspecialchars($fileUrl) ?>" alt="<?= htmlspecialchars($displayTitle) ?>">
                            <?php else: ?>
                                <p class="material-card__notice"><?= t('dashboard.material_preview_unavailable') ?></p>
                            <?php endif; ?>
                        </div>
                        <a class="btn btn-outline" href="<?= htmlspecialchars($fileUrl) ?>" target="_blank" rel="noopener">
                            <?= t('dashboard.download_material') ?>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <a class="btn btn-primary" href="test.php?course_id=<?= $course['id'] ?>"><?= t('dashboard.start_test') ?></a>
    </div>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>

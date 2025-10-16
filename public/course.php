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
        <ul style="line-height:1.8;">
            <?php foreach ($materials as $material): ?>
                <li><a href="../<?= htmlspecialchars($material['file_path']) ?>" target="_blank"><?= htmlspecialchars($material['title']) ?></a></li>
            <?php endforeach; ?>
        </ul>
        <a class="btn btn-primary" href="test.php?course_id=<?= $course['id'] ?>"><?= t('dashboard.start_test') ?></a>
    </div>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>

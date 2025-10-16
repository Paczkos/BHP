<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();
$results = $pdo->query('SELECT r.*, u.first_name, u.last_name, c.title FROM results r JOIN users u ON r.user_id = u.id JOIN courses c ON r.course_id = c.id ORDER BY r.completed_at DESC')->fetchAll();
?>
<h1><?= t('admin.results') ?></h1>
<p><a class="btn btn-outline" href="export_results.php">Export CSV</a></p>
<table class="table">
    <thead>
        <tr>
            <th><?= t('auth.first_name') ?></th>
            <th><?= t('auth.last_name') ?></th>
            <th><?= t('nav.courses') ?></th>
            <th><?= t('dashboard.score') ?></th>
            <th><?= t('dashboard.status_passed') ?></th>
            <th><?= t('certificate.date') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $result): ?>
            <tr>
                <td><?= htmlspecialchars($result['first_name']) ?></td>
                <td><?= htmlspecialchars($result['last_name']) ?></td>
                <td><?= htmlspecialchars($result['title']) ?></td>
                <td><?= $result['score_percent'] ?>%</td>
                <td><?= $result['passed'] ? t('dashboard.status_passed') : t('dashboard.status_failed') ?></td>
                <td><?= htmlspecialchars($result['completed_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
require_once __DIR__ . '/footer.php';
?>

<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();
$results = $pdo->query('SELECT r.*, u.first_name, u.last_name, c.title FROM results r JOIN users u ON r.user_id = u.id JOIN courses c ON r.course_id = c.id ORDER BY r.completed_at DESC')->fetchAll();
?>
<section class="admin-page-header">
    <div>
        <p class="admin-eyebrow"><?= t('admin.results') ?></p>
        <h1><?= t('admin.results') ?></h1>
        <p class="admin-page-subtitle"><?= t('admin.results_subtitle') ?></p>
    </div>
    <div class="admin-quick-links">
        <a class="btn btn-outline" href="export_results.php"><?= t('admin.export_csv') ?></a>
        <a class="btn btn-outline" href="certificates.php"><?= t('admin.certificates') ?></a>
    </div>
</section>

<div class="card admin-card table-card">
    <?php if (empty($results)): ?>
        <p class="empty-state"><?= t('admin.no_results') ?></p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table table--admin">
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
                            <td>
                                <span class="status-pill <?= $result['passed'] ? 'status-pill--success' : 'status-pill--warning' ?>">
                                    <?= $result['passed'] ? t('dashboard.status_passed') : t('dashboard.status_failed') ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($result['completed_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/footer.php';
?>

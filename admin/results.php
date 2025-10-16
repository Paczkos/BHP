<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_result'])) {
    $resultId = (int)($_POST['result_id'] ?? 0);

    if ($resultId > 0) {
        $stmt = $pdo->prepare('DELETE FROM results WHERE id = :id');
        $stmt->execute(['id' => $resultId]);

        if ($stmt->rowCount() > 0) {
            flash('success', t('admin.result_deleted'));
        } else {
            flash('error', t('admin.result_delete_failed'));
        }
    } else {
        flash('error', t('admin.result_delete_failed'));
    }

    redirect('results.php');
}

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

<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= $message ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= $message ?></div>
<?php endif; ?>

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
                        <th><?= t('admin.actions') ?></th>
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
                            <td>
                                <div class="table-actions">
                                    <form method="post" data-confirm="<?= t('admin.result_delete_confirm') ?>">
                                        <input type="hidden" name="result_id" value="<?= $result['id'] ?>">
                                        <button class="btn btn-danger btn--sm" type="submit" name="delete_result" value="1">
                                            <?= t('admin.delete') ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
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

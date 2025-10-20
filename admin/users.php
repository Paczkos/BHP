<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($userId > 0) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);

        if ($stmt->rowCount() > 0) {
            flash('success', t('admin.user_deleted'));
        } else {
            flash('error', t('admin.user_delete_failed'));
        }
    } else {
        flash('error', t('admin.user_delete_failed'));
    }

    redirect('users.php');
}

$usersStmt = $pdo->query('SELECT u.*, 
        COUNT(r.id) AS results_total,
        SUM(CASE WHEN r.passed = 1 THEN 1 ELSE 0 END) AS results_passed
    FROM users u
    LEFT JOIN results r ON r.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC');
$users = $usersStmt->fetchAll();
?>
<section class="admin-page-header">
    <div>
        <p class="admin-eyebrow"><?= t('admin.users_manage') ?></p>
        <h1><?= t('admin.users_manage_title') ?></h1>
        <p class="admin-page-subtitle"><?= t('admin.users_manage_subtitle') ?></p>
    </div>
    <div class="admin-quick-links">
        <a class="btn btn-outline" href="courses.php"><?= t('admin.courses') ?></a>
        <a class="btn btn-outline" href="results.php"><?= t('admin.results') ?></a>
    </div>
</section>

<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= $message ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= $message ?></div>
<?php endif; ?>

<div class="card admin-card table-card">
    <?php if (empty($users)): ?>
        <p class="empty-state"><?= t('admin.no_recent_users') ?></p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table table--admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= t('auth.first_name') ?></th>
                        <th><?= t('auth.last_name') ?></th>
                        <th><?= t('auth.email') ?></th>
                        <th><?= t('auth.language') ?></th>
                        <th><?= t('admin.tests_taken') ?></th>
                        <th><?= t('dashboard.status_passed') ?></th>
                        <th><?= t('admin.registered_at') ?></th>
                        <th><?= t('admin.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['first_name']) ?></td>
                            <td><?= htmlspecialchars($user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= strtoupper($user['language']) ?></td>
                            <td><?= (int)$user['results_total'] ?></td>
                            <td><?= (int)$user['results_passed'] ?></td>
                            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="table-actions">
                                    <form method="post" data-confirm="<?= t('admin.user_delete_confirm') ?>">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button class="btn btn-danger btn--sm" type="submit" name="delete_user" value="1">
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

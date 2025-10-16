<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();
$users = $pdo->query('SELECT u.*, (SELECT COUNT(*) FROM results r WHERE r.user_id = u.id AND r.passed = 1) AS passed_tests FROM users u ORDER BY u.created_at DESC')->fetchAll();
?>
<h1><?= t('admin.users') ?></h1>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th><?= t('auth.first_name') ?></th>
            <th><?= t('auth.last_name') ?></th>
            <th><?= t('auth.email') ?></th>
            <th><?= t('auth.language') ?></th>
            <th><?= t('nav.tests') ?></th>
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
                <td><?= $user['passed_tests'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
require_once __DIR__ . '/footer.php';
?>

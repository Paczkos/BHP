<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();
$stats = [
    'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'courses' => (int)$pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn(),
    'results' => (int)$pdo->query('SELECT COUNT(*) FROM results')->fetchColumn(),
    'certificates' => (int)$pdo->query('SELECT COUNT(*) FROM certificates')->fetchColumn(),
];

$usersStmt = $pdo->query('SELECT u.*, (
        SELECT COUNT(*) FROM results r WHERE r.user_id = u.id AND r.passed = 1
    ) AS passed_tests
    FROM users u
    ORDER BY u.created_at DESC
    LIMIT 12');
$users = $usersStmt->fetchAll();
?>
<section class="admin-page-header">
    <div>
        <p class="admin-eyebrow"><?= t('admin.panel') ?></p>
        <h1><?= t('admin.overview_title') ?></h1>
        <p class="admin-page-subtitle"><?= t('admin.overview_subtitle') ?></p>
    </div>
    <div class="admin-quick-links">
        <a class="btn btn-outline" href="courses.php"><?= t('admin.open_courses') ?></a>
        <a class="btn btn-outline" href="results.php"><?= t('admin.open_results') ?></a>
        <a class="btn btn-outline" href="certificates.php"><?= t('admin.open_certificates') ?></a>
    </div>
</section>

<section class="admin-dashboard-grid">
    <article class="admin-dashboard-card">
        <span class="admin-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" role="presentation" focusable="false"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-3.33 0-10 1.67-10 5v1h20v-1c0-3.33-6.67-5-10-5Z"></path></svg>
        </span>
        <div class="admin-metric-details">
            <span class="admin-metric-label"><?= t('admin.metric_users') ?></span>
            <span class="admin-metric-value"><?= number_format($stats['users']) ?></span>
        </div>
    </article>
    <article class="admin-dashboard-card">
        <span class="admin-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" role="presentation" focusable="false"><path d="M5 3h14a2 2 0 0 1 2 2v14l-4-2-4 2-4-2-4 2V5a2 2 0 0 1 2-2Z"></path></svg>
        </span>
        <div class="admin-metric-details">
            <span class="admin-metric-label"><?= t('admin.metric_courses') ?></span>
            <span class="admin-metric-value"><?= number_format($stats['courses']) ?></span>
        </div>
    </article>
    <article class="admin-dashboard-card">
        <span class="admin-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" role="presentation" focusable="false"><path d="M21 6H3V4h18Zm0 5H3V9h18Zm0 5H3v-2h18Z"></path></svg>
        </span>
        <div class="admin-metric-details">
            <span class="admin-metric-label"><?= t('admin.metric_results') ?></span>
            <span class="admin-metric-value"><?= number_format($stats['results']) ?></span>
        </div>
    </article>
    <article class="admin-dashboard-card">
        <span class="admin-metric-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" role="presentation" focusable="false"><path d="M12 2 9.19 8.26 2 9.27l5 4.87L5.82 21 12 17.77 18.18 21 17 14.14l5-4.87-7.19-1.01Z"></path></svg>
        </span>
        <div class="admin-metric-details">
            <span class="admin-metric-label"><?= t('admin.metric_certificates') ?></span>
            <span class="admin-metric-value"><?= number_format($stats['certificates']) ?></span>
        </div>
    </article>
</section>

<section class="admin-section">
    <div class="admin-section-header">
        <div>
            <h2><?= t('admin.latest_registrations') ?></h2>
            <p><?= t('admin.latest_registrations_caption') ?></p>
        </div>
    </div>
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
                            <th><?= t('nav.tests') ?></th>
                            <th><?= t('admin.registered_at') ?></th>
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
                                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
require_once __DIR__ . '/footer.php';
?>

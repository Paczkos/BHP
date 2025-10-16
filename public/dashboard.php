<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/course.php';

require_login();
$user = current_user();
$courses = get_courses($_SESSION['lang'] ?? 'pl', $user['id']);
$results = get_user_results($user['id']);
$certificates = get_user_certificates($user['id']);
?>
<div class="sidebar-layout">
    <aside class="sidebar">
        <a href="#courses" class="active"><?= t('nav.courses') ?></a>
        <a href="#results"><?= t('nav.tests') ?></a>
        <a href="#certificates"><?= t('nav.certificates') ?></a>
    </aside>
    <section class="content">
        <h1><?= t('dashboard.welcome', ['name' => $user['first_name']]) ?></h1>
        <?php if ($msg = flash('success')): ?>
            <div class="alert"><?= $msg ?></div>
        <?php endif; ?>
        <section id="courses" style="margin-top:2rem;">
            <h2><?= t('dashboard.available_courses') ?></h2>
            <div class="card-grid">
                <?php foreach ($courses as $course): ?>
                    <article class="card">
                        <h3><?= htmlspecialchars($course['title']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                        <a class="btn btn-primary" href="course.php?id=<?= $course['id'] ?>"><?= t('dashboard.materials') ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <section id="results" style="margin-top:3rem;">
            <h2><?= t('dashboard.your_results') ?></h2>
            <table class="table">
                <thead>
                    <tr>
                        <th><?= t('nav.courses') ?></th>
                        <th><?= t('dashboard.score') ?></th>
                        <th><?= t('test.minimum_score', ['score' => '80']) ?></th>
                        <th><?= t('dashboard.status_passed') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars($result['title']) ?></td>
                            <td><?= $result['score_percent'] ?>%</td>
                            <td>80%</td>
                            <td>
                                <?php if ($result['passed']): ?>
                                    <span class="badge success"><?= t('dashboard.status_passed') ?></span>
                                <?php else: ?>
                                    <span class="badge danger"><?= t('dashboard.status_failed') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <section id="certificates" style="margin-top:3rem;">
            <h2><?= t('dashboard.your_certificates') ?></h2>
            <table class="table">
                <thead>
                    <tr>
                        <th><?= t('nav.courses') ?></th>
                        <th><?= t('certificate.number') ?></th>
                        <th><?= t('certificate.date') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $certificate): ?>
                        <tr>
                            <td><?= htmlspecialchars($certificate['title']) ?></td>
                            <td><?= htmlspecialchars($certificate['certificate_number']) ?></td>
                            <td><?= htmlspecialchars($certificate['issued_at']) ?></td>
                            <td><a class="btn btn-outline" href="../<?= htmlspecialchars($certificate['pdf_path']) ?>" target="_blank"><?= t('dashboard.download') ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </section>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>

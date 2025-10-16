<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $language = sanitize($_POST['language'] ?? 'pl');
    $filePath = sanitize($_POST['file_path'] ?? '');

    $stmt = $pdo->prepare('INSERT INTO courses (title, description, language, file_path) VALUES (:title, :description, :language, :file_path)');
    $stmt->execute([
        'title' => $title,
        'description' => $description,
        'language' => $language,
        'file_path' => $filePath,
    ]);
    flash('success', t('admin.course_created'));
    redirect('courses.php');
}

$courses = $pdo->query('SELECT * FROM courses ORDER BY created_at DESC')->fetchAll();
?>
<section class="admin-page-header">
    <div>
        <p class="admin-eyebrow"><?= t('admin.courses') ?></p>
        <h1><?= t('admin.courses') ?></h1>
        <p class="admin-page-subtitle"><?= t('admin.courses_subtitle') ?></p>
    </div>
    <div class="admin-quick-links">
        <a class="btn btn-outline" href="index.php"><?= t('admin.users') ?></a>
        <a class="btn btn-outline" href="results.php"><?= t('admin.results') ?></a>
    </div>
</section>

<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= $message ?></div>
<?php endif; ?>

<div class="card admin-card admin-card--form">
    <div class="admin-card-header">
        <h2><?= t('admin.add_course') ?></h2>
        <p><?= t('admin.add_course_help') ?></p>
    </div>
    <form method="post" class="form-vertical">
        <div class="form-grid">
            <div class="form-field">
                <label for="course-title"><?= t('nav.courses') ?></label>
                <input type="text" id="course-title" name="title" required>
            </div>
            <div class="form-field">
                <label for="course-language"><?= t('auth.language') ?></label>
                <select id="course-language" name="language">
                    <?php foreach (AVAILABLE_LANGUAGES as $code): ?>
                        <option value="<?= $code ?>"><?= strtoupper($code) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-field">
            <label for="course-material"><?= t('admin.materials') ?></label>
            <input type="text" id="course-material" name="file_path" placeholder="uploads/material.pdf">
        </div>
        <div class="form-field">
            <label for="course-description"><?= t('admin.course_description') ?></label>
            <textarea id="course-description" name="description" rows="4"></textarea>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= t('admin.save') ?></button>
        </div>
    </form>
</div>

<div class="card admin-card table-card">
    <?php if (empty($courses)): ?>
        <p class="empty-state"><?= t('admin.no_courses') ?></p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table table--admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= t('nav.courses') ?></th>
                        <th><?= t('auth.language') ?></th>
                        <th><?= t('admin.created_at') ?></th>
                        <th><?= t('admin.questions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?= $course['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($course['title']) ?></strong>
                                <?php if (!empty($course['file_path'])): ?>
                                    <div class="table-subtext"><a href="../<?= htmlspecialchars($course['file_path']) ?>" target="_blank" rel="noopener"><?= t('admin.materials') ?></a></div>
                                <?php endif; ?>
                            </td>
                            <td><?= strtoupper($course['language']) ?></td>
                            <td><?= date('Y-m-d', strtotime($course['created_at'])) ?></td>
                            <td><a class="btn btn-outline" href="questions.php?course_id=<?= $course['id'] ?>"><?= t('admin.questions') ?></a></td>
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

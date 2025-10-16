<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();

$editingCourse = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :id');
        $stmt->execute(['id' => $editId]);
        $editingCourse = $stmt->fetch();
        if (!$editingCourse) {
            flash('error', t('alerts.course_not_found'));
            redirect('courses.php');
        }
    } else {
        redirect('courses.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = isset($_POST['course_id']) ? (int) $_POST['course_id'] : null;
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $language = sanitize($_POST['language'] ?? 'pl');
    $filePath = sanitize($_POST['file_path'] ?? '');

    if ($courseId) {
        $stmt = $pdo->prepare('UPDATE courses SET title = :title, description = :description, language = :language, file_path = :file_path WHERE id = :id');
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'language' => $language,
            'file_path' => $filePath,
            'id' => $courseId,
        ]);
        flash('success', t('admin.course_updated'));
    } else {
        $stmt = $pdo->prepare('INSERT INTO courses (title, description, language, file_path) VALUES (:title, :description, :language, :file_path)');
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'language' => $language,
            'file_path' => $filePath,
        ]);
        flash('success', t('admin.course_created'));
    }

    redirect('courses.php');
}

$courses = $pdo->query('SELECT * FROM courses ORDER BY created_at DESC')->fetchAll();
$formTitle = $editingCourse['title'] ?? '';
$formDescription = $editingCourse['description'] ?? '';
$formLanguage = $editingCourse['language'] ?? 'pl';
$formFilePath = $editingCourse['file_path'] ?? '';
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
    <?php if ($message = flash('success')): ?>
        <div class="alert success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($error = flash('error')): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>
</section>

<div class="card admin-card admin-card--form">
    <div class="admin-card-header">
        <h2><?= $editingCourse ? t('admin.edit_course') : t('admin.add_course') ?></h2>
        <p><?= $editingCourse ? t('admin.edit_course_help') : t('admin.add_course_help') ?></p>
    </div>
    <form method="post" class="form-vertical">
        <?php if ($editingCourse): ?>
            <input type="hidden" name="course_id" value="<?= (int) $editingCourse['id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-field">
                <label for="course-title"><?= t('nav.courses') ?></label>
                <input type="text" id="course-title" name="title" value="<?= htmlspecialchars($formTitle) ?>" required>
            </div>
            <div class="form-field">
                <label for="course-language"><?= t('auth.language') ?></label>
                <select id="course-language" name="language">
                    <?php foreach (AVAILABLE_LANGUAGES as $code): ?>
                        <option value="<?= $code ?>" <?= $formLanguage === $code ? 'selected' : '' ?>><?= strtoupper($code) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-field">
            <label for="course-material"><?= t('admin.materials') ?></label>
            <input type="text" id="course-material" name="file_path" placeholder="uploads/material.pdf" value="<?= htmlspecialchars($formFilePath) ?>">
        </div>
        <div class="form-field">
            <label for="course-description"><?= t('admin.course_description') ?></label>
            <textarea id="course-description" name="description" rows="4"><?= htmlspecialchars($formDescription) ?></textarea>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $editingCourse ? t('admin.update') : t('admin.save') ?></button>
            <?php if ($editingCourse): ?>
                <a class="btn btn-outline" href="courses.php"><?= t('admin.cancel') ?></a>
            <?php endif; ?>
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
                        <th><?= t('admin.actions') ?></th>
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
                            <td class="table-actions">
                                <a class="btn btn-outline" href="courses.php?edit=<?= $course['id'] ?>"><?= t('admin.edit') ?></a>
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

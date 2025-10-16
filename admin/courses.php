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
    flash('success', t('admin.courses'));
}

$courses = $pdo->query('SELECT * FROM courses ORDER BY created_at DESC')->fetchAll();
?>
<h1><?= t('admin.courses') ?></h1>
<?php if ($message = flash('success')): ?>
    <div class="alert"><?= $message ?></div>
<?php endif; ?>
<div class="card" style="margin-bottom:2rem;">
    <h2><?= t('admin.add_course') ?></h2>
    <form method="post">
        <div>
            <label><?= t('nav.courses') ?></label>
            <input type="text" name="title" required>
        </div>
        <div>
            <label><?= t('admin.materials') ?></label>
            <input type="text" name="file_path" placeholder="uploads/material.pdf">
        </div>
        <div>
            <label><?= t('auth.language') ?></label>
            <select name="language">
                <?php foreach (AVAILABLE_LANGUAGES as $code): ?>
                    <option value="<?= $code ?>"><?= strtoupper($code) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Opis</label>
            <textarea name="description" rows="4" style="width:100%;"></textarea>
        </div>
        <button class="btn btn-primary" type="submit"><?= t('admin.save') ?></button>
    </form>
</div>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th><?= t('nav.courses') ?></th>
            <th><?= t('auth.language') ?></th>
            <th><?= t('admin.questions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?= $course['id'] ?></td>
                <td><?= htmlspecialchars($course['title']) ?></td>
                <td><?= strtoupper($course['language']) ?></td>
                <td><a class="btn btn-outline" href="questions.php?course_id=<?= $course['id'] ?>"><?= t('admin.questions') ?></a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
require_once __DIR__ . '/footer.php';
?>

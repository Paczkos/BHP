<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['certificate_id'])) {
    $certificateId = (int)$_POST['certificate_id'];
    if (!empty($_FILES['signed_scan']['name'])) {
        $fileName = 'signed_' . $certificateId . '_' . basename($_FILES['signed_scan']['name']);
        $target = __DIR__ . '/../uploads/signed/' . $fileName;
        if (move_uploaded_file($_FILES['signed_scan']['tmp_name'], $target)) {
            $pdo->prepare('UPDATE certificates SET signed_scan_path = :path WHERE id = :id')
                ->execute([
                    'path' => 'uploads/signed/' . $fileName,
                    'id' => $certificateId,
                ]);
            flash('success', t('admin.upload_signed_scan'));
        }
    }
}

$certificates = $pdo->query('SELECT cert.*, u.first_name, u.last_name, c.title FROM certificates cert JOIN users u ON cert.user_id = u.id JOIN courses c ON cert.course_id = c.id ORDER BY cert.issued_at DESC')->fetchAll();
?>
<h1><?= t('admin.certificates') ?></h1>
<?php if ($message = flash('success')): ?>
    <div class="alert"><?= $message ?></div>
<?php endif; ?>
<table class="table">
    <thead>
        <tr>
            <th><?= t('auth.first_name') ?></th>
            <th><?= t('auth.last_name') ?></th>
            <th><?= t('nav.courses') ?></th>
            <th><?= t('certificate.number') ?></th>
            <th><?= t('certificate.date') ?></th>
            <th><?= t('admin.upload_signed_scan') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($certificates as $certificate): ?>
            <tr>
                <td><?= htmlspecialchars($certificate['first_name']) ?></td>
                <td><?= htmlspecialchars($certificate['last_name']) ?></td>
                <td><?= htmlspecialchars($certificate['title']) ?></td>
                <td><a href="../<?= htmlspecialchars($certificate['pdf_path']) ?>" target="_blank"><?= htmlspecialchars($certificate['certificate_number']) ?></a></td>
                <td><?= htmlspecialchars($certificate['issued_at']) ?></td>
                <td>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="certificate_id" value="<?= $certificate['id'] ?>">
                        <input type="file" name="signed_scan" accept=".pdf,.jpg,.jpeg,.png" required>
                        <button class="btn btn-primary" type="submit"><?= t('admin.save') ?></button>
                        <?php if ($certificate['signed_scan_path']): ?>
                            <a href="../<?= htmlspecialchars($certificate['signed_scan_path']) ?>" target="_blank"><?= t('dashboard.download') ?></a>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
require_once __DIR__ . '/footer.php';
?>

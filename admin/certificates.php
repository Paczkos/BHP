<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_certificate_id'])) {
        $certificateId = (int)$_POST['delete_certificate_id'];

        $stmt = $pdo->prepare('SELECT pdf_path, signed_scan_path FROM certificates WHERE id = :id');
        $stmt->execute(['id' => $certificateId]);
        $certificate = $stmt->fetch();

        if ($certificate) {
            $delete = $pdo->prepare('DELETE FROM certificates WHERE id = :id');
            $delete->execute(['id' => $certificateId]);

            if ($delete->rowCount() > 0) {
                foreach (['pdf_path', 'signed_scan_path'] as $field) {
                    if (!empty($certificate[$field])) {
                        $file = __DIR__ . '/../' . $certificate[$field];
                        if (is_file($file)) {
                            @unlink($file);
                        }
                    }
                }

                flash('success', t('admin.certificate_deleted'));
            } else {
                flash('error', t('admin.certificate_delete_failed'));
            }
        } else {
            flash('error', t('admin.certificate_delete_failed'));
        }

        redirect('certificates.php');
    }

    if (isset($_POST['certificate_id'])) {
        if (!certificates_support_extended_details()) {
            flash('error', t('admin.signed_scan_unavailable'));
            redirect('certificates.php');
        }

        $certificateId = (int)$_POST['certificate_id'];
        if (!empty($_FILES['signed_scan']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/signed/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileName = 'signed_' . $certificateId . '_' . basename($_FILES['signed_scan']['name']);
            $target = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['signed_scan']['tmp_name'], $target)) {
                $pdo->prepare('UPDATE certificates SET signed_scan_path = :path WHERE id = :id')
                    ->execute([
                        'path' => 'uploads/signed/' . $fileName,
                        'id' => $certificateId,
                    ]);
                flash('success', t('admin.upload_signed_scan'));
            }
        }

        redirect('certificates.php');
    }
}

$certificates = $pdo->query('SELECT cert.*, u.first_name, u.last_name, c.title FROM certificates cert JOIN users u ON cert.user_id = u.id JOIN courses c ON cert.course_id = c.id ORDER BY cert.issued_at DESC')->fetchAll();
?>
<section class="admin-page-header">
    <div>
        <p class="admin-eyebrow"><?= t('admin.certificates') ?></p>
        <h1><?= t('admin.certificates') ?></h1>
        <p class="admin-page-subtitle"><?= t('admin.certificates_subtitle') ?></p>
    </div>
    <div class="admin-quick-links">
        <a class="btn btn-outline" href="results.php"><?= t('admin.results') ?></a>
        <a class="btn btn-outline" href="export_results.php"><?= t('admin.export_csv') ?></a>
    </div>
</section>

<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= $message ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= $message ?></div>
<?php endif; ?>

<div class="card admin-card table-card">
    <?php if (empty($certificates)): ?>
        <p class="empty-state"><?= t('admin.no_certificates') ?></p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table table--admin">
                <thead>
                    <tr>
                        <th><?= t('auth.first_name') ?></th>
                        <th><?= t('auth.last_name') ?></th>
                        <th><?= t('nav.courses') ?></th>
                        <th><?= t('certificate.number') ?></th>
                        <th><?= t('certificate.training_date') ?></th>
                        <th><?= t('certificate.test_date') ?></th>
                        <th><?= t('certificate.company_label') ?></th>
                        <th><?= t('certificate.issued_at') ?></th>
                        <th><?= t('admin.upload_signed_scan') ?></th>
                        <th><?= t('admin.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $certificate): ?>
                        <tr>
                            <td><?= htmlspecialchars($certificate['first_name']) ?></td>
                            <td><?= htmlspecialchars($certificate['last_name']) ?></td>
                            <td><?= htmlspecialchars($certificate['title']) ?></td>
                            <td><a class="link-muted" href="../<?= htmlspecialchars($certificate['pdf_path']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($certificate['certificate_number']) ?></a></td>
                            <?php
                                $trainingDate = $certificate['training_date'] ?? null;
                                $testDate = $certificate['test_date'] ?? null;
                                $company = $certificate['company_name'] ?? null;
                                $placeholder = t('certificate.not_available');
                            ?>
                            <td><?= htmlspecialchars($trainingDate ?: $placeholder) ?></td>
                            <td><?= htmlspecialchars($testDate ?: $placeholder) ?></td>
                            <td><?= htmlspecialchars($company ?: $placeholder) ?></td>
                            <td><?= date('Y-m-d', strtotime($certificate['issued_at'])) ?></td>
                            <td>
                                <?php $signedScanPath = $certificate['signed_scan_path'] ?? null; ?>
                                <?php if (certificates_support_extended_details()): ?>
                                    <form class="inline-form" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="certificate_id" value="<?= $certificate['id'] ?>">
                                        <label class="sr-only" for="signed-scan-<?= $certificate['id'] ?>"><?= t('admin.upload_signed_scan') ?></label>
                                        <input id="signed-scan-<?= $certificate['id'] ?>" type="file" name="signed_scan" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <button class="btn btn-primary" type="submit"><?= t('admin.save') ?></button>
                                        <?php if ($signedScanPath): ?>
                                            <a class="btn btn-outline" href="../<?= htmlspecialchars($signedScanPath) ?>" target="_blank" rel="noopener">
                                                <?= t('dashboard.download') ?>
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                    <p class="table-subtext"><?= t('admin.upload_hint') ?></p>
                                <?php else: ?>
                                    <p class="table-subtext"><?= t('admin.signed_scan_unavailable') ?></p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" data-confirm="<?= t('admin.certificate_delete_confirm') ?>">
                                    <input type="hidden" name="delete_certificate_id" value="<?= $certificate['id'] ?>">
                                    <button class="btn btn-danger btn--sm" type="submit">
                                        <?= t('admin.delete') ?>
                                    </button>
                                </form>
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

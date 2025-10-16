<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/course.php';
require_once __DIR__ . '/../includes/certificate.php';

require_login();
$user = current_user();
$courseId = (int)($_GET['course_id'] ?? ($_POST['course_id'] ?? 0));
$course = get_course($courseId);

if (!$course) {
    flash('error', t('alerts.course_not_found'));
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];
    $questions = $_SESSION['current_test']['questions'] ?? [];
    $correct = 0;

    $pdo = get_db_connection();
    foreach ($questions as $question) {
        $answerId = (int)($answers[$question['id']] ?? 0);
        if ($answerId) {
            $stmt = $pdo->prepare('SELECT is_correct FROM answers WHERE id = :id');
            $stmt->execute(['id' => $answerId]);
            if ($stmt->fetchColumn()) {
                $correct++;
            }
        }
    }

    $total = max(count($questions), 1);
    $score = (int)round(($correct / $total) * 100);
    $passed = $score >= 80;
    $resultId = save_test_result($user['id'], $courseId, $score, $passed);

    if ($passed) {
        $result = get_result_by_id($resultId);
        $testDate = $result && !empty($result['completed_at']) ? date('Y-m-d', strtotime($result['completed_at'])) : date('Y-m-d');
        $trainingDate = $testDate;
        $companyName = APP_COMPANY;

        $pdfPath = generate_certificate_pdf($user, $course, 'TEMP', $trainingDate, $testDate, $companyName);
        $certificateNumber = record_certificate($user['id'], $courseId, $pdfPath, $trainingDate, $testDate, $companyName);
        $finalPath = str_replace('TEMP', $certificateNumber, $pdfPath);
        rename(__DIR__ . '/../' . $pdfPath, __DIR__ . '/../' . $finalPath);
        $pdo->prepare('UPDATE certificates SET pdf_path = :path WHERE certificate_number = :number')
            ->execute([
                'path' => $finalPath,
                'number' => $certificateNumber,
            ]);
        send_notification($user['email'], t('nav.certificates'), t('alerts.certificate_generated'));
        flash('success', t('test.result_passed', ['score' => $score]));
    } else {
        flash('error', t('test.result_failed', ['score' => $score]));
    }

    unset($_SESSION['current_test']);
    redirect('dashboard.php#results');
}

$questions = get_random_questions($courseId, $_SESSION['lang'] ?? 'pl');
$_SESSION['current_test'] = [
    'course_id' => $courseId,
    'questions' => $questions,
];
?>
<div class="container">
    <div class="card" style="margin-top:2rem;">
        <h1><?= t('test.heading') ?> – <?= htmlspecialchars($course['title']) ?></h1>
        <p><?= t('test.minimum_score', ['score' => '80']) ?></p>
        <form method="post">
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
            <?php foreach ($questions as $index => $question): ?>
                <div style="margin:1.5rem 0;">
                    <strong><?= ($index + 1) ?>. <?= htmlspecialchars($question['question_text']) ?></strong>
                    <?php foreach ($question['answers'] as $answer): ?>
                        <label style="display:block; margin-top:0.5rem;">
                            <input type="radio" name="answers[<?= $question['id'] ?>]" value="<?= $answer['id'] ?>" required>
                            <?= htmlspecialchars($answer['answer_text']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary"><?= t('test.submit') ?></button>
        </form>
    </div>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>

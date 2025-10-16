<?php
require_once __DIR__ . '/header.php';
require_admin();
$pdo = get_db_connection();
$courseId = (int)($_GET['course_id'] ?? 0);
$course = get_course($courseId);

if (!$course) {
    flash('error', t('alerts.course_not_found'));
    redirect('courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionText = sanitize($_POST['question_text'] ?? '');
    $answers = $_POST['answers'] ?? [];
    $correctIndex = (int)($_POST['correct_answer'] ?? 0);

    $stmt = $pdo->prepare('INSERT INTO questions (course_id, question_text, language) VALUES (:course_id, :question_text, :language)');
    $stmt->execute([
        'course_id' => $courseId,
        'question_text' => $questionText,
        'language' => $course['language'],
    ]);
    $questionId = (int)$pdo->lastInsertId();

    foreach ($answers as $index => $answerText) {
        $answerStmt = $pdo->prepare('INSERT INTO answers (question_id, answer_text, is_correct) VALUES (:question_id, :answer_text, :is_correct)');
        $answerStmt->execute([
            'question_id' => $questionId,
            'answer_text' => sanitize($answerText),
            'is_correct' => ($index == $correctIndex) ? 1 : 0,
        ]);
    }

    flash('success', t('admin.questions'));
}

$questions = $pdo->prepare('SELECT * FROM questions WHERE course_id = :course_id ORDER BY id DESC');
$questions->execute(['course_id' => $courseId]);
$questions = $questions->fetchAll();
?>
<h1><?= t('admin.questions') ?> – <?= htmlspecialchars($course['title']) ?></h1>
<?php if ($message = flash('success')): ?>
    <div class="alert"><?= $message ?></div>
<?php endif; ?>
<div class="card" style="margin-bottom:2rem;">
    <h2>Dodaj pytanie</h2>
    <form method="post">
        <div>
            <label>Pytanie</label>
            <input type="text" name="question_text" required>
        </div>
        <div>
            <label>Odpowiedzi</label>
            <?php for ($i = 0; $i < 4; $i++): ?>
                <input type="text" name="answers[]" required placeholder="Odpowiedź <?= $i + 1 ?>">
            <?php endfor; ?>
        </div>
        <div>
            <label>Poprawna odpowiedź (0-3)</label>
            <input type="number" name="correct_answer" min="0" max="3" value="0">
        </div>
        <button class="btn btn-primary" type="submit"><?= t('admin.save') ?></button>
    </form>
</div>
<div class="card">
    <h2>Istniejące pytania</h2>
    <ol>
        <?php foreach ($questions as $question): ?>
            <li><?= htmlspecialchars($question['question_text']) ?></li>
        <?php endforeach; ?>
    </ol>
</div>
<?php
require_once __DIR__ . '/footer.php';
?>

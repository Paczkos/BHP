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
    if (isset($_POST['delete_question'])) {
        $questionId = (int)($_POST['question_id'] ?? 0);

        if ($questionId > 0) {
            $deleteStmt = $pdo->prepare('DELETE FROM questions WHERE id = :id AND course_id = :course_id');
            $deleteStmt->execute([
                'id' => $questionId,
                'course_id' => $courseId,
            ]);

            if ($deleteStmt->rowCount() > 0) {
                flash('success', t('admin.question_deleted'));
            } else {
                flash('error', t('admin.question_delete_failed'));
            }
        } else {
            flash('error', t('admin.question_delete_failed'));
        }

        redirect('questions.php?course_id=' . $courseId);
    }

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

    flash('success', t('admin.question_created'));
    redirect('questions.php?course_id=' . $courseId);
}

$questions = $pdo->prepare('SELECT * FROM questions WHERE course_id = :course_id ORDER BY id DESC');
$questions->execute(['course_id' => $courseId]);
$questions = $questions->fetchAll();
$answersByQuestion = [];
if (!empty($questions)) {
    $placeholders = implode(',', array_fill(0, count($questions), '?'));
    $questionIds = array_column($questions, 'id');
    $answersStmt = $pdo->prepare("SELECT question_id, answer_text, is_correct FROM answers WHERE question_id IN ($placeholders) ORDER BY id ASC");
    $answersStmt->execute($questionIds);
    foreach ($answersStmt as $answerRow) {
        $answersByQuestion[$answerRow['question_id']][] = $answerRow;
    }
}
?>
<section class="admin-page-header">
    <div>
        <p class="admin-eyebrow"><?= t('admin.questions') ?></p>
        <h1><?= t('admin.questions') ?> – <?= htmlspecialchars($course['title']) ?></h1>
        <p class="admin-page-subtitle"><?= t('admin.questions_subtitle') ?></p>
    </div>
    <div class="admin-quick-links">
        <a class="btn btn-outline" href="courses.php"><?= t('admin.courses') ?></a>
    </div>
</section>

<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= $message ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= $message ?></div>
<?php endif; ?>

<div class="card admin-card admin-card--form">
    <div class="admin-card-header">
        <h2><?= t('admin.add_question') ?></h2>
        <p><?= t('admin.add_question_help') ?></p>
    </div>
    <form method="post" class="form-vertical">
        <div class="form-field">
            <label for="question-text"><?= t('admin.question_label') ?></label>
            <textarea id="question-text" name="question_text" rows="3" required></textarea>
        </div>
        <div class="form-grid form-grid--answers">
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="form-field">
                    <label for="answer-<?= $i ?>"><?= sprintf(t('admin.answer_label'), $i + 1) ?></label>
                    <input type="text" id="answer-<?= $i ?>" name="answers[]" required placeholder="<?= sprintf(t('admin.answer_placeholder'), $i + 1) ?>">
                </div>
            <?php endfor; ?>
        </div>
        <div class="form-field">
            <label for="correct-answer"><?= t('admin.correct_answer_label') ?></label>
            <select id="correct-answer" name="correct_answer">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <option value="<?= $i ?>"><?= sprintf(t('admin.answer_label'), $i + 1) ?></option>
                <?php endfor; ?>
            </select>
            <p class="form-hint"><?= t('admin.correct_answer_hint') ?></p>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= t('admin.save') ?></button>
        </div>
    </form>
</div>

<div class="card admin-card">
    <div class="admin-card-header">
        <h2><?= t('admin.existing_questions') ?></h2>
        <p><?= t('admin.question_list_caption') ?></p>
    </div>
    <?php if (empty($questions)): ?>
        <p class="empty-state"><?= t('admin.no_questions') ?></p>
    <?php else: ?>
        <ol class="question-list">
            <?php foreach ($questions as $question): ?>
                <li>
                    <div class="question-item">
                        <div class="question-item-header">
                            <span class="question-text"><?= htmlspecialchars($question['question_text']) ?></span>
                            <form method="post" data-confirm="<?= t('admin.question_delete_confirm') ?>">
                                <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                                <button class="btn btn-danger btn--sm" type="submit" name="delete_question" value="1"><?= t('admin.delete') ?></button>
                            </form>
                        </div>
                        <?php if (!empty($answersByQuestion[$question['id']])): ?>
                            <ul class="question-answers">
                                <?php foreach ($answersByQuestion[$question['id']] as $answer): ?>
                                    <li class="<?= $answer['is_correct'] ? 'question-answer--correct' : '' ?>">
                                        <span><?= htmlspecialchars($answer['answer_text']) ?></span>
                                        <?php if ($answer['is_correct']): ?>
                                            <span class="status-pill status-pill--success"><?= t('admin.correct_answer_badge') ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/footer.php';
?>

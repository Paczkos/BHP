<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function ensure_course_test_settings_columns(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $pdo = get_db_connection();

    try {
        $hasQuestionLimit = $pdo->query("SHOW COLUMNS FROM courses LIKE 'question_limit'")->fetch();
        if (!$hasQuestionLimit) {
            $pdo->exec('ALTER TABLE courses ADD COLUMN question_limit INT NOT NULL DEFAULT 5');
        }

        $hasPassingScore = $pdo->query("SHOW COLUMNS FROM courses LIKE 'passing_score'")->fetch();
        if (!$hasPassingScore) {
            $pdo->exec('ALTER TABLE courses ADD COLUMN passing_score INT NOT NULL DEFAULT 80');
        }
    } catch (PDOException $exception) {
        // Swallow the exception to keep backwards compatibility with read-only connections.
    }

    $ensured = true;
}

function normalize_course_settings(array $course): array
{
    if (!array_key_exists('question_limit', $course)) {
        $course['question_limit'] = 5;
    }

    if (!array_key_exists('passing_score', $course)) {
        $course['passing_score'] = 80;
    }

    $course['question_limit'] = max(1, (int) $course['question_limit']);
    $course['passing_score'] = max(1, min(100, (int) $course['passing_score']));

    return $course;
}

function certificates_support_extended_details(): bool
{
    static $supports = null;

    if ($supports !== null) {
        return $supports;
    }

    $pdo = get_db_connection();

    try {
        $pdo->query('SELECT training_date, test_date, company_name FROM certificates LIMIT 0');
        $supports = true;
    } catch (PDOException $exception) {
        $supports = false;
    }

    return $supports;
}

function get_courses(string $language, ?int $userId = null): array
{
    ensure_course_test_settings_columns();
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE language = :language ORDER BY title');
    $stmt->execute(['language' => $language]);
    $courses = $stmt->fetchAll();

    foreach ($courses as &$course) {
        $course = normalize_course_settings($course);
    }

    return $courses;
}

function get_course(int $courseId): ?array
{
    ensure_course_test_settings_columns();
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $courseId]);
    $course = $stmt->fetch();
    if (!$course) {
        return null;
    }

    return normalize_course_settings($course);
}

function get_course_materials(int $courseId): array
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT id, course_id, title, file_path, created_at FROM course_materials WHERE course_id = :course_id ORDER BY id');
    $stmt->execute(['course_id' => $courseId]);
    $materials = $stmt->fetchAll();

    foreach ($materials as &$material) {
        $material['is_primary'] = false;
    }
    unset($material);

    $courseStmt = $pdo->prepare('SELECT title, file_path, created_at FROM courses WHERE id = :id');
    $courseStmt->execute(['id' => $courseId]);
    $course = $courseStmt->fetch();

    if ($course && !empty($course['file_path'])) {
        $alreadyListed = false;

        foreach ($materials as $material) {
            if ($material['file_path'] === $course['file_path']) {
                $alreadyListed = true;
                break;
            }
        }

        if (!$alreadyListed) {
            array_unshift($materials, [
                'id' => null,
                'course_id' => $courseId,
                'title' => $course['title'],
                'file_path' => $course['file_path'],
                'created_at' => $course['created_at'] ?? null,
                'is_primary' => true,
            ]);
        }
    }

    return $materials;
}

function get_random_questions(int $courseId, string $language, int $limit = 5): array
{
    $pdo = get_db_connection();

    // MySQL does not allow binding the LIMIT clause when native prepared statements are used.
    // Cast the limit to an integer to avoid SQL injection and interpolate it directly.
    $limit = max(1, (int) $limit);
    $query = sprintf(
        'SELECT * FROM questions WHERE course_id = :course_id AND language = :language ORDER BY RAND() LIMIT %d',
        $limit
    );

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
    $stmt->bindValue(':language', $language, PDO::PARAM_STR);
    $stmt->execute();
    $questions = $stmt->fetchAll();

    foreach ($questions as &$question) {
        $answersStmt = $pdo->prepare('SELECT id, answer_text FROM answers WHERE question_id = :question_id ORDER BY RAND()');
        $answersStmt->execute(['question_id' => $question['id']]);
        $question['answers'] = $answersStmt->fetchAll();
    }

    return $questions;
}

function save_test_result(int $userId, int $courseId, int $scorePercent, bool $passed): int
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('INSERT INTO results (user_id, course_id, score_percent, passed, completed_at) VALUES (:user_id, :course_id, :score, :passed, NOW())');
    $stmt->execute([
        'user_id' => $userId,
        'course_id' => $courseId,
        'score' => $scorePercent,
        'passed' => $passed ? 1 : 0,
    ]);

    return (int) $pdo->lastInsertId();
}

function get_result_by_id(int $resultId): ?array
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT * FROM results WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $resultId]);
    $result = $stmt->fetch();

    return $result ?: null;
}

function record_certificate(
    int $userId,
    int $courseId,
    string $trainingDate,
    string $testDate,
    ?string $companyName = null
): array {
    $pdo = get_db_connection();

    if (certificates_support_extended_details()) {
        $stmt = $pdo->prepare('INSERT INTO certificates (user_id, course_id, certificate_number, pdf_path, training_date, test_date, company_name, issued_at) VALUES (:user_id, :course_id, :number, :pdf_path, :training_date, :test_date, :company_name, NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
            'number' => '',
            'pdf_path' => '',
            'training_date' => $trainingDate,
            'test_date' => $testDate,
            'company_name' => $companyName ?? '',
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO certificates (user_id, course_id, certificate_number, pdf_path, issued_at) VALUES (:user_id, :course_id, :number, :pdf_path, NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
            'number' => '',
            'pdf_path' => '',
        ]);
    }

    $certificateId = (int) $pdo->lastInsertId();
    $certificateNumber = generate_certificate_number($certificateId);

    $update = $pdo->prepare('UPDATE certificates SET certificate_number = :number WHERE id = :id');
    $update->execute([
        'number' => $certificateNumber,
        'id' => $certificateId,
    ]);

    return [
        'id' => $certificateId,
        'number' => $certificateNumber,
    ];
}

function get_user_results(int $userId): array
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT r.*, c.title FROM results r JOIN courses c ON r.course_id = c.id WHERE r.user_id = :user_id ORDER BY r.completed_at DESC');
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function get_user_certificates(int $userId): array
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT cert.*, c.title FROM certificates cert JOIN courses c ON cert.course_id = c.id WHERE cert.user_id = :user_id ORDER BY cert.issued_at DESC');
    $stmt->execute(['user_id' => $userId]);
    $certificates = $stmt->fetchAll();

    if (!certificates_support_extended_details()) {
        foreach ($certificates as &$certificate) {
            $certificate['training_date'] = $certificate['training_date'] ?? null;
            $certificate['test_date'] = $certificate['test_date'] ?? null;
            $certificate['company_name'] = $certificate['company_name'] ?? null;
        }
        unset($certificate);
    }

    return $certificates;
}

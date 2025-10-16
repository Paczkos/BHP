<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!is_admin()) {
    http_response_code(403);
    exit('Forbidden');
}

$pdo = get_db_connection();
$stmt = $pdo->query('SELECT u.first_name, u.last_name, u.email, c.title, r.score_percent, r.passed, r.completed_at FROM results r JOIN users u ON r.user_id = u.id JOIN courses c ON r.course_id = c.id ORDER BY r.completed_at DESC');
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="results.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['First name', 'Last name', 'E-mail', 'Course', 'Score %', 'Passed', 'Completed at']);
foreach ($rows as $row) {
    fputcsv($output, [
        $row['first_name'],
        $row['last_name'],
        $row['email'],
        $row['title'],
        $row['score_percent'],
        $row['passed'] ? 'Yes' : 'No',
        $row['completed_at'],
    ]);
}
fclose($output);
exit;

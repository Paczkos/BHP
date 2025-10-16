<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/fpdf.php';

function generate_certificate_pdf(
    array $user,
    array $course,
    string $certificateNumber,
    string $trainingDate,
    string $testDate,
    int $scorePercent
): string {
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->SetMargins(25, 25, 25);
    $pdf->SetAutoPageBreak(true, 25);
    $pdf->AddPage();

    $pdf->SetFont('Helvetica', 'B', 30);
    $pdf->SetTextColor(0, 70, 130);
    $pdf->Cell(0, 20, APP_NAME, 0, 1, 'C');

    $pdf->SetTextColor(140, 180, 200);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(0, 6, str_repeat('=', 90), 0, 1, 'C');
    $pdf->Ln(2);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'B', 24);
    $pdf->Cell(0, 14, t('certificate.title'), 0, 1, 'C');
    $pdf->Ln(6);

    $pdf->SetFont('Helvetica', '', 15);
    $body = sprintf(
        t('certificate.body'),
        $user['first_name'] . ' ' . $user['last_name'],
        $user['passport_or_pesel'],
        $course['title']
    );
    $pdf->MultiCell(0, 9, $body, 0, 'C');

    $pdf->Ln(6);
    $pdf->SetFont('Helvetica', 'B', 15);
    $pdf->Cell(0, 10, t('certificate.details_heading'), 0, 1, 'C');

    $pdf->SetFont('Helvetica', '', 12);
    $details = [
        t('certificate.course_label') . ': ' . $course['title'],
        t('certificate.score') . ': ' . $scorePercent . '%',
        t('certificate.training_date') . ': ' . $trainingDate,
        t('certificate.test_date') . ': ' . $testDate,
        t('certificate.number') . ': ' . $certificateNumber,
        t('certificate.issued_at') . ': ' . date('Y-m-d'),
    ];

    foreach ($details as $line) {
        $pdf->Cell(0, 8, $line, 0, 1, 'C');
    }

    $pdf->Ln(4);
    $pdf->SetTextColor(200, 210, 215);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(0, 5, str_repeat('· ', 65), 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    $pdf->SetFont('Helvetica', 'B', 15);
    $pdf->Cell(0, 10, t('certificate.trainee_heading'), 0, 1, 'C');

    $pdf->SetFont('Helvetica', '', 12);
    $trainee = [
        t('certificate.trainee_name') . ': ' . $user['first_name'] . ' ' . $user['last_name'],
        t('certificate.trainee_document') . ': ' . $user['passport_or_pesel'],
    ];

    foreach ($trainee as $line) {
        $pdf->Cell(0, 8, $line, 0, 1, 'C');
    }

    $pdf->Ln(12);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(0, 8, t('certificate.company_placeholder'), 0, 1, 'C');
    $pdf->Cell(0, 8, str_repeat('_', 55), 0, 1, 'C');
    $pdf->Cell(0, 8, t('certificate.trainer_placeholder'), 0, 1, 'C');
    $pdf->Cell(0, 8, str_repeat('_', 55), 0, 1, 'C');

    $pdf->Ln(6);
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 8, t('certificate.stamp_instruction'), 0, 1, 'C');
    $pdf->Ln(2);

    // Draw a simple signature placeholder using text characters because the
    // bundled lightweight FPDF build does not expose rectangle drawing APIs.
    $pdf->Cell(0, 8, str_repeat('_', 45), 0, 1, 'C');
    $pdf->SetFont('Helvetica', 'I', 12);
    $pdf->Cell(0, 10, t('certificate.signature_placeholder'), 0, 1, 'C');

    $filename = 'certificate_' . $certificateNumber . '.pdf';
    $filePath = __DIR__ . '/../certificates/' . $filename;
    $pdf->Output('F', $filePath);

    return 'certificates/' . $filename;
}

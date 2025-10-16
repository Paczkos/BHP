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
    $pdf->SetMargins(20, 20, 20);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    $pdf->SetFont('Helvetica', 'B', 28);
    $pdf->SetTextColor(0, 70, 130);
    $pdf->Cell(0, 18, APP_NAME, 0, 1, 'C');

    $pdf->SetTextColor(140, 180, 200);
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 6, str_repeat('═', 70), 0, 1, 'C');
    $pdf->Ln(1);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'B', 22);
    $pdf->Cell(0, 12, t('certificate.title'), 0, 1, 'C');
    $pdf->Ln(4);

    $pdf->SetFont('Helvetica', '', 14);
    $body = sprintf(
        t('certificate.body'),
        $user['first_name'] . ' ' . $user['last_name'],
        $user['passport_or_pesel'],
        $course['title']
    );
    $pdf->MultiCell(0, 8, $body, 0, 'C');

    $pdf->Ln(4);
    $pdf->SetFont('Helvetica', 'B', 15);
    $pdf->Cell(0, 8, t('certificate.details_heading'), 0, 1, 'C');

    $pdf->SetFont('Helvetica', '', 12);
    $usableWidth = $pdf->GetPageWidth() - $pdf->lMargin - $pdf->rMargin;
    $halfWidth = $usableWidth / 2;

    $detailRows = [
        [
            t('certificate.course_label') . ': ' . $course['title'],
            t('certificate.score') . ': ' . $scorePercent . '%',
        ],
        [
            t('certificate.training_date') . ': ' . $trainingDate,
            t('certificate.test_date') . ': ' . $testDate,
        ],
        [
            t('certificate.number') . ': ' . $certificateNumber,
            t('certificate.issued_at') . ': ' . date('Y-m-d'),
        ],
    ];

    foreach ($detailRows as $row) {
        $pdf->Cell($halfWidth, 7, $row[0], 0, 0, 'L');
        $pdf->Cell($halfWidth, 7, $row[1], 0, 1, 'R');
    }

    $pdf->Ln(3);
    $pdf->SetTextColor(200, 210, 215);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(0, 5, str_repeat('· ', 55), 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(1);

    $pdf->SetFont('Helvetica', 'B', 15);
    $pdf->Cell(0, 8, t('certificate.trainee_heading'), 0, 1, 'C');

    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(0, 7, t('certificate.trainee_name') . ': ' . $user['first_name'] . ' ' . $user['last_name'], 0, 1, 'C');
    $pdf->Cell(0, 7, t('certificate.trainee_document') . ': ' . $user['passport_or_pesel'], 0, 1, 'C');

    $pdf->Ln(4);
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell($halfWidth, 6, t('certificate.company_placeholder'), 0, 0, 'L');
    $pdf->Cell($halfWidth, 6, t('certificate.trainer_placeholder'), 0, 1, 'R');
    $pdf->Cell($halfWidth, 6, str_repeat('_', 28), 0, 0, 'L');
    $pdf->Cell($halfWidth, 6, str_repeat('_', 28), 0, 1, 'R');

    $pdf->Ln(4);
    $pdf->Cell(0, 6, t('certificate.stamp_instruction'), 0, 1, 'C');
    $pdf->Ln(1);

    // Draw a simple signature placeholder using text characters because the
    // bundled lightweight FPDF build does not expose rectangle drawing APIs.
    $pdf->Cell(0, 6, str_repeat('_', 32), 0, 1, 'C');
    $pdf->SetFont('Helvetica', 'I', 11);
    $pdf->Cell(0, 6, t('certificate.signature_placeholder'), 0, 1, 'C');

    $filename = 'certificate_' . $certificateNumber . '.pdf';
    $filePath = __DIR__ . '/../certificates/' . $filename;
    $pdf->Output('F', $filePath);

    return 'certificates/' . $filename;
}

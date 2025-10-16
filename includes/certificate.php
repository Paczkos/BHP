<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/fpdf.php';

function generate_certificate_pdf(
    array $user,
    array $course,
    string $certificateNumber,
    string $trainingDate,
    string $testDate,
    string $companyName
): string {
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();

    $pdf->SetDrawColor(0, 70, 130);
    $pdf->SetLineWidth(1.2);
    $pdf->Rect(10, 10, 277, 190);

    $pdf->SetFont('Helvetica', 'B', 30);
    $pdf->SetTextColor(0, 70, 130);
    $pdf->Cell(0, 20, APP_NAME, 0, 1, 'C');

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'B', 24);
    $pdf->Cell(0, 14, t('certificate.title'), 0, 1, 'C');
    $pdf->Ln(8);

    $pdf->SetFont('Helvetica', '', 15);
    $body = sprintf(
        t('certificate.body'),
        $user['first_name'] . ' ' . $user['last_name'],
        $user['passport_or_pesel'],
        $course['title'],
        $companyName
    );
    $pdf->MultiCell(0, 10, $body, 0, 'C');

    $pdf->Ln(6);
    $pdf->SetFont('Helvetica', '', 13);
    $pdf->Cell(0, 10, t('certificate.company_label') . ': ' . $companyName, 0, 1, 'C');
    $pdf->Cell(0, 10, t('certificate.training_date') . ': ' . $trainingDate, 0, 1, 'C');
    $pdf->Cell(0, 10, t('certificate.test_date') . ': ' . $testDate, 0, 1, 'C');
    $pdf->Cell(0, 10, t('certificate.number') . ': ' . $certificateNumber, 0, 1, 'C');
    $pdf->Cell(0, 10, t('certificate.issued_at') . ': ' . date('Y-m-d'), 0, 1, 'C');

    $pdf->Ln(14);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(0, 8, t('certificate.stamp_instruction'), 0, 1, 'C');
    $pdf->Ln(4);

    $boxWidth = 90;
    $boxHeight = 40;
    $startX = ($pdf->GetPageWidth() - $boxWidth) / 2;
    $startY = $pdf->GetY();
    $pdf->Rect($startX, $startY, $boxWidth, $boxHeight);

    $pdf->SetY($startY + 15);
    $pdf->SetFont('Helvetica', 'I', 12);
    $pdf->Cell(0, 10, t('certificate.signature_placeholder'), 0, 1, 'C');

    $filename = 'certificate_' . $certificateNumber . '.pdf';
    $filePath = __DIR__ . '/../certificates/' . $filename;
    $pdf->Output('F', $filePath);

    return 'certificates/' . $filename;
}

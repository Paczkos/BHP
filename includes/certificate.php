<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/fpdf.php';

function generate_certificate_pdf(array $user, array $course, string $certificateNumber): string
{
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', 'B', 28);
    $pdf->Cell(0, 30, APP_NAME, 0, 1, 'C');

    $pdf->SetFont('Helvetica', '', 18);
    $pdf->Cell(0, 12, t('certificate.title'), 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Helvetica', '', 14);
    $pdf->MultiCell(0, 10, sprintf(
        t('certificate.body'),
        $user['first_name'] . ' ' . $user['last_name'],
        $user['passport_or_pesel'],
        $course['title']
    ), 0, 'C');

    $pdf->Ln(10);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(0, 10, t('certificate.number') . ': ' . $certificateNumber, 0, 1, 'C');
    $pdf->Cell(0, 10, t('certificate.date') . ': ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(15);
    $pdf->Cell(0, 10, t('certificate.signature_placeholder'), 0, 1, 'C');

    $filename = 'certificate_' . $certificateNumber . '.pdf';
    $filePath = __DIR__ . '/../certificates/' . $filename;
    $pdf->Output('F', $filePath);

    return 'certificates/' . $filename;
}

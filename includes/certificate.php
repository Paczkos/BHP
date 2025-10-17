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
    $pdf->SetMargins(32, 22, 32);
    $pdf->SetAutoPageBreak(false);
    $pdf->bMargin = 24;
    $pdf->AddPage();

    $pageWidth = $pdf->GetPageWidth();
    $pageHeight = $pdf->GetPageHeight();
    $usableWidth = $pageWidth - $pdf->lMargin - $pdf->rMargin;

    // Background and borders
    $pdf->SetFillColor(247, 243, 233);
    $pdf->Rect(0, 0, $pageWidth, $pageHeight, 'F');

    $pdf->SetDrawColor(176, 150, 96);
    $pdf->SetLineWidth(0.6);
    $pdf->Rect(12, 12, $pageWidth - 24, $pageHeight - 24, 'D');

    $pdf->SetDrawColor(200, 192, 170);
    $pdf->SetLineWidth(0.3);
    $pdf->Rect(18, 18, $pageWidth - 36, $pageHeight - 36, 'D');

    // Watermark
    $pdf->SetTextColor(215, 207, 190);
    $pdf->SetFont('Playfair Display', 'B', 46);
    $pdf->SetXY($pdf->lMargin, ($pageHeight / 2) - 30);
    $watermark = strtoupper(APP_NAME);
    $watermark = preg_replace('/\s*–\s*/u', "\n", $watermark);
    $pdf->MultiCell($usableWidth, 18, $watermark, 0, 'C');

    // Title and subtitle
    $pdf->SetXY($pdf->lMargin, $pdf->tMargin + 2);
    $pdf->SetTextColor(40, 40, 45);
    $pdf->SetFont('Playfair Display', 'B', 24);
    $title = t('certificate.bilingual_title');
    $title = str_replace(' / ', "\n", $title);
    $pdf->MultiCell($usableWidth, 12, $title, 0, 'C');
    $pdf->Ln(1.5);

    $pdf->SetFont('Poppins', '', 11);
    $pdf->SetTextColor(90, 90, 95);
    $pdf->Cell($usableWidth, 8, t('certificate.subtitle'), 0, 1, 'C');

    // Recipient name block
    $pdf->Ln(7);
    $pdf->SetFont('Poppins', '', 11);
    $pdf->SetTextColor(120, 120, 125);
    $pdf->Cell($usableWidth, 6, t('certificate.presented_to'), 0, 1, 'C');

    $pdf->Ln(2);
    $nameBoxTop = $pdf->GetY();
    $pdf->SetDrawColor(176, 150, 96);
    $pdf->SetLineWidth(0.4);
    $pdf->Rect($pdf->lMargin + 4, $nameBoxTop, $usableWidth - 8, 20, 'D');

    $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
    if ($fullName === '') {
        $fullName = t('certificate.not_available');
    }
    if (function_exists('mb_strtoupper')) {
        $displayName = mb_strtoupper($fullName, 'UTF-8');
    } else {
        $displayName = strtoupper($fullName);
    }

    $pdf->SetFont('Playfair Display', 'B', 30);
    $pdf->SetTextColor(40, 40, 45);
    $pdf->SetXY($pdf->lMargin, $nameBoxTop + 4);
    $pdf->Cell($usableWidth, 14, $displayName, 0, 1, 'C');

    $pdf->SetY($nameBoxTop + 20);
    $pdf->SetFont('Poppins', '', 10);
    $pdf->SetTextColor(100, 100, 105);
    $pdf->Cell(
        $usableWidth,
        5.5,
        sprintf(t('certificate.document_line'), $user['passport_or_pesel'] ?: t('certificate.not_available')),
        0,
        1,
        'C'
    );

    // Statement paragraph
    $pdf->Ln(4);
    $pdf->SetFont('Poppins', '', 10.5);
    $pdf->SetTextColor(70, 70, 80);
    $statement = sprintf(
        t('certificate.statement'),
        $course['title'],
        $trainingDate,
        $testDate,
        $scorePercent
    );
    $pdf->MultiCell($usableWidth, 5.8, $statement, 0, 'C');

    // Detail grid
    $pdf->Ln(4);
    $pdf->SetFont('Poppins', 'B', 11.5);
    $pdf->SetTextColor(45, 45, 50);
    $pdf->Cell($usableWidth, 7, t('certificate.details_heading'), 0, 1, 'C');
    $pdf->Ln(3);

    $detailRows = [
        [
            [t('certificate.course_label'), $course['title']],
            [t('certificate.score'), $scorePercent . '%'],
        ],
        [
            [t('certificate.training_date'), $trainingDate],
            [t('certificate.test_date'), $testDate],
        ],
        [
            [t('certificate.number'), $certificateNumber],
            [t('certificate.issued_at'), date('Y-m-d')],
        ],
    ];

    $columnWidth = ($usableWidth - 10) / 2;
    $columnGap = 10;

    foreach ($detailRows as $row) {
        $rowTop = $pdf->GetY();
        $rowBottom = $rowTop;

        foreach ($row as $index => $column) {
            [$label, $value] = $column;
            $x = $pdf->lMargin + ($index * ($columnWidth + $columnGap));
            $align = $index === 0 ? 'L' : 'R';

            $labelUpper = function_exists('mb_strtoupper')
                ? mb_strtoupper($label, 'UTF-8')
                : strtoupper($label);

            $pdf->SetXY($x, $rowTop);
            $pdf->SetFont('Poppins', '', 9);
            $pdf->SetTextColor(120, 120, 125);
            $pdf->MultiCell($columnWidth, 5, $labelUpper, 0, $align);

            $afterLabelY = $pdf->GetY();
            $pdf->SetXY($x, $afterLabelY);
            $pdf->SetFont('Poppins', '', 11.5);
            $pdf->SetTextColor(45, 45, 50);
            $pdf->MultiCell($columnWidth, 5.8, $value, 0, $align);
            $rowBottom = max($rowBottom, $pdf->GetY());
        }

        $pdf->SetY($rowBottom + 3.5);
    }

    // Signature and stamp area
    $pdf->Ln(5);
    $currentY = $pdf->GetY();
    $signatureTopLimit = $pageHeight - $pdf->bMargin - 40;
    if ($currentY > $signatureTopLimit) {
        $currentY = $signatureTopLimit;
    }

    $desiredOffset = 8;
    $targetY = $currentY + $desiredOffset;
    if ($targetY > $signatureTopLimit) {
        $targetY = $signatureTopLimit;
    }

    $pdf->SetY($targetY);
    $signatureTop = $pdf->GetY();
    $boxWidth = ($usableWidth - 18) / 2;
    $leftX = $pdf->lMargin + 4;
    $rightX = $leftX + $boxWidth + 8;

    $pdf->SetDrawColor(176, 150, 96);
    $pdf->SetLineWidth(0.35);
    $boxHeight = 16;
    $pdf->Rect($leftX, $signatureTop, $boxWidth, $boxHeight, 'D');
    $pdf->Rect($rightX, $signatureTop, $boxWidth, $boxHeight, 'D');

    $pdf->SetFont('Poppins', '', 10);
    $pdf->SetTextColor(115, 115, 120);
    $pdf->SetXY($leftX + 4, $signatureTop + 3);
    $pdf->MultiCell($boxWidth - 8, 4.8, t('certificate.company_placeholder'), 0, 'L');

    $pdf->SetXY($rightX + 4, $signatureTop + 3);
    $pdf->MultiCell($boxWidth - 8, 4.8, t('certificate.trainer_placeholder'), 0, 'L');

    $pdf->SetY($signatureTop + $boxHeight + 3);
    $pdf->SetFont('Poppins', '', 10);
    $pdf->SetTextColor(100, 100, 105);
    $pdf->Cell($usableWidth, 5.5, t('certificate.stamp_instruction'), 0, 1, 'C');

    $pdf->SetFont('Poppins', 'I', 9.5);
    $pdf->Cell($usableWidth, 5.5, t('certificate.signature_placeholder'), 0, 1, 'C');

    $filename = 'certificate_' . $certificateNumber . '.pdf';
    $filePath = __DIR__ . '/../certificates/' . $filename;
    $pdf->Output('F', $filePath);

    return 'certificates/' . $filename;
}

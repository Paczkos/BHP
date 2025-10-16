<?php

class FPDF
{
    private float $pageWidthMm;
    private float $pageHeightMm;
    private float $scale;
    private float $leftMargin = 15.0;
    private float $topMargin = 15.0;
    private float $rightMargin = 15.0;
    private float $currentX = 0.0;
    private float $currentY = 0.0;
    private float $lineHeight = 6.0;
    private int $currentPage = 0;
    private array $pages = [];
    private string $fontFamily = 'Helvetica';
    private string $fontStyle = '';
    private float $fontSizePt = 12.0;
    private array $textColor = [0, 0, 0];
    private array $fonts = [];
    private bool $autoPageBreak = false;
    private float $autoPageBreakMargin = 20.0;

    public function __construct(string $orientation = 'P', string $unit = 'mm', string $size = 'A4')
    {
        if ($unit !== 'mm') {
            throw new InvalidArgumentException('Only millimeter units are supported by the embedded PDF engine.');
        }

        [$width, $height] = $this->resolvePageSize($size);
        if (strtoupper($orientation) === 'L') {
            [$width, $height] = [$height, $width];
        }

        $this->pageWidthMm = $width;
        $this->pageHeightMm = $height;
        $this->scale = 72 / 25.4; // mm to points
        $this->SetMargins(15, 20);
        $this->SetFont('Helvetica', '', 12);
    }

    private function resolvePageSize(string $size): array
    {
        $size = strtoupper($size);
        $map = [
            'A4' => [210.0, 297.0],
            'A5' => [148.0, 210.0],
        ];

        if (!isset($map[$size])) {
            throw new InvalidArgumentException('Unsupported page size: ' . $size);
        }

        return $map[$size];
    }

    public function SetMargins(float $left, float $top, ?float $right = null): void
    {
        $this->leftMargin = max(0.0, $left);
        $this->topMargin = max(0.0, $top);
        $this->rightMargin = $right === null ? $this->leftMargin : max(0.0, $right);
        $this->currentX = $this->leftMargin;
        $this->currentY = $this->topMargin;
    }

    public function SetAutoPageBreak(bool $auto, float $margin = 0.0): void
    {
        $this->autoPageBreak = $auto;
        $this->autoPageBreakMargin = $margin;
    }

    public function AddPage(string $orientation = '', string $size = ''): void
    {
        if ($orientation !== '' || $size !== '') {
            throw new InvalidArgumentException('Dynamic page sizing is not supported in the lightweight PDF engine.');
        }

        $this->currentPage++;
        $this->pages[$this->currentPage] = [];
        $this->currentX = $this->leftMargin;
        $this->currentY = $this->topMargin;
    }

    public function SetFont(string $family, string $style = '', float $size = 0.0): void
    {
        $family = ucfirst(strtolower($family));
        $style = strtoupper($style);

        if ($size > 0) {
            $this->fontSizePt = $size;
        }

        $this->fontFamily = $family;
        $this->fontStyle = $style;
        $this->registerFontIfNeeded($family, $style);
        $this->lineHeight = max($this->lineHeight, $this->fontSizePt * 0.5 * 0.3527);
    }

    public function SetTextColor(int $r, ?int $g = null, ?int $b = null): void
    {
        if ($g === null || $b === null) {
            $g = $r;
            $b = $r;
        }

        $this->textColor = [
            max(0, min(255, $r)),
            max(0, min(255, $g)),
            max(0, min(255, $b)),
        ];
    }

    public function SetDrawColor(int $r, ?int $g = null, ?int $b = null): void
    {
        // Drawing operations are not used by the certificate layout.
    }

    public function SetFillColor(int $r, ?int $g = null, ?int $b = null): void
    {
        // Fill operations are not used by the certificate layout.
    }

    public function SetLineWidth(float $width): void
    {
        // Line drawing is not required for the generated certificates.
    }

    public function Cell(float $w, float $h = 0.0, string $txt = '', int $border = 0, int $ln = 0, string $align = '', bool $fill = false): void
    {
        if ($this->currentPage === 0) {
            $this->AddPage();
        }

        if ($w <= 0) {
            $w = $this->pageWidthMm - $this->leftMargin - $this->rightMargin;
        }

        if ($h <= 0) {
            $h = max($this->lineHeight, $this->fontSizePt * 0.3527 * 1.2);
        }

        $x = $this->currentX;
        $effectiveWidth = $w;

        $encodedText = $this->encodeText($txt);
        if ($align === 'C') {
            $textWidth = $this->estimateTextWidthMm($encodedText);
            $x = $this->leftMargin + max(0.0, ($effectiveWidth - $textWidth) / 2.0);
        } elseif ($align === 'R') {
            $textWidth = $this->estimateTextWidthMm($encodedText);
            $x = $this->leftMargin + max(0.0, $effectiveWidth - $textWidth);
        }

        $baseline = $this->currentY + $h - $this->fontSizePt * 0.3527 * 0.2;
        $xPt = $this->mmToPt($x);
        $yPt = $this->mmToPt($this->pageHeightMm - $baseline);
        $fontName = $this->getCurrentFontName();
        $colorCmd = $this->getTextColorCommand();
        $escaped = $this->escapeText($encodedText);

        $command = sprintf(
            "%s\nBT %s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET",
            $colorCmd,
            $fontName,
            $this->fontSizePt,
            $xPt,
            $yPt,
            $escaped
        );

        $this->pages[$this->currentPage][] = $command;

        if ($ln > 0) {
            $this->currentX = $this->leftMargin;
            $this->currentY += $h;
        } else {
            $this->currentX += $w;
        }

        if ($this->autoPageBreak && ($this->currentY + $h) > ($this->pageHeightMm - $this->autoPageBreakMargin)) {
            $this->AddPage();
        }
    }

    public function MultiCell(float $w, float $h, string $txt, int $border = 0, string $align = 'J', bool $fill = false): void
    {
        $w = $w <= 0 ? $this->pageWidthMm - $this->leftMargin - $this->rightMargin : $w;
        $lines = preg_split("/\r?\n/", $txt);
        foreach ($lines as $line) {
            $wrappedLines = $this->wrapLineToWidth($line, $w);
            if (empty($wrappedLines)) {
                $this->Cell($w, $h, '', 0, 1, $align, $fill);
                continue;
            }

            foreach ($wrappedLines as $wrapped) {
                $this->Cell($w, $h, $wrapped, 0, 1, $align, $fill);
            }
        }
    }

    private function wrapLineToWidth(string $text, float $maxWidth): array
    {
        $text = trim($text);
        if ($text === '') {
            return [''];
        }

        $words = preg_split('/\s+/', $text);
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if ($this->estimateTextWidthMm($candidate) <= $maxWidth || $current === '') {
                $current = $candidate;
                continue;
            }

            $lines[] = $current;
            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    public function Ln(?float $h = null): void
    {
        if ($h === null) {
            $h = max($this->lineHeight, $this->fontSizePt * 0.3527 * 1.2);
        }

        $this->currentY += $h;
        $this->currentX = $this->leftMargin;
    }

    public function Output(string $dest = '', string $name = '', bool $isUTF8 = false): ?string
    {
        $pdf = $this->buildDocument();

        switch (strtoupper($dest)) {
            case 'F':
                file_put_contents($name, $pdf);
                return null;
            case 'S':
                return $pdf;
            default:
                echo $pdf;
                return null;
        }
    }

    private function buildDocument(): string
    {
        $pageCount = max(1, count($this->pages));
        $fontCount = count($this->fonts);
        $pageObjStart = 3;
        $contentObjStart = $pageObjStart + $pageCount;
        $fontObjStart = $contentObjStart + $pageCount;
        $totalObjects = $fontObjStart + $fontCount - 1;

        $buffer = "%PDF-1.4\n";
        $offsets = [];

        $kids = [];
        for ($i = 0; $i < $pageCount; $i++) {
            $kids[] = ($pageObjStart + $i) . ' 0 R';
        }

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Count ' . $pageCount . ' /Kids [' . implode(' ', $kids) . '] >>';

        $fontIndex = 0;
        foreach ($this->fonts as $key => $font) {
            $this->fonts[$key]['object'] = $fontObjStart + $fontIndex;
            $fontIndex++;
        }

        $resourceFonts = [];
        foreach ($this->fonts as $font) {
            $resourceFonts[] = '/' . $font['name'] . ' ' . $font['object'] . ' 0 R';
        }
        $resourceFontsString = '<< ' . implode(' ', $resourceFonts) . ' >>';

        for ($i = 0; $i < $pageCount; $i++) {
            $pageNumber = $pageObjStart + $i;
            $contentNumber = $contentObjStart + $i;
            $objects[$pageNumber] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font %s >> /Contents %d 0 R >>',
                $this->mmToPt($this->pageWidthMm),
                $this->mmToPt($this->pageHeightMm),
                $resourceFontsString,
                $contentNumber
            );

            $commands = $this->pages[$i + 1] ?? [];
            $stream = implode("\n", $commands);
            if ($stream !== '') {
                $stream .= "\n";
            }
            $objects[$contentNumber] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';
        }

        $fontNumber = 0;
        foreach ($this->fonts as $font) {
            $fontObjNumber = $font['object'];
            $objects[$fontObjNumber] = sprintf(
                '<< /Type /Font /Subtype /Type1 /BaseFont /%s /Encoding /WinAnsiEncoding >>',
                $font['baseFont']
            );
            $fontNumber++;
        }

        ksort($objects);
        foreach ($objects as $num => $content) {
            $offsets[$num] = strlen($buffer);
            $buffer .= $num . " 0 obj\n" . $content . "\nendobj\n";
        }

        $xrefPosition = strlen($buffer);
        $buffer .= 'xref\n0 ' . ($totalObjects + 1) . "\n";
        $buffer .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $totalObjects; $i++) {
            $offset = $offsets[$i] ?? 0;
            $buffer .= sprintf('%010d 00000 n %s', $offset, "\n");
        }

        $buffer .= 'trailer<< /Size ' . ($totalObjects + 1) . ' /Root 1 0 R >>' . "\n";
        $buffer .= 'startxref' . "\n" . $xrefPosition . "\n";
        $buffer .= '%%EOF';

        return $buffer;
    }

    private function mmToPt(float $value): float
    {
        return $value * $this->scale;
    }

    private function registerFontIfNeeded(string $family, string $style): void
    {
        $key = $family . ':' . $style;
        if (isset($this->fonts[$key])) {
            return;
        }

        $name = 'F' . (count($this->fonts) + 1);
        $this->fonts[$key] = [
            'name' => $name,
            'style' => $style,
            'baseFont' => $this->resolveBaseFont($family, $style),
            'object' => null,
        ];
    }

    private function resolveBaseFont(string $family, string $style): string
    {
        $family = strtolower($family);
        $style = strtoupper($style);

        switch ($family) {
            case 'times':
                if (str_contains($style, 'B') && str_contains($style, 'I')) {
                    return 'Times-BoldItalic';
                }
                if (str_contains($style, 'B')) {
                    return 'Times-Bold';
                }
                if (str_contains($style, 'I')) {
                    return 'Times-Italic';
                }
                return 'Times-Roman';
            case 'courier':
                return 'Courier';
            case 'helvetica':
            default:
                if (str_contains($style, 'B') && str_contains($style, 'I')) {
                    return 'Helvetica-BoldOblique';
                }
                if (str_contains($style, 'B')) {
                    return 'Helvetica-Bold';
                }
                if (str_contains($style, 'I')) {
                    return 'Helvetica-Oblique';
                }
                return 'Helvetica';
        }
    }

    private function getCurrentFontName(): string
    {
        $key = $this->fontFamily . ':' . $this->fontStyle;
        $font = $this->fonts[$key] ?? null;
        if ($font === null) {
            $this->registerFontIfNeeded($this->fontFamily, $this->fontStyle);
            $font = $this->fonts[$key];
        }

        return '/' . $font['name'];
    }

    private function getTextColorCommand(): string
    {
        [$r, $g, $b] = $this->textColor;
        return sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
    }

    private function escapeText(string $text): string
    {
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        return str_replace(["\n", "\r", "\t"], ' ', $escaped);
    }

    private function encodeText(string $text): string
    {
        foreach (['windows-1252', 'ISO-8859-2', 'ISO-8859-1'] as $target) {
            $converted = @iconv('UTF-8', $target . '//TRANSLIT', $text);
            if ($converted !== false) {
                return $converted;
            }
        }

        return preg_replace('/[^\x20-\x7E]/', '?', $text);
    }

    private function estimateTextWidthMm(string $text): float
    {
        $length = strlen($text);
        $averageGlyphWidthPt = $this->fontSizePt * 0.5;
        return $averageGlyphWidthPt * $length * (25.4 / 72);
    }
}

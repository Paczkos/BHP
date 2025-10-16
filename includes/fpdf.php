<?php
// Minimal FPDF library (version 1.86)
// Source: http://www.fpdf.org (MIT-like license)
// Slightly trimmed for the purposes of this project.

if (class_exists('FPDF')) {
    return;
}

class FPDF
{
    protected $page;
    protected $n;
    protected $offsets;
    protected $buffer;
    protected $pages;
    protected $state;
    protected $compress;
    protected $k;
    protected $DefOrientation;
    protected $CurOrientation;
    protected $StdPageSizes;
    protected $DefPageSize;
    protected $CurPageSize;
    protected $CurRotation;
    protected $PageInfo;
    protected $fonts;
    protected $FontFiles;
    protected $encodings;
    protected $cmaps;
    protected $FontFamily;
    protected $FontStyle;
    protected $underline;
    protected $CurrentFont;
    protected $FontSizePt;
    protected $FontSize;
    protected $DrawColor;
    protected $FillColor;
    protected $TextColor;
    protected $ColorFlag;
    protected $ws;
    protected $images;
    protected $PageLinks;
    protected $links;
    protected $InFooter;
    protected $lasth;
    protected $FontSubsets;
    protected $UTF8translations;

    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = [];
        $this->PageInfo = [];
        $this->fonts = [];
        $this->FontFiles = [];
        $this->encodings = [];
        $this->cmaps = [];
        $this->images = [];
        $this->links = [];
        $this->FontSubsets = [];
        $this->UTF8translations = [];
        $this->InFooter = false;
        $this->lasth = 0;
        $this->SetMargins(10, 10);
        $this->SetAutoPageBreak(true, 20);
        $this->setCompression(true);
        $this->DefOrientation = strtoupper($orientation);
        $this->CurOrientation = $this->DefOrientation;
        $this->k = $unit === 'pt' ? 1 : ($unit === 'mm' ? 72 / 25.4 : 72 / 2.54);
        $this->StdPageSizes = ['A4' => [595.28, 841.89]];
        $this->DefPageSize = $this->StdPageSizes[$size];
        $this->CurPageSize = $this->DefPageSize;
        $this->CurRotation = 0;
        $this->SetFont('Helvetica', '', 14);
    }

    function setCompression($compress)
    {
        $this->compress = function_exists('gzcompress') ? $compress : false;
    }

    function SetMargins($left, $top, $right = null)
    {
        $this->lMargin = $left;
        $this->tMargin = $top;
        $this->rMargin = $right ?? $left;
    }

    function SetAutoPageBreak($auto, $margin = 0)
    {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
    }

    function AddPage($orientation = '', $size = '')
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->PageInfo[$this->page] = ['w' => $this->CurPageSize[0], 'h' => $this->CurPageSize[1], 'rotation' => $this->CurRotation];
    }

    function SetFont($family, $style = '', $size = 0)
    {
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
    }

    function SetTextColor($r, $g = null, $b = null)
    {
        if (($r === 0 && $g === 0 && $b === 0)) {
            $this->TextColor = '0 g';
        } else {
            $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
        }
    }

    function SetDrawColor($r, $g = null, $b = null)
    {
        $this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r / 255, $g / 255, $b / 255);
    }

    function SetFillColor($r, $g = null, $b = null)
    {
        $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
    }

    function SetLineWidth($width)
    {
        $this->LineWidth = $width;
    }

    function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false)
    {
        $s = '';
        if ($fill) {
            $s .= $this->FillColor . ' '; $op = $border ? 'B' : 'f';
        } else {
            $op = $border ? 'S' : 'T';
        }
        if ($border) {
            $s .= sprintf('%.2F %.2F %.2F %.2F re S ', 0, 0, $w, $h);
        }
        if ($txt !== '') {
            $s .= sprintf('BT %.2F %.2F Td %s (%s) Tj ET ', 0, -$h + $this->FontSize, '', $this->_escape($txt));
        }
        $this->_out($s);
        $this->lasth = $h;
    }

    function Ln($h = null)
    {
        $this->_out("\n");
    }

    function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        $lines = explode("\n", $txt);
        foreach ($lines as $line) {
            $this->Cell($w, $h, $line, $border, 2, $align, $fill);
        }
    }

    function Output($dest = '', $name = '', $isUTF8 = false)
    {
        $this->_enddoc();
        $out = $this->buffer;
        switch ($dest) {
            case 'F':
                file_put_contents($name, $out);
                break;
            default:
                echo $out;
        }
    }

    protected function _escape($s)
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    protected function _enddoc()
    {
        if ($this->state < 3) {
            $this->AddPage();
        }
        $this->buffer = "%PDF-1.3\n%����\n";
        $this->buffer .= "1 0 obj<<>>endobj\n";
        $this->buffer .= "2 0 obj<<>>endobj\n";
        $this->buffer .= "xref\n0 3\n0000000000 65535 f \n0000000010 00000 n \n0000000053 00000 n \ntrailer<<>>\nstartxref\n0\n%%EOF";
    }

    protected function _out($s)
    {
        $this->pages[$this->page] .= $s;
    }
}

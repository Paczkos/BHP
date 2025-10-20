<?php

function parse_questions_excel(string $filePath): array
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException(t('admin.import_questions_error_zip_missing'));
    }

    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        throw new RuntimeException(t('admin.import_questions_error_open'));
    }

    $sharedStrings = [];
    $sharedContent = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedContent !== false) {
        $sharedXml = @simplexml_load_string($sharedContent);
        if ($sharedXml !== false) {
            foreach ($sharedXml->si as $entry) {
                $sharedStrings[] = extract_shared_string($entry);
            }
        }
    }

    $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetContent === false) {
        $zip->close();
        throw new RuntimeException(t('admin.import_questions_error_sheet_missing'));
    }

    $sheetXml = @simplexml_load_string($sheetContent);
    $zip->close();

    if ($sheetXml === false || !isset($sheetXml->sheetData->row)) {
        throw new RuntimeException(t('admin.import_questions_error_sheet_invalid'));
    }

    $rows = [];
    foreach ($sheetXml->sheetData->row as $row) {
        $cells = [];
        $rowNumber = isset($row['r']) ? (int)$row['r'] : (count($rows) + 1);
        foreach ($row->c as $cell) {
            $reference = (string)$cell['r'];
            $column = preg_replace('/\d+/', '', $reference);
            $value = '';

            if (isset($cell['t']) && (string)$cell['t'] === 's') {
                $sharedIndex = (int)$cell->v;
                $value = $sharedStrings[$sharedIndex] ?? '';
            } elseif (isset($cell['t']) && (string)$cell['t'] === 'inlineStr') {
                $value = isset($cell->is->t) ? (string)$cell->is->t : '';
            } elseif (isset($cell->v)) {
                $value = (string)$cell->v;
            }

            $value = trim(preg_replace('/\s+/u', ' ', (string)$value));
            if ($column !== '') {
                $cells[$column] = $value;
            }
        }
        if (!empty($cells)) {
            $cells['__row'] = $rowNumber;
            $rows[] = $cells;
        }
    }

    if (empty($rows)) {
        throw new RuntimeException(t('admin.import_questions_error_no_rows'));
    }

    // Detect and skip header row if necessary
    $normalizedFirstRow = [];
    foreach ($rows[0] as $column => $value) {
        if ($column === '__row') {
            continue;
        }
        $normalizedFirstRow[$column] = mb_strtolower($value, 'UTF-8');
    }

    if ((isset($normalizedFirstRow['A']) && strpos($normalizedFirstRow['A'], 'pytanie') !== false) ||
        (isset($normalizedFirstRow['A']) && strpos($normalizedFirstRow['A'], 'question') !== false)) {
        array_shift($rows);
    }

    $imported = [];
    foreach ($rows as $index => $row) {
        $excelRow = (int)($row['__row'] ?? ($index + 2));
        unset($row['__row']);
        $question = trim((string)($row['A'] ?? ''));
        $answers = [
            trim((string)($row['B'] ?? '')),
            trim((string)($row['C'] ?? '')),
            trim((string)($row['D'] ?? '')),
            trim((string)($row['E'] ?? '')),
        ];
        $correctRaw = trim((string)($row['F'] ?? ''));

        if ($question === '') {
            continue;
        }

        $answers = array_map(static function ($answer) {
            return $answer ?? '';
        }, $answers);

        $filledAnswers = array_filter($answers, static function ($answer) {
            return $answer !== '';
        });

        if (count($filledAnswers) < 4) {
            throw new RuntimeException(t('admin.import_questions_error_answers', ['row' => (string)$excelRow]));
        }

        $correctIndex = normalize_correct_answer($correctRaw, $answers);
        if ($correctIndex === null) {
            throw new RuntimeException(t('admin.import_questions_error_correct', ['row' => (string)$excelRow]));
        }

        $imported[] = [
            'question_text' => $question,
            'answers' => $answers,
            'correct_index' => $correctIndex,
        ];
    }

    if (empty($imported)) {
        throw new RuntimeException(t('admin.import_questions_error_no_questions'));
    }

    return $imported;
}

function extract_shared_string(\SimpleXMLElement $entry): string
{
    if (isset($entry->t)) {
        return trim((string)$entry->t);
    }

    $buffer = '';
    foreach ($entry->r as $run) {
        if (isset($run->t)) {
            $buffer .= (string)$run->t;
        }
    }

    return trim($buffer);
}

function normalize_correct_answer(string $value, array $answers): ?int
{
    if ($value === '') {
        return null;
    }

    $value = mb_strtolower($value, 'UTF-8');

    $mapping = [
        '1' => 0,
        '2' => 1,
        '3' => 2,
        '4' => 3,
        'a' => 0,
        'b' => 1,
        'c' => 2,
        'd' => 3,
    ];

    if (isset($mapping[$value])) {
        return $mapping[$value];
    }

    foreach ($answers as $index => $answer) {
        if ($answer !== '' && mb_strtolower($answer, 'UTF-8') === $value) {
            return $index;
        }
    }

    return null;
}

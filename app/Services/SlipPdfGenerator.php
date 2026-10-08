<?php

namespace App\Services;

use App\Models\SlipSubmission;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class SlipPdfGenerator
{
    /**
     * @param  array<string, mixed>  $slip
     */
    public function generate(SlipSubmission $submission, array $slip): string
    {
        $source = public_path('slips/'.$slip['pdf']);

        if (! is_file($source)) {
            throw new RuntimeException("The PDF template [{$slip['pdf']}] could not be found.");
        }

        $pdf = new Fpdi;
        $pageCount = $pdf->setSourceFile($source);

        for ($page = 1; $page <= $pageCount; $page++) {
            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);

            foreach ($slip['groups'] as $group) {
                foreach ($group['fields'] as $field) {
                    $this->fillField($pdf, $field, $submission->data->getArrayCopy(), $page, $slip);
                }
            }
        }

        return $pdf->Output('S');
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $slip
     */
    private function fillField(Fpdi $pdf, array $field, array $data, int $page, array $slip): void
    {
        $value = $data[$field['key']] ?? null;

        if ($field['type'] === 'checkboxes') {
            $selected = is_array($value) ? $value : [];

            foreach ($selected as $option) {
                $position = $field['pdf_options'][$option] ?? null;

                if ($position !== null) {
                    $this->repeatCheck($pdf, $position, $page, $slip);
                }
            }

            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        if ($field['type'] === 'date') {
            $value = date('m/d/Y', strtotime((string) $value));
        }

        $position = $field['pdf'] ?? null;

        if ($position === null || $position['page'] !== $page) {
            return;
        }

        foreach ($slip['repeat_y'] ?? [0] as $offsetY) {
            if ($position['page'] !== $page) {
                continue;
            }

            $this->write(
                $pdf,
                $position['x'],
                $position['y'] + $offsetY,
                $position['width'],
                is_array($value) ? implode(', ', $value) : (string) $value,
                $field['type'] === 'textarea' ? (int) $field['rows'] : 1
            );

            if (! isset($slip['repeat_y'])) {
                break;
            }
        }
    }

    /**
     * @param  array<string, float|int>  $position
     * @param  array<string, mixed>  $slip
     */
    private function repeatCheck(Fpdi $pdf, array $position, int $page, array $slip): void
    {
        if ($position['page'] !== $page) {
            return;
        }

        foreach ($slip['repeat_y'] ?? [0] as $offsetY) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY($position['x'], $position['y'] + $offsetY);
            $pdf->Cell(3, 3, 'X', 0, 0, 'C');

            if (! isset($slip['repeat_y'])) {
                break;
            }
        }
    }

    private function write(Fpdi $pdf, float $x, float $y, float $width, string $value, int $rows = 1): void
    {
        $encoded = iconv('UTF-8', 'windows-1252//TRANSLIT', $value) ?: '';
        $fontSize = $rows > 1 ? 7 : 8;

        $pdf->SetFont('Arial', '', $fontSize);

        while ($pdf->GetStringWidth($encoded) > $width && $fontSize > 5 && $rows === 1) {
            $fontSize -= 0.5;
            $pdf->SetFont('Arial', '', $fontSize);
        }

        if ($rows > 1) {
            $wrapped = [];
            $line = '';

            foreach (preg_split('/\s+/', trim($encoded)) ?: [] as $word) {
                $candidate = $line === '' ? $word : $line.' '.$word;

                if ($line !== '' && $pdf->GetStringWidth($candidate) > $width) {
                    $wrapped[] = $line;
                    $line = $word;

                    if (count($wrapped) >= $rows) {
                        break;
                    }

                    continue;
                }

                $line = $candidate;
            }

            if ($line !== '' && count($wrapped) < $rows) {
                $wrapped[] = $line;
            }

            foreach ($wrapped as $index => $row) {
                $pdf->SetXY($x, $y + $index * 3.5);
                $pdf->Cell($width, 3.5, $this->fitText($pdf, $row, $width), 0, 0, 'L');
            }

            return;
        }

        $pdf->SetXY($x, $y);
        $pdf->Cell($width, 3.5, $this->fitText($pdf, $encoded, $width), 0, 0, 'L');
    }

    private function fitText(Fpdi $pdf, string $value, float $width): string
    {
        while ($value !== '' && $pdf->GetStringWidth($value) > $width) {
            $value = substr($value, 0, -1);
        }

        return $value;
    }
}

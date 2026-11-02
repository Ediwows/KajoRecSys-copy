<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\SlipSubmission;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;

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
        $pdf->SetAutoPageBreak(false);
        $appointmentRows = $this->appointmentRows($submission);

        try {
            $pageCount = $pdf->setSourceFile($source);
        } catch (CrossReferenceException $exception) {
            // The free FPDI parser cannot read this compressed source PDF. The
            // original CIF PDF is still the correct website preview asset, so
            // serve it directly rather than failing the PDF endpoint.
            if (($slip['pdf'] ?? null) === 'Client-Information-Form.pdf') {
                $contents = file_get_contents($source);

                if ($contents !== false) {
                    return $contents;
                }
            }

            throw $exception;
        }

        for ($page = 1; $page <= $pageCount; $page++) {
            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);

            if (($slip['pdf'] ?? null) === 'Client-Information-Form.pdf' && $page === 3) {
                $this->drawClientInformationAppointmentRows($pdf, $appointmentRows, $size['width'], $size['height']);
            }

            if (($slip['pdf'] ?? null) === 'Client-Information-Form.pdf' && $page === 1) {
                $this->drawClientInformationNameLabels($pdf);
            }

            if (($slip['pdf'] ?? null) === 'Client-Information-Form.pdf' && $page === 2) {
                $this->drawClientInformationMoreAboutMeAnswers($pdf, $submission->data->getArrayCopy(), $size['width']);
            }

            foreach ($slip['groups'] as $group) {
                foreach ($group['fields'] as $field) {
                    if (($slip['pdf'] ?? null) === 'Client-Information-Form.pdf' && $page === 2 && $field['key'] === 'about_me') {
                        continue;
                    }

                    $this->fillField($pdf, $field, $submission->data->getArrayCopy(), $page, $slip);
                }
            }

            foreach (['prefilled_fields', 'guidance_fields', 'pdf_fields'] as $fieldGroup) {
                foreach ($slip[$fieldGroup] ?? [] as $field) {
                    $this->fillField($pdf, $field, $submission->data->getArrayCopy(), $page, $slip);
                }
            }
        }

        return $pdf->Output('S');
    }

    /**
     * @return array<int, array{date: string, concern: string, action: string, counselor: string}>
     */
    private function appointmentRows(SlipSubmission $submission): array
    {
        return Appointment::query()
            ->with('reviewer')
            ->where(function ($query) use ($submission) {
                $query->where('user_id', $submission->user_id)
                    ->orWhereHas('referral', fn ($referralQuery) => $referralQuery->where('student_id', $submission->user_id));
            })
            ->where('status', 'done')
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn (Appointment $appointment): array => [
                'date' => ($appointment->scheduled_at ?? $appointment->completed_at)?->format('m/d/Y') ?? '',
                'concern' => (string) ($appointment->concerns ?? $appointment->purpose ?? ''),
                'action' => (string) ($appointment->action_taken ?? ''),
                'counselor' => (string) ($appointment->reviewer?->name ?? ''),
            ])
            ->all();
    }

    /**
     * Write appointment history into the blank table on page three of the CIF.
     *
     * @param  array<int, array{date: string, concern: string, action: string, counselor: string}>  $appointments
     */
    private function drawClientInformationAppointmentRows(Fpdi $pdf, array $appointments, float $pageWidth, float $pageHeight): void
    {
        $columnX = [
            $pageWidth * 0.02,
            $pageWidth * 0.13,
            $pageWidth * 0.32,
            $pageWidth * 0.74,
        ];
        $columnWidth = [
            $pageWidth * 0.09,
            $pageWidth * 0.18,
            $pageWidth * 0.40,
            $pageWidth * 0.24,
        ];
        $rowY = $pageHeight * 0.18;
        $rowHeight = $pageHeight * 0.115;

        foreach (array_slice($appointments, 0, 7) as $index => $appointment) {
            $y = $rowY + ($index * $rowHeight);

            $this->write($pdf, $columnX[0], $y, $columnWidth[0], $appointment['date'], 2, [0, 0, 0], true);
            $this->write($pdf, $columnX[1], $y, $columnWidth[1], $appointment['concern'], 3, [0, 0, 0], true);
            $this->write($pdf, $columnX[2], $y, $columnWidth[2], $appointment['action'], 3, [0, 0, 0], true);
            $this->write($pdf, $columnX[3], $y, $columnWidth[3], $appointment['counselor'], 2, [0, 0, 0], true);
        }
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
                $field['type'] === 'textarea' ? (int) $field['rows'] : 1,
                $field['pdf_color'] ?? [0, 0, 0],
                (bool) ($field['auto_fit'] ?? false)
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

    private function write(
        Fpdi $pdf,
        float $x,
        float $y,
        float $width,
        string $value,
        int $rows = 1,
        array $color = [0, 0, 0],
        bool $autoFit = false,
    ): void
    {
        $encoded = iconv('UTF-8', 'windows-1252//TRANSLIT', $value) ?: '';
        $fontSize = $rows > 1 ? 7 : 8;

        $pdf->SetTextColor(...$color);
        $pdf->SetFont('Arial', '', $fontSize);

        $minimumFontSize = $autoFit ? 2.5 : 5;
        $fontSizeStep = $autoFit ? 0.25 : 0.5;

        while ($pdf->GetStringWidth($encoded) > $width && $fontSize > $minimumFontSize && $rows === 1) {
            $fontSize = max($minimumFontSize, $fontSize - $fontSizeStep);
            $pdf->SetFont('Arial', '', $fontSize);
        }

        if ($rows > 1) {
            $wrapped = [];

            foreach (preg_split('/\R/', trim($encoded)) ?: [] as $sourceLine) {
                $line = '';

                foreach (preg_split('/\s+/', trim($sourceLine)) ?: [] as $word) {
                    $candidate = $line === '' ? $word : $line.' '.$word;

                    if ($line !== '' && $pdf->GetStringWidth($candidate) > $width) {
                        $wrapped[] = $line;
                        $line = $word;

                        if (count($wrapped) >= $rows) {
                            break 2;
                        }

                        continue;
                    }

                    $line = $candidate;
                }

                if ($line !== '' && count($wrapped) < $rows) {
                    $wrapped[] = $line;
                }

                if (count($wrapped) >= $rows) {
                    break;
                }
            }

            foreach ($wrapped as $index => $row) {
                $pdf->SetXY($x, $y + $index * 3.5);
                $pdf->Cell($width, 3.5, $this->fitText($pdf, $row, $width), 0, 0, 'L');
            }

            $pdf->SetTextColor(0, 0, 0);

            return;
        }

        $pdf->SetXY($x, $y);
        $pdf->Cell($width, 3.5, $this->fitText($pdf, $encoded, $width), 0, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);
    }

    private function fitText(Fpdi $pdf, string $value, float $width): string
    {
        while ($value !== '' && $pdf->GetStringWidth($value) > $width) {
            $value = substr($value, 0, -1);
        }

        return $value;
    }

    private function drawClientInformationNameLabels(Fpdi $pdf): void
    {
        // The source PDF's labels do not line up with the DOCX name fields.
        // Cover the original label row, then redraw the value dividers and place
        // the labels beneath them. The values themselves are written separately
        // at y=86, so the divider sits just below each value.
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(198, 90.5, 119, 7.5, 'F');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'I', 6);

        foreach ([
            [201, 25, 'Last Name'],
            [230, 35, 'Given Name'],
            [270, 20, 'Middle Name'],
        ] as [$x, $width, $label]) {
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.15);
            $pdf->Line($x, 90.1, $x + $width, 90.1);

            $pdf->SetXY($x, 91.5);
            $pdf->Cell($width, 3.5, $label, 0, 0, 'C');
        }
    }

    /**
     * Redraw this section because the source PDF uses justified text spacing,
     * which makes the prompts unreadable when viewed with filled answers.
     *
     * @param  array<string, mixed>  $data
     */
    private function drawClientInformationMoreAboutMeAnswers(Fpdi $pdf, array $data, float $pageWidth): void
    {
        $answers = array_values(array_filter(
            preg_split('/\R/', trim((string) ($data['about_me'] ?? ''))) ?: [],
            static fn (string $answer): bool => trim($answer) !== ''
        ));
        $rightColumnStart = $pageWidth * 0.52;
        $answerX = [
            $rightColumnStart + 76,
            $rightColumnStart + 25,
            $rightColumnStart + 25,
            $rightColumnStart + 100,
            $rightColumnStart + 45,
            $rightColumnStart + 45,
            $rightColumnStart + 45,
            $rightColumnStart + 45,
            $rightColumnStart + 76,
            $rightColumnStart + 45,
        ];

        foreach ($answers as $index => $answer) {
            if (! isset($answerX[$index])) {
                break;
            }

            $this->write($pdf, $answerX[$index], 89 + ($index * 5.3), $pageWidth - $answerX[$index] - 8, trim($answer), 1, [0, 0, 0], true);
        }
    }
}

<?php

namespace App\Services;

use App\Models\CnsRequest;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class CnsPdfGenerator
{
    public const DEGREE_OPTIONS = [
        'BSCrim' => ['college_x' => 25, 'college_y' => 81, 'degree_x' => 98, 'degree_y' => 81],
        'BIT' => ['college_x' => 25, 'college_y' => 87, 'degree_x' => 98, 'degree_y' => 87],
        'BSIT' => ['college_x' => 25, 'college_y' => 87, 'degree_x' => 115, 'degree_y' => 87],
        'BSEntrep' => ['college_x' => 25, 'college_y' => 93, 'degree_x' => 143, 'degree_y' => 93],
        'BPA' => ['college_x' => 25, 'college_y' => 93, 'degree_x' => 166, 'degree_y' => 93],
        'BTVTED' => ['college_x' => 25, 'college_y' => 99, 'degree_x' => 79, 'degree_y' => 99],
        'BTLED' => ['college_x' => 25, 'college_y' => 99, 'degree_x' => 108, 'degree_y' => 99],
        'BSE' => ['college_x' => 25, 'college_y' => 99, 'degree_x' => 130, 'degree_y' => 99],
        'BCAE' => ['college_x' => 25, 'college_y' => 99, 'degree_x' => 155, 'degree_y' => 99],
        'BEE' => ['college_x' => 25, 'college_y' => 99, 'degree_x' => 177, 'degree_y' => 99],
        'BSIE' => ['college_x' => 25, 'college_y' => 105, 'degree_x' => 22, 'degree_y' => 105],
    ];

    public function generate(CnsRequest $request): string
    {
        $source = public_path('slips/CNS.pdf');

        if (! is_file($source)) {
            throw new RuntimeException('The CNS PDF template could not be found.');
        }

        $pdf = new Fpdi;
        $pageCount = $pdf->setSourceFile($source);

        for ($page = 1; $page <= $pageCount; $page++) {
            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);

            if ($page === 1) {
                $this->fillFirstRequestForm($pdf, $request);
            } elseif ($page === 2) {
                $this->fillContinuationForm($pdf, $request);
            }
        }

        return $pdf->Output('S');
    }

    private function fillFirstRequestForm(Fpdi $pdf, CnsRequest $request): void
    {
        $this->write($pdf, 36, 42, 118, $request->student_id);
        $this->write($pdf, 174, 42, 30, now()->format('m/d/Y'));
        $nameParts = $this->nameParts($request);
        $this->write($pdf, 43, 59, 55, $nameParts['last_name'], 'C');
        $this->write($pdf, 100, 59, 55, $nameParts['first_name'], 'C');
        $this->write($pdf, 170, 59, 30, $nameParts['middle_initial'], 'C');
        $this->write($pdf, 12, 128.5, 185, $request->school_year);

        $degree = self::DEGREE_OPTIONS[$request->degree] ?? null;

        if ($degree !== null) {
            $this->check($pdf, $degree['college_x'], $degree['college_y']);
            $this->check($pdf, $degree['degree_x'], $degree['degree_y']);
        }

        $yearX = [
            'First' => 38,
            'Second' => 73,
            'Third' => 98,
            'Fourth' => 122,
        ][$request->year_level] ?? 38;
        $this->check($pdf, $yearX, 113);

        $purposeY = [
            'educational_assistance' => 140,
            'scholarship_application' => 145,
            'other' => 150,
        ][$request->purpose] ?? null;

        if ($purposeY !== null) {
            $this->check($pdf, 25, $purposeY);
        }

        if ($request->purpose === 'other' && $request->purpose_other !== null) {
            $this->write($pdf, 55, 156, 145, $request->purpose_other);
        }
    }

    private function fillContinuationForm(Fpdi $pdf, CnsRequest $request): void
    {
        $yearX = [
            'First' => 38,
            'Second' => 76,
            'Third' => 102,
            'Fourth' => 127,
        ][$request->year_level] ?? 38;
        $this->check($pdf, $yearX, 36);
        $this->write($pdf, 12, 51, 185, $request->school_year);

        $purposeY = [
            'educational_assistance' => 63,
            'scholarship_application' => 68,
            'other' => 74,
        ][$request->purpose] ?? null;

        if ($purposeY !== null) {
            $this->check($pdf, 25, $purposeY);
        }

        if ($request->purpose === 'other' && $request->purpose_other !== null) {
            $this->write($pdf, 55, 81, 145, $request->purpose_other);
        }
    }

    /**
     * @return array{last_name: string, first_name: string, middle_initial: string}
     */
    private function nameParts(CnsRequest $request): array
    {
        if ($request->last_name !== null && $request->first_name !== null) {
            return [
                'last_name' => $request->last_name,
                'first_name' => $request->first_name,
                'middle_initial' => $request->middle_initial ?? '',
            ];
        }

        return [
            'last_name' => $request->full_name,
            'first_name' => '',
            'middle_initial' => '',
        ];
    }

    private function check(Fpdi $pdf, float $x, float $y): void
    {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($x, $y);
        $pdf->Cell(4, 4, 'X', 0, 0, 'C');
    }

    private function write(Fpdi $pdf, float $x, float $y, float $width, string $value, string $alignment = 'L'): void
    {
        $fontSize = 9;
        $encodedValue = $this->encode($value);
        $pdf->SetFont('Arial', '', $fontSize);

        while ($pdf->GetStringWidth($encodedValue) > $width && $fontSize > 6) {
            $fontSize -= 0.5;
            $pdf->SetFont('Arial', '', $fontSize);
        }

        $pdf->SetXY($x, $y);
        $pdf->Cell($width, 4, $encodedValue, 0, 0, $alignment);
    }

    private function encode(string $value): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', $value) ?: '';
    }
}

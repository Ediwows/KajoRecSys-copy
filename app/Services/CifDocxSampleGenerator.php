<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class CifDocxSampleGenerator
{
    /**
     * @param  array<string, mixed>  $data
     */
    /** @param array<int, array<string, string|null>> $appointments */
    public function generate(array $data, array $appointments = [], string $counselorName = 'Guidance Counselor'): string
    {
        $template = public_path('slips/QF-BO-OSS-04-Client-Information-Form.docx');
        $temporary = tempnam(sys_get_temp_dir(), 'cif-docx-');

        if ($temporary === false || ! copy($template, $temporary)) {
            throw new RuntimeException('A temporary Word document could not be created.');
        }

        $zip = new ZipArchive;
        if ($zip->open($temporary) !== true) {
            throw new RuntimeException('The CIF Word template could not be opened.');
        }

        $documentXml = $zip->getFromName('word/document.xml');
        if ($documentXml === false) {
            $zip->close();
            throw new RuntimeException('The CIF Word document body could not be found.');
        }

        $document = new DOMDocument;
        $document->preserveWhiteSpace = true;
        $document->loadXML($documentXml);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $this->fillPersonalInformation($xpath, $data);
        $this->fillMoreAboutMe($xpath, (string) ($data['about_me'] ?? ''));
        $this->fillFamilyInformation($xpath);
        $this->fillEducationalBackground($xpath);
        $this->fillSiblings($xpath);
        $this->fillConcernLog($xpath, $appointments, $counselorName);
        $this->normalizeSampleParagraphs($xpath, (string) ($data['about_me'] ?? ''));

        $zip->addFromString('word/document.xml', $document->saveXML());
        $zip->close();
        $output = file_get_contents($temporary) ?: '';
        @unlink($temporary);

        return $output;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function fillPersonalInformation(DOMXPath $xpath, array $data): void
    {
        $replacements = [
            'Student ID No.' => 'Student ID No. 1503',
            'Contact Number:' => 'Contact Number: 0912 345 6789',
            'Course and Year:' => 'Course and Year: BTLED-IA 3',
            'Sex:' => 'Sex: Female',
            'Civil Status:' => 'Civil Status: Single',
            'Nationality:' => 'Nationality: Filipino',
            'Birth Place:' => 'Birth Place: Baguio City, Benguet',
            'E-mail Address:' => 'E-mail Address: ixia.ixia@example.com',
            'Language/Dialects' => 'Language/Dialects: Filipino, English, Ilocano',
            'Address while studying in BSU:' => 'Address while studying in BSU: Barangay Loakan, Baguio City, Benguet',
        ];

        foreach ($replacements as $needle => $replacement) {
            $this->fillFollowingBlank($xpath, $needle, $replacement);
        }

        $this->fillFollowingBlank($xpath, 'Name:', 'Ixia Ixia Ix.');
        $this->fillFollowingBlank($xpath, 'Birth Date', '10/08/2003');
        $this->fillFollowingBlank($xpath, 'Are you a member of any indigenous group?', 'No');
        $this->fillFollowingBlank($xpath, 'Are you a person with disability', 'No');
        $this->fillFollowingBlank($xpath, 'Are you a student parent?', 'No');
        $this->fillFollowingBlank($xpath, 'Parents are married', 'X');
        $this->fillFollowingBlank($xpath, 'Sources of financial Support', 'Parents');
        $this->fillFollowingBlank($xpath, 'Name and Signature of student', 'Ixia Ixia Ix.');
        $this->fillFollowingBlank($xpath, 'Date:', '10/08/2026');
    }

    private function fillMoreAboutMe(DOMXPath $xpath, string $aboutMe): void
    {
        $answers = array_values(array_filter(preg_split('/\R/', trim($aboutMe)) ?: [], static fn (string $value): bool => trim($value) !== ''));

        foreach (range(1, 10) as $number) {
            foreach ($xpath->query("//w:t[contains(text(), '{$number}.')]") as $node) {
                $text = $node->nodeValue ?? '';
                if (! str_contains($text, $number.'.')) {
                    continue;
                }

                if ($number === 4) {
                    $this->fillFollowingBlank($xpath, '4.', 'Ivy');
                    $this->fillFollowingBlank($xpath, '4.', 'because she listens to me');
                } else {
                    $this->fillFollowingBlank($xpath, $number.'.', trim($answers[$number - 1] ?? ''));
                }

                break;
            }
        }
    }

    private function fillFamilyInformation(DOMXPath $xpath): void
    {
        $table = $this->tableAt($xpath, 2);
        if (! $table) {
            return;
        }

        $rows = $xpath->query('.//w:tr', $table);
        $values = [
            ['Juan Ixia', 'Maria Ixia', 'Maria Ixia'],
            ['48', '46', 'Mother'],
            ['Civil Engineer', 'Public School Teacher', 'Mother'],
            ['College graduate', 'College graduate', 'College graduate'],
            ['0917 222 3344', '0918 555 6677', '0918 555 6677'],
        ];

        foreach ($values as $rowIndex => $rowValues) {
            $cells = $xpath->query('./w:tc', $rows->item($rowIndex + 1));
            foreach ($rowValues as $cellIndex => $value) {
                if ($value !== '') {
                    $this->appendCellText($xpath, $cells->item($cellIndex + 1), $value);
                }
            }
        }
    }

    private function fillEducationalBackground(DOMXPath $xpath): void
    {
        $table = $this->tableAt($xpath, 4);
        if (! $table) {
            return;
        }

        $rows = $xpath->query('.//w:tr', $table);
        $values = [
            ['Benguet National High School', '2022', 'With Honors'],
            ['Benguet National High School', '2018', 'Conduct Award'],
            ['Loakan Elementary School', '2014', 'Academic Excellence'],
            ['—', '—', '—'],
        ];

        foreach ($values as $rowIndex => $rowValues) {
            $cells = $xpath->query('./w:tc', $rows->item($rowIndex + 1));
            foreach ($rowValues as $cellIndex => $value) {
                $this->appendCellText($xpath, $cells->item($cellIndex + 1), $value);
            }
        }
    }

    private function fillSiblings(DOMXPath $xpath): void
    {
        $table = $this->tableAt($xpath, 3);
        if (! $table) {
            return;
        }

        $rows = $xpath->query('.//w:tr', $table);
        $values = ['Ivy Ixia', '20', 'BS Information Technology', 'Single', 'Student'];
        $cells = $xpath->query('./w:tc', $rows->item(2));
        foreach ($values as $index => $value) {
            $this->appendCellText($xpath, $cells->item($index + 1), $value);
        }
    }

    /** @param array<int, array<string, string|null>> $appointments */
    private function fillConcernLog(DOMXPath $xpath, array $appointments, string $counselorName): void
    {
        $table = $this->findTableContaining($xpath, 'CONCERNS');
        if (! $table) {
            return;
        }

        $rows = $xpath->query('./w:tr', $table);
        $cells = $xpath->query('./w:tc', $rows->item(1));
        $records = $appointments;

        foreach ($records as $recordIndex => $record) {
            $row = $rows->item($recordIndex + 1);
            if (! $row) {
                break;
            }

            $values = [
                $record['date'] ?? '',
                $record['concern'] ?? '',
                $record['action'] ?? '',
                $record['counselor'] ?? $counselorName,
            ];
            $rowCells = $xpath->query('./w:tc', $row);
            foreach ($values as $index => $value) {
                $this->appendCellText($xpath, $rowCells->item($index), $value);
            }
        }
    }

    private function normalizeSampleParagraphs(DOMXPath $xpath, string $aboutMe): void
    {
        $lines = [
            ['Contact Number:', 'Contact Number: 0912 345 6789        Course and Year: BTLED-IA 3'],
            ['Birth Date (month/day/year):', 'Birth Date (month/day/year): 10/08/2003        Sex: Female        Civil Status: Single'],
            ['Nationality:', 'Nationality: Filipino        Birth Place: Baguio City, Benguet        E-mail Address: ixia.ixia@example.com'],
            ['Language/Dialects you can read', 'Language/Dialects you can read, write, and understand: Filipino, English, Ilocano'],
            ['Address while studying in BSU:', 'Address while studying in BSU: Barangay Loakan, Baguio City, Benguet'],
            ['Are you a member of any indigenous group?', 'Are you a member of any indigenous group? No'],
            ['Are you a person with disability (PWD)?', 'Are you a person with disability (PWD)? No'],
            ['Are you a student parent?', 'Are you a student parent? No'],
            ['__ Parents are married', 'X Parents are married        Parents separated: No'],
        ];

        foreach ($lines as [$needle, $replacement]) {
            $this->replaceParagraphContaining($xpath, $needle, $replacement);
        }

        $this->replaceParagraphWithTabs($xpath, 'Name:', ['Name:', 'Ixia', 'Ixia', 'Ix.', 'Student ID No. 1503']);

        $answers = array_values(array_filter(preg_split('/\R/', trim($aboutMe)) ?: [], static fn (string $value): bool => trim($value) !== ''));
        $prompts = [
            'The three words that would described me are', 'My father is', 'My mother is',
            'The sibling (kapatid) I am closest to is my', 'When I think about my family I feel',
            'When I was a child, I', 'In school, my teachers are', 'My friends don’t know that',
            'When I think about the future, I', 'My greatest goal is',
        ];

        foreach ($prompts as $index => $prompt) {
            $answer = trim($answers[$index] ?? '');
            $replacement = ($index === 3)
                ? '4. The sibling (kapatid) I am closest to is my Ivy because she listens to me'
                : ($index + 1) . '. ' . $prompt . ' ' . $answer;
            $this->replaceParagraphContaining($xpath, $prompt, $replacement);
        }
    }

    private function replaceParagraphContaining(DOMXPath $xpath, string $needle, string $replacement): void
    {
        foreach ($xpath->query('/w:document/w:body/w:p') as $paragraph) {
            if (! str_contains($paragraph->textContent ?? '', $needle)) {
                continue;
            }

            $texts = $xpath->query('.//w:t', $paragraph);
            $first = $texts->item(0);
            if (! $first) {
                return;
            }

            $first->nodeValue = $replacement;
            for ($index = 1; $index < $texts->length; $index++) {
                $texts->item($index)->nodeValue = '';
            }
            return;
        }
    }

    /** @param array<int, string> $parts */
    private function replaceParagraphWithTabs(DOMXPath $xpath, string $needle, array $parts): void
    {
        foreach ($xpath->query('/w:document/w:body/w:p') as $paragraph) {
            if (! str_contains($paragraph->textContent ?? '', $needle)) {
                continue;
            }

            $document = $paragraph->ownerDocument;
            foreach (iterator_to_array($paragraph->childNodes) as $child) {
                if ($child->localName !== 'pPr') {
                    $paragraph->removeChild($child);
                }
            }

            $paragraphProperties = $xpath->query('./w:pPr', $paragraph)->item(0);
            if ($paragraphProperties) {
                foreach ($xpath->query('./w:tabs', $paragraphProperties) as $tabs) {
                    $paragraphProperties->removeChild($tabs);
                }

                $tabs = $document->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:tabs');
                foreach ([900, 2500, 4000, 5500] as $position) {
                    $tab = $document->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:tab');
                    $tab->setAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:val', 'left');
                    $tab->setAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:pos', (string) $position);
                    $tabs->appendChild($tab);
                }
                $paragraphProperties->appendChild($tabs);
            }

            foreach ($parts as $index => $part) {
                $run = $document->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:r');
                if ($index > 0) {
                    $run->appendChild($document->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:tab'));
                }
                $text = $document->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t');
                $text->appendChild($document->createTextNode($part));
                $run->appendChild($text);
                $paragraph->appendChild($run);
            }

            return;
        }
    }

    private function fillFollowingBlank(DOMXPath $xpath, string $needle, string $value): void
    {
        $nodes = $xpath->query('//w:t');

        foreach ($nodes as $node) {
            if (! str_contains($node->nodeValue ?? '', $needle)) {
                continue;
            }

            $paragraph = $xpath->query('ancestor::w:p[1]', $node)->item(0);
            $paragraphNodes = $paragraph ? $xpath->query('.//w:t', $paragraph) : null;
            $index = $paragraphNodes ? array_search($node, iterator_to_array($paragraphNodes), true) : false;

            if ($paragraphNodes === null || $index === false) {
                continue;
            }

            for ($next = $index + 1; $next < $paragraphNodes->length; $next++) {
                $candidate = $paragraphNodes->item($next);
                $candidateText = trim($candidate->nodeValue ?? '');

                if (str_contains($candidateText, '___')) {
                    $candidate->nodeValue = preg_replace('/_{3,}/', $value, $candidate->nodeValue, 1) ?? $candidate->nodeValue;
                    return;
                }
            }

            $this->replaceInTextNode($xpath, $needle, $value);
            return;
        }
    }

    private function replaceInTextNode(DOMXPath $xpath, string $needle, string $replacement): void
    {
        foreach ($xpath->query('//w:t') as $node) {
            if (str_contains($node->nodeValue ?? '', $needle)) {
                $node->nodeValue = preg_replace('/_{3,}/', $replacement, $node->nodeValue, 1) ?? $replacement;
            }
        }
    }

    private function findTableContaining(DOMXPath $xpath, string $needle): ?DOMElement
    {
        foreach ($xpath->query('//w:tbl') as $table) {
            if (str_contains($table->textContent ?? '', $needle)) {
                return $table;
            }
        }

        return null;
    }

    private function tableAt(DOMXPath $xpath, int $index): ?DOMElement
    {
        $table = $xpath->query('//w:tbl')->item($index);

        return $table instanceof DOMElement ? $table : null;
    }

    private function appendCellText(DOMXPath $xpath, ?DOMElement $cell, string $value): void
    {
        if (! $cell || (trim($cell->textContent ?? '') !== '' && ! str_ends_with(trim($cell->textContent ?? ''), ':'))) {
            return;
        }

        $paragraph = $xpath->query('./w:p', $cell)->item(0);
        if (! $paragraph) {
            return;
        }

        $document = $paragraph->ownerDocument;
        $run = $document->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:r');
        $text = $document->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t');
        $text->appendChild($document->createTextNode($value));
        $run->appendChild($text);
        $paragraph->appendChild($run);
    }
}

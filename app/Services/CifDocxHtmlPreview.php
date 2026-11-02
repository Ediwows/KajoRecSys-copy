<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class CifDocxHtmlPreview
{
    public function render(string $docx): string
    {
        $zip = new ZipArchive;
        $temporary = tempnam(sys_get_temp_dir(), 'cif-preview-');
        if ($temporary === false || file_put_contents($temporary, $docx) === false || $zip->open($temporary) !== true) {
            throw new RuntimeException('The CIF Word preview could not be opened.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $logo = $this->firstImageData($zip);
        $zip->close();
        @unlink($temporary);
        if ($xml === false) {
            throw new RuntimeException('The CIF Word document body could not be found.');
        }

        $document = new DOMDocument;
        $document->loadXML($xml);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $pageOneLeft = '';
        $pageOneRight = '';
        $pageTwoLeft = '';
        $pageTwoRight = '';
        $tableIndex = 0;
        $column = 'pageOneRight';

        foreach ($xpath->query('//w:body/*') as $element) {
            if ($element->localName === 'tbl') {
                $table = $this->table($xpath, $element);
                if ($tableIndex === 0) {
                    $pageOneLeft .= $table;
                } elseif ($tableIndex === 1) {
                    $pageOneRight .= $table;
                } elseif ($tableIndex === 2 || $tableIndex === 3) {
                    $pageTwoLeft .= $table;
                } else {
                    $pageTwoRight .= $table;
                }
                $tableIndex++;
                continue;
            }

            if ($element->localName === 'p') {
                $text = $this->text($xpath, $element);
                if ($text === 'FAMILY INFORMATION') {
                    $column = 'pageTwoLeft';
                } elseif ($text === 'EDUCATIONAL BACKGROUND') {
                    $column = 'pageTwoRight';
                }

                if ($text !== '') {
                    ${$column} .= '<p>' . e($text) . '</p>';
                }
            }
        }

        $pageOneRight = $this->header($logo).$pageOneRight;

        return '<section class="cif-page"><div class="cif-column">'.$pageOneLeft.'</div><div class="cif-column">'.$pageOneRight.'</div></section>'
            .'<section class="cif-page"><div class="cif-column">'.$pageTwoLeft.'</div><div class="cif-column">'.$pageTwoRight.'</div></section>';
    }

    private function firstImageData(ZipArchive $zip): ?string
    {
        for ($index = 1; $index <= 5; $index++) {
            $entry = $zip->getFromName('word/media/image'.$index.'.png');
            if ($entry !== false) {
                return 'data:image/png;base64,'.base64_encode($entry);
            }
        }

        return null;
    }

    private function header(?string $logo): string
    {
        $image = $logo ? '<img src="'.$logo.'" alt="University seal" class="cif-logo">' : '';

        return '<div class="cif-header"><div class="cif-header-brand">'.$image.'<div><div>Republic of the Philippines</div><strong>Benguet State University</strong><div>Office of Student Services</div></div></div><div class="cif-header-code"><div><b>Document Code:</b> QF-BO-OSS-04</div><div><b>Revision No.:</b> 0</div><div><b>Effectivity Date:</b> October 14, 2024</div><div><b>Control No.:</b> __________</div></div><h2>CLIENT INFORMATION FORM (CIF)</h2></div>';
    }

    private function table(DOMXPath $xpath, DOMElement $table): string
    {
        $html = '<table>';
        foreach ($xpath->query('./w:tr', $table) as $row) {
            $html .= '<tr>';
            foreach ($xpath->query('./w:tc', $row) as $cell) {
                $html .= '<td>' . e($this->text($xpath, $cell)) . '</td>';
            }
            $html .= '</tr>';
        }
        return $html . '</table>';
    }

    private function text(DOMXPath $xpath, DOMElement $element): string
    {
        $parts = [];
        foreach ($xpath->query('.//w:t | .//w:tab | .//w:br', $element) as $node) {
            $parts[] = $node->localName === 'tab' ? "\t" : ($node->localName === 'br' ? "\n" : $node->nodeValue);
        }
        return trim(preg_replace('/[ \t]+/', ' ', implode('', $parts)) ?? '');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\StudentReference;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function importStudents(Request $request): RedirectResponse
    {
        $request->validate([
            'student_file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:20480'],
        ]);

        $file = $request->file('student_file');
        [$headers, $fileRows] = strtolower($file->getClientOriginalExtension()) === 'xlsx'
            ? $this->readXlsxRows($file->getRealPath())
            : $this->readCsvRows($file->getRealPath());

        $headerMap = $this->studentFileHeaderMap($headers);
        $required = ['id_number', 'gender', 'year', 'course'];
        $missing = array_diff($required, array_keys($headerMap));

        $hasNameColumns = isset($headerMap['name'])
            || (isset($headerMap['first_name']) && isset($headerMap['last_name']));

        if ($missing !== [] || ! $hasNameColumns) {
            throw ValidationException::withMessages([
                'student_file' => 'The file must include Code, name columns, Sex, Year, and Course columns.',
            ]);
        }

        $rows = [];
        foreach ($fileRows as $values) {
            if (count(array_filter($values, fn ($value) => filled(trim((string) $value)))) === 0) {
                continue;
            }

            $sourceData = [];
            foreach ($headers as $index => $header) {
                $sourceData[trim((string) $header)] = trim((string) ($values[$index] ?? ''));
            }

            $idNumber = trim((string) ($values[$headerMap['id_number']] ?? ''));
            $name = $this->studentNameFromRow($values, $headerMap);
            $gender = $this->normalizeGender($values[$headerMap['gender']] ?? '');

            if ($idNumber === '' || $name === '' || $gender === null) {
                continue;
            }

            $rows[$idNumber] = [
                'id_number' => $idNumber,
                'name' => $name,
                'gender' => $gender,
                'year' => $this->valueFromHeader($values, $headerMap, 'year'),
                'course' => $this->valueFromHeader($values, $headerMap, 'course'),
                'source_data' => json_encode($sourceData, JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'student_file' => 'No valid student records were found in the file.',
            ]);
        }

        $isUpdate = StudentReference::query()->exists();

        DB::transaction(function () use ($rows): void {
            $ids = array_keys($rows);

            StudentReference::query()->upsert(
                array_values($rows),
                ['id_number'],
                ['name', 'gender', 'year', 'course', 'source_data', 'updated_at']
            );

            // The uploaded file is the complete roster for the current school year.
            StudentReference::query()->whereNotIn('id_number', $ids)->delete();

            foreach ($rows as $row) {
                User::query()
                    ->where('role', 'student')
                    ->where('id_number', $row['id_number'])
                    ->update([
                        'name' => $row['name'],
                        'gender' => $row['gender'],
                        'is_active' => true,
                        'is_graduated' => false,
                        'updated_at' => now(),
                    ]);
            }

            User::query()
                ->where('role', 'student')
                ->whereNotNull('id_number')
                ->whereNotIn('id_number', $ids)
                ->update(['is_active' => false, 'updated_at' => now()]);
        });

        return to_route('guidance.dashboard')->with('status', count($rows).($isUpdate
            ? ' student records updated. Accounts missing from the file were deactivated and moved to the archived files folder.'
            : ' student records imported successfully. Future uploads will update this roster automatically.'));
    }

    /** @return array{0: array<int, string>, 1: array<int, array<int, string>>} */
    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages(['student_file' => 'The student file could not be read.']);
        }

        try {
            $headers = fgetcsv($handle) ?: [];
            $rows = [];
            while (($values = fgetcsv($handle)) !== false) {
                $rows[] = array_map(fn ($value) => (string) $value, $values);
            }

            return [array_map(fn ($header) => (string) $header, $headers), $rows];
        } finally {
            fclose($handle);
        }
    }

    /** @return array{0: array<int, string>, 1: array<int, array<int, string>>} */
    private function readXlsxRows(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['student_file' => 'The Excel file could not be read.']);
        }

        try {
            $sheetName = collect(range(0, $zip->numFiles - 1))
                ->map(fn (int $index) => $zip->getNameIndex($index))
                ->first(fn (string $name) => preg_match('/^xl\/worksheets\/sheet\d+\.xml$/', $name) === 1);
            $sheetXml = $sheetName ? $zip->getFromName($sheetName) : false;

            if ($sheetXml === false) {
                throw ValidationException::withMessages(['student_file' => 'The Excel file has no readable worksheet.']);
            }

            $sharedStrings = [];
            if (($sharedXml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
                $shared = simplexml_load_string($sharedXml);
                if ($shared !== false) {
                    foreach ($shared->xpath('//*[local-name() = "si"]') ?: [] as $item) {
                        $sharedStrings[] = implode('', $this->xmlTextValues($item));
                    }
                }
            }

            $sheet = simplexml_load_string($sheetXml);
            if ($sheet === false) {
                throw ValidationException::withMessages(['student_file' => 'The Excel worksheet could not be read.']);
            }

            $rows = [];
            foreach ($sheet->xpath('//*[local-name() = "sheetData"]/*[local-name() = "row"]') ?: [] as $row) {
                $values = [];
                foreach ($row->xpath('./*[local-name() = "c"]') ?: [] as $cell) {
                    $column = $this->xlsxColumnIndex((string) $cell['r']);
                    $type = (string) $cell['t'];
                    $inlineText = $cell->xpath('./*[local-name() = "is"]');
                    $valueNodes = $cell->xpath('./*[local-name() = "v"]');
                    $value = $type === 'inlineStr'
                        ? implode('', $this->xmlTextValues($inlineText[0] ?? $cell))
                        : (string) ($valueNodes[0] ?? '');

                    if ($type === 's') {
                        $value = $sharedStrings[(int) $value] ?? '';
                    }

                    $values[$column] = $value;
                }

                if ($values !== [] && count(array_filter($values, fn ($value) => filled(trim((string) $value)))) > 0) {
                    $maxColumn = max(array_keys($values));
                    $rows[] = array_map(fn ($index) => (string) ($values[$index] ?? ''), range(0, $maxColumn));
                }
            }

            $headers = array_shift($rows) ?? [];
            return [$headers, $rows];
        } finally {
            $zip->close();
        }
    }

    /** @return array<int, string> */
    private function xmlTextValues(\SimpleXMLElement $xml): array
    {
        $texts = $xml->xpath('.//*[local-name() = "t"]') ?: [];

        return array_map(fn ($text) => (string) $text, $texts);
    }

    private function xlsxColumnIndex(string $reference): int
    {
        preg_match('/^[A-Z]+/i', $reference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + ord($letter) - 64;
        }

        return $index - 1;
    }

    /** @param array<int, string|null> $headers */
    private function studentFileHeaderMap(array $headers): array
    {
        $aliases = [
            'id_number' => ['id', 'idnumber', 'code', 'studentcode', 'studentid', 'studentnumber', 'studentidnumber'],
            'name' => ['name', 'fullname', 'studentname', 'studentfullname'],
            'first_name' => ['firstname', 'givenname'],
            'last_name' => ['lastname', 'surname', 'familyname'],
            'middle_name' => ['middlename', 'middleinitial'],
            'gender' => ['gender', 'sex'],
            'year' => ['year', 'years', 'yearlevel', 'level', 'yearofstudy'],
            'course' => ['course', 'program', 'degree', 'courseprogram'],
        ];
        $map = [];

        foreach ($headers as $index => $header) {
            $normalized = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $header, " \t\r\n\xEF\xBB\xBF"))) ?? '';
            foreach ($aliases as $field => $fieldAliases) {
                if (in_array($normalized, $fieldAliases, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    /** @param array<int, string> $values @param array<string, int> $headerMap */
    private function studentNameFromRow(array $values, array $headerMap): string
    {
        if (isset($headerMap['name'])) {
            return trim((string) ($values[$headerMap['name']] ?? ''));
        }

        return implode(' ', array_filter([
            $this->valueFromHeader($values, $headerMap, 'first_name'),
            $this->valueFromHeader($values, $headerMap, 'middle_name'),
            $this->valueFromHeader($values, $headerMap, 'last_name'),
        ]));
    }

    /** @param array<int, string> $values @param array<string, int> $headerMap */
    private function valueFromHeader(array $values, array $headerMap, string $field): ?string
    {
        return isset($headerMap[$field]) && filled($value = trim((string) ($values[$headerMap[$field]] ?? '')))
            ? $value
            : null;
    }

    private function normalizeGender(string $gender): ?string
    {
        return match (strtolower(trim($gender))) {
            'male', 'm' => 'male',
            'female', 'f' => 'female',
            default => null,
        };
    }

    public function index(): View
    {
        $students = User::query()
            ->where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'id_number', 'gender', 'is_active', 'is_graduated']);

        $studentReferences = StudentReference::query()
            ->whereIn('id_number', $students->pluck('id_number')->filter())
            ->get(['id_number', 'course', 'year'])
            ->keyBy('id_number');

        $teachers = User::query()
            ->where('role', 'teacher')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.users', compact('students', 'studentReferences', 'teachers'));
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['student', 'teacher'], true), 404);

        $validated = $request->validate([
            'action' => [
                'required',
                Rule::in($user->role === 'student'
                    ? ['activate', 'deactivate', 'graduate']
                    : ['activate', 'deactivate']),
            ],
        ]);

        match ($validated['action']) {
            'activate' => $user->forceFill(['is_active' => true])->save(),
            'deactivate' => $user->forceFill(['is_active' => false])->save(),
            'graduate' => $user->forceFill(['is_graduated' => true, 'is_active' => false])->save(),
        };

        return to_route('admin.users.index')->with('status', 'User updated successfully.');
    }
}

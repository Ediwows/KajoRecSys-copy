<?php

namespace App\Http\Controllers;

use App\Models\CnsRequest;
use App\Models\SlipAssignment;
use App\Models\User;
use App\Services\CnsPdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CnsController extends Controller
{
    public function assignedStore(
        Request $request,
        SlipAssignment $slipAssignment,
        CnsPdfGenerator $pdfGenerator
    ): RedirectResponse {
        abort_unless(
            $request->user()->role === 'student'
                && $slipAssignment->student_id === $request->user()->id,
            403
        );
        abort_unless(
            $slipAssignment->slip === 'cns'
                && in_array($slipAssignment->status, ['pending', 'submitted'], true),
            403
        );

        $validated = $request->validate([
            'last_name' => ['required', 'string', 'max:80'],
            'first_name' => ['required', 'string', 'max:80'],
            'middle_initial' => ['nullable', 'string', 'max:10'],
            'student_id' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'degree' => ['required', Rule::in(array_keys(CnsPdfGenerator::DEGREE_OPTIONS))],
            'year_level' => ['required', Rule::in(['First', 'Second', 'Third', 'Fourth'])],
            'school_year' => ['required', 'string', 'max:50'],
            'purpose' => ['required', Rule::in(['educational_assistance', 'scholarship_application', 'other'])],
            'purpose_other' => ['nullable', 'required_if:purpose,other', 'string', 'max:100'],
        ]);
        $student = $request->user();
        $validated = [
            ...$validated,
            ...$this->studentDefaults($student),
        ];
        $existingRequest = $slipAssignment->cnsRequest;
        $pdfPath = $existingRequest?->pdf_path ?? 'cns-requests/'.Str::uuid().'.pdf';

        try {
            $cnsRequest = DB::transaction(function () use ($request, $validated, $pdfGenerator, $pdfPath, $slipAssignment, $existingRequest) {
                $lockedAssignment = SlipAssignment::query()
                    ->whereKey($slipAssignment->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                abort_unless(in_array($lockedAssignment->status, ['pending', 'submitted'], true), 409);

                $requestData = [
                    ...$validated,
                    'full_name' => implode(' ', array_filter([
                        $validated['first_name'],
                        $validated['middle_initial'] ?? null,
                        $validated['last_name'],
                    ])),
                    'user_id' => $request->user()->id,
                    'pdf_path' => $pdfPath,
                    'review_status' => 'submitted',
                    'approved_by' => null,
                    'approved_at' => null,
                ];

                if ($existingRequest) {
                    $cnsRequest = CnsRequest::query()
                        ->whereKey($existingRequest->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    abort_unless($cnsRequest->review_status !== 'approved', 409);
                    $cnsRequest->update($requestData);
                } else {
                    $cnsRequest = CnsRequest::create($requestData);
                }

                if (! Storage::disk('local')->put($pdfPath, $pdfGenerator->generate($cnsRequest))) {
                    throw new \RuntimeException('The completed CNS PDF could not be stored.');
                }

                $lockedAssignment->update([
                    'status' => 'submitted',
                    'cns_request_id' => $cnsRequest->id,
                    'completed_at' => now(),
                    'approved_by' => null,
                    'approved_at' => null,
                ]);

                return $cnsRequest;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($pdfPath);
            throw $exception;
        }

        return redirect()->route('cns.requests.show', $cnsRequest);
    }

    public function show(Request $request, CnsRequest $cnsRequest): View
    {
        $this->authorizeRequest($request, $cnsRequest);
        $assignment = SlipAssignment::query()
            ->where('cns_request_id', $cnsRequest->id)
            ->first();

        return view('slips.cns.submitted', compact('cnsRequest', 'assignment'));
    }

    public function pdf(Request $request, CnsRequest $cnsRequest): StreamedResponse
    {
        $this->authorizeRequest($request, $cnsRequest);
        abort_unless(Storage::disk('local')->exists($cnsRequest->pdf_path), 404);

        return Storage::disk('local')->response(
            $cnsRequest->pdf_path,
            "CNS-request-{$cnsRequest->id}.pdf",
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    private function authorizeRequest(Request $request, CnsRequest $cnsRequest): void
    {
        abort_unless(
            $cnsRequest->user_id === $request->user()->id
                || $request->user()->role === 'guidance_counselor',
            403
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function studentDefaults(User $student): array
    {
        $nameParts = preg_split('/\s+/', trim($student->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstName = array_shift($nameParts) ?? '';
        $lastName = count($nameParts) > 0 ? array_pop($nameParts) : '';
        $middleName = implode(' ', $nameParts);

        return [
            'student_id' => $student->id_number,
            'email' => $student->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_initial' => $middleName !== '' ? mb_substr($middleName, 0, 1).'.' : null,
        ];
    }
}

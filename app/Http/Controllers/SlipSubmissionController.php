<?php

namespace App\Http\Controllers;

use App\Models\CnsRequest;
use App\Models\SlipAssignment;
use App\Models\SlipSubmission;
use App\Models\User;
use App\Services\SlipPdfGenerator;
use App\Support\SlipCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SlipSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $slips = SlipCatalog::forRole($request->user()->role);
        $preview = $request->query('preview');
        $previewModal = is_string($preview) && isset($slips[$preview])
            ? $preview.'-modal'
            : null;

        return view('slips.index', [
            'slips' => $slips,
            'previewModal' => $previewModal,
        ]);
    }

    public function assignedIndex(Request $request): View
    {
        abort_unless($request->user()->role === 'student', 403);

        $assignments = SlipAssignment::query()
            ->with(['assignedBy', 'slipSubmission', 'cnsRequest'])
            ->where('student_id', $request->user()->id)
            ->whereIn('slip', SlipCatalog::workflowSlugs())
            ->latest()
            ->get();

        return view('slips.assigned', [
            'assignments' => $assignments,
            'slips' => SlipCatalog::all(),
        ]);
    }

    public function teacherDashboard(Request $request): View
    {
        abort_unless($request->user()->role === 'teacher', 403);

        $referrals = SlipAssignment::query()
            ->with('student')
            ->where('assigned_by', $request->user()->id)
            ->where('slip', 'student-referral-slip')
            ->latest()
            ->limit(8)
            ->get();

        return view('teacher.teacher', compact('referrals'));
    }

    public function guidanceDashboard(Request $request): View
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);

        $referrals = SlipAssignment::query()
            ->with('student')
            ->where('slip', 'student-referral-slip')
            ->whereIn('status', ['submitted', 'approved'])
            ->latest('completed_at')
            ->get()
            ->map(function (SlipAssignment $referral) {
                $hasClientInformationForm = SlipAssignment::query()
                    ->where('student_id', $referral->student_id)
                    ->where('slip', 'qf-bo-oss-04-client-information-form')
                    ->exists()
                    || SlipSubmission::query()
                        ->where('user_id', $referral->student_id)
                        ->where('slip', 'qf-bo-oss-04-client-information-form')
                        ->exists();
                $hasClassAdmission = SlipAssignment::query()
                    ->where('related_assignment_id', $referral->id)
                    ->where('slip', 'class-admission-slip')
                    ->exists();

                return [
                    'assignment' => $referral,
                    'can_issue_client_information' => ! $hasClientInformationForm,
                    'can_issue_class_admission' => ! $hasClassAdmission,
                ];
            })
            ->filter(fn (array $referral) => $referral['can_issue_client_information'] || $referral['can_issue_class_admission'])
            ->values();

        return view('admin.admin_dashboard', [
            'referrals' => $referrals,
            'pendingApprovalsCount' => SlipAssignment::query()
                ->where('status', 'submitted')
                ->whereIn('slip', SlipCatalog::workflowSlugs())
                ->count(),
        ]);
    }

    public function reviewIndex(Request $request): View
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);

        $assignments = SlipAssignment::query()
            ->with(['student', 'assignedBy', 'slipSubmission', 'cnsRequest'])
            ->where('status', 'submitted')
            ->whereIn('slip', SlipCatalog::workflowSlugs())
            ->latest('completed_at')
            ->get();

        return view('admin.slip-review', [
            'assignments' => $assignments,
            'slips' => SlipCatalog::all(),
        ]);
    }

    public function approve(Request $request, SlipAssignment $slipAssignment): RedirectResponse
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);

        DB::transaction(function () use ($request, $slipAssignment) {
            $assignment = SlipAssignment::query()
                ->whereKey($slipAssignment->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($assignment->status === 'submitted', 409);

            if ($assignment->slip === 'cns') {
                $cnsRequest = $assignment->cnsRequest;
                abort_unless($cnsRequest !== null, 409);
                $cnsRequest->update([
                    'review_status' => 'approved',
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                ]);
            } else {
                $submission = $assignment->slipSubmission;
                abort_unless($submission !== null, 409);
                $submission->update([
                    'review_status' => 'approved',
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                ]);
            }

            $assignment->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);
        });

        return redirect()
            ->route('guidance.slip-review')
            ->with('status', 'The submitted form has been approved.');
    }

    public function history(Request $request): View
    {
        abort_unless($request->user()->role === 'student', 403);

        $slips = SlipCatalog::all();
        $history = SlipSubmission::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (SlipSubmission $submission) => [
                'title' => $slips[$submission->slip]['title'] ?? 'Assigned Slip',
                'submitted_at' => $submission->created_at,
                'status' => $submission->review_status,
                'show_url' => route('slips.submissions.show', $submission),
                'pdf_url' => route('slips.submissions.pdf', $submission),
            ])
            ->concat(
                CnsRequest::query()
                    ->where('user_id', $request->user()->id)
                    ->latest()
                    ->get()
                    ->map(fn (CnsRequest $cnsRequest) => [
                        'title' => 'Certificate of No Scholarship',
                        'submitted_at' => $cnsRequest->created_at,
                        'status' => $cnsRequest->review_status,
                        'show_url' => route('cns.requests.show', $cnsRequest),
                        'pdf_url' => route('cns.requests.pdf', $cnsRequest),
                    ])
            )
            ->sortByDesc('submitted_at')
            ->values();

        return view('slips.history', compact('history'));
    }

    public function assign(Request $request, string $slip): RedirectResponse
    {
        abort_unless($request->user()->role === 'teacher', 403);
        abort_unless(isset(SlipCatalog::forRole('teacher')[$slip]), 404);

        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'student'),
            ],
        ]);

        SlipAssignment::create([
            'assigned_by' => $request->user()->id,
            'student_id' => $validated['student_id'],
            'slip' => $slip,
        ]);

        return redirect()
            ->route('slips.index')
            ->with('status', 'The form was assigned to the selected student.');
    }

    public function create(Request $request, string $slip): View
    {
        abort_unless(isset(SlipCatalog::forRole($request->user()->role)[$slip]), 403);

        $catalog = SlipCatalog::forRole($request->user()->role);
        abort_unless(isset($catalog[$slip]), 404);

        $students = User::query()
            ->where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'id_number', 'email']);

        return view('slips.select-student', [
            'title' => $catalog[$slip]['title'],
            'students' => $students,
            'selectAction' => route('slips.assign', $slip),
            'cancelUrl' => route('slips.index'),
        ]);
    }

    public function requestForm(Request $request, string $slip): RedirectResponse
    {
        abort_unless($request->user()->role === 'student', 403);
        abort_unless(isset(SlipCatalog::studentRequestForms()[$slip]), 404);

        $assignment = DB::transaction(function () use ($request, $slip) {
            User::query()
                ->whereKey($request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            $activeRequestExists = SlipAssignment::query()
                ->where('student_id', $request->user()->id)
                ->where('slip', $slip)
                ->whereIn('status', ['pending', 'submitted'])
                ->exists();
            abort_unless(! $activeRequestExists, 409, 'You already have an active request for this form.');

            return SlipAssignment::create([
                'assigned_by' => $request->user()->id,
                'student_id' => $request->user()->id,
                'slip' => $slip,
            ]);
        });

        return redirect()->route('assigned-slips.fillup', $assignment);
    }

    public function issueFollowup(
        Request $request,
        SlipAssignment $referral,
        string $slip
    ): RedirectResponse {
        abort_unless($request->user()->role === 'guidance_counselor', 403);
        abort_unless(in_array($slip, [
            'qf-bo-oss-04-client-information-form',
            'class-admission-slip',
        ], true), 404);
        DB::transaction(function () use ($request, $referral, $slip) {
            $lockedReferral = SlipAssignment::query()
                ->whereKey($referral->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless(
                $lockedReferral->slip === 'student-referral-slip'
                    && in_array($lockedReferral->status, ['submitted', 'approved'], true),
                409
            );

            if ($slip === 'qf-bo-oss-04-client-information-form') {
                User::query()
                    ->whereKey($lockedReferral->student_id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $this->ensureFollowupFormMayBeIssued($slip, $lockedReferral);

            SlipAssignment::create([
                'assigned_by' => $request->user()->id,
                'student_id' => $lockedReferral->student_id,
                'related_assignment_id' => $lockedReferral->id,
                'slip' => $slip,
            ]);
        });

        return redirect()
            ->route('guidance.dashboard')
            ->with('status', 'The form was sent to the student.');
    }

    private function ensureFollowupFormMayBeIssued(string $slip, SlipAssignment $referral): void
    {
        if ($slip === 'qf-bo-oss-04-client-information-form') {
            $hasClientInformationForm = SlipAssignment::query()
                ->where('student_id', $referral->student_id)
                ->where('slip', $slip)
                ->exists()
                || SlipSubmission::query()
                    ->where('user_id', $referral->student_id)
                    ->where('slip', $slip)
                    ->exists();
            abort_unless(! $hasClientInformationForm, 409, 'The Client Information Form is only issued to first-time students.');

            return;
        }

        abort_unless(
            ! SlipAssignment::query()
                ->where('related_assignment_id', $referral->id)
                ->where('slip', $slip)
                ->exists(),
            409,
            'This form has already been issued for the referral.'
        );
    }

    public function assignedCreate(Request $request, SlipAssignment $slipAssignment): View
    {
        abort_unless(
            $request->user()->role === 'student'
                && $slipAssignment->student_id === $request->user()->id,
            403
        );
        abort_unless(in_array($slipAssignment->status, ['pending', 'submitted'], true), 403);

        $student = $request->user();

        if ($slipAssignment->slip === 'cns') {
            return view('slips.cns.fillup', [
                'assignment' => $slipAssignment,
                'selectedStudent' => $student,
                'defaults' => $this->studentDefaults($student),
                'existingCnsRequest' => $slipAssignment->cnsRequest,
            ]);
        }

        $catalog = SlipCatalog::all();
        abort_unless(isset($catalog[$slipAssignment->slip]), 404);

        return view('slips.fillup', [
            'slug' => $slipAssignment->slip,
            'slip' => $catalog[$slipAssignment->slip],
            'assignment' => $slipAssignment,
            'selectedStudent' => $student,
            'defaults' => $this->studentDefaults($student),
            'existingSubmission' => $slipAssignment->slipSubmission,
        ]);
    }

    public function assignedStore(
        Request $request,
        SlipAssignment $slipAssignment,
        SlipPdfGenerator $pdfGenerator
    ): RedirectResponse {
        abort_unless(
            $request->user()->role === 'student'
                && $slipAssignment->student_id === $request->user()->id,
            403
        );
        abort_unless(
            in_array($slipAssignment->status, ['pending', 'submitted'], true)
                && $slipAssignment->slip !== 'cns',
            403
        );

        $catalog = SlipCatalog::all();
        abort_unless(isset($catalog[$slipAssignment->slip]), 404);
        $definition = $catalog[$slipAssignment->slip];
        $data = $this->validatedData($request, $definition);
        $student = $request->user();

        foreach ($this->studentDefaults($student) as $key => $value) {
            if ($value !== null && array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        $data['referred_student_id'] = $student->id;
        $data['referred_student_name'] = $student->name;
        $data['assigned_by'] = $slipAssignment->assigned_by;

        return $this->createSubmission(
            $request,
            $slipAssignment->slip,
            $data,
            $definition,
            $pdfGenerator,
            $slipAssignment,
            $slipAssignment->slipSubmission
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, array $definition): array
    {
        $rules = [];

        foreach ($definition['groups'] as $group) {
            foreach ($group['fields'] as $field) {
                $key = 'details.'.$field['key'];
                $presence = $field['required'] ? 'required' : 'nullable';

                if ($field['type'] === 'checkboxes') {
                    $rules[$key] = [$presence, 'array', ...($field['required'] ? ['min:1'] : [])];
                    $rules[$key.'.*'] = [
                        'string',
                        Rule::in(array_keys($field['options'])),
                    ];

                    continue;
                }

                $rules[$key] = [
                    $presence,
                    $field['type'] === 'date' ? 'date' : 'string',
                    'max:'.($field['type'] === 'textarea' ? 4000 : 255),
                ];
            }
        }

        $validated = $request->validate($rules);
        $input = $validated['details'] ?? [];
        $data = [];

        foreach ($definition['groups'] as $group) {
            foreach ($group['fields'] as $field) {
                $data[$field['key']] = $input[$field['key']] ?? null;

                if ($field['key'] === 'request_date' && $data[$field['key']] === null) {
                    $data[$field['key']] = now()->toDateString();
                }
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $definition
     */
    private function createSubmission(
        Request $request,
        string $slip,
        array $data,
        array $definition,
        SlipPdfGenerator $pdfGenerator,
        ?SlipAssignment $assignment = null,
        ?SlipSubmission $existingSubmission = null
    ): RedirectResponse {
        $pdfPath = $existingSubmission?->pdf_path ?? 'slip-submissions/'.Str::uuid().'.pdf';

        try {
            $submission = DB::transaction(function () use ($request, $slip, $data, $definition, $pdfGenerator, $pdfPath, $assignment, $existingSubmission) {
                $lockedAssignment = null;

                if ($assignment) {
                    $lockedAssignment = SlipAssignment::query()
                        ->whereKey($assignment->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    abort_unless(in_array($lockedAssignment->status, ['pending', 'submitted'], true), 409);
                }

                if ($existingSubmission) {
                    $submission = SlipSubmission::query()
                        ->whereKey($existingSubmission->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    abort_unless($submission->review_status !== 'approved', 409);
                    $submission->update([
                        'data' => $data,
                        'review_status' => 'submitted',
                        'approved_by' => null,
                        'approved_at' => null,
                    ]);
                } else {
                    $submission = SlipSubmission::create([
                        'user_id' => $request->user()->id,
                        'slip' => $slip,
                        'data' => $data,
                        'pdf_path' => $pdfPath,
                        'review_status' => 'submitted',
                    ]);
                }

                if (! Storage::disk('local')->put($pdfPath, $pdfGenerator->generate($submission->fresh(), $definition))) {
                    throw new \RuntimeException('The completed slip PDF could not be stored.');
                }

                if ($lockedAssignment) {
                    $lockedAssignment->update([
                        'status' => 'submitted',
                        'slip_submission_id' => $submission->id,
                        'completed_at' => now(),
                        'approved_by' => null,
                        'approved_at' => null,
                    ]);
                }

                return $submission;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($pdfPath);
            throw $exception;
        }

        return redirect()->route('slips.submissions.show', $submission);
    }

    public function show(Request $request, SlipSubmission $slipSubmission): View
    {
        $this->authorizeSubmission($request, $slipSubmission);
        abort_unless(isset(SlipCatalog::all()[$slipSubmission->slip]), 404);

        return view('slips.submitted', [
            'submission' => $slipSubmission,
            'slip' => SlipCatalog::all()[$slipSubmission->slip],
            'assignment' => SlipAssignment::query()
                ->where('slip_submission_id', $slipSubmission->id)
                ->first(),
        ]);
    }

    public function pdf(Request $request, SlipSubmission $slipSubmission): StreamedResponse
    {
        $this->authorizeSubmission($request, $slipSubmission);
        abort_unless(Storage::disk('local')->exists($slipSubmission->pdf_path), 404);

        return Storage::disk('local')->response(
            $slipSubmission->pdf_path,
            "{$slipSubmission->slip}-{$slipSubmission->id}.pdf",
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    private function authorizeSubmission(Request $request, SlipSubmission $submission): void
    {
        abort_unless(
            $submission->user_id === $request->user()->id
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
            'student_name' => $student->name,
            'email' => $student->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => $middleName ?: null,
            'middle_initial' => $middleName !== '' ? mb_substr($middleName, 0, 1).'.' : null,
            'request_date' => now()->toDateString(),
        ];
    }
}

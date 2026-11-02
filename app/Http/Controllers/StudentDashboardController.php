<?php

namespace App\Http\Controllers;

use App\Models\SlipAssignment;
use App\Models\SlipSubmission;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use App\Support\SlipCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $student = $request->user();
        abort_unless($student->role === 'student', 403);

        // Every student completes CIF once. Imported identity fields are populated from
        // StudentReference when available; newly registered students fill those fields in.
        DB::transaction(function () use ($student): void {
            $hasCif = SlipAssignment::query()->where('student_id', $student->id)
                ->where('slip', 'qf-bo-oss-04-client-information-form')->exists()
                || SlipSubmission::query()->where('user_id', $student->id)
                    ->where('slip', 'qf-bo-oss-04-client-information-form')->exists();
            if (! $hasCif) {
                SlipAssignment::create([
                    'assigned_by' => $student->id,
                    'student_id' => $student->id,
                    'slip' => 'qf-bo-oss-04-client-information-form',
                ]);
            }
        });

        $cifAssignment = SlipAssignment::query()
            ->where('student_id', $student->id)
            ->where('slip', 'qf-bo-oss-04-client-information-form')
            ->where('status', 'pending')
            ->latest()
            ->first();
        if ($cifAssignment) {
            return redirect()->route('assigned-slips.fillup', $cifAssignment);
        }

        $assignments = SlipAssignment::query()
            ->with(['assignedBy', 'slipSubmission', 'cnsRequest'])
            ->where('student_id', $student->id)
            ->whereIn('slip', SlipCatalog::workflowSlugs())
            ->latest()
            ->limit(5)
            ->get();

        $slips = SlipCatalog::all();
        $workflowSlugs = SlipCatalog::workflowSlugs();
        $history = SlipSubmission::query()
            ->where('user_id', $student->id)
            ->whereIn('slip', $workflowSlugs)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (SlipSubmission $submission) => [
                'title' => $slips[$submission->slip]['title'] ?? 'Assigned Slip',
                'date' => $submission->created_at,
                'status' => $submission->review_status,
                'url' => route('slips.submissions.show', $submission),
            ])
            ->sortByDesc('date')
            ->take(5)
            ->values();

        $appointments = Appointment::query()
            ->where('user_id', $student->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('student.student', [
            'pendingAssignmentsCount' => SlipAssignment::query()
                ->where('student_id', $student->id)
                ->whereIn('slip', $workflowSlugs)
                ->where('status', 'pending')
                ->count(),
            'awaitingApprovalCount' => SlipAssignment::query()
                ->where('student_id', $student->id)
                ->whereIn('slip', $workflowSlugs)
                ->where('status', 'submitted')
                ->count(),
            'submittedSlipsCount' => SlipSubmission::query()
                ->where('user_id', $student->id)
                ->whereIn('slip', $workflowSlugs)
                ->count(),
            'assignments' => $assignments,
            'history' => $history,
            'appointments' => $appointments,
            'slips' => $slips,
            'requestableForms' => SlipCatalog::studentRequestForms(),
            'activeRequestSlugs' => SlipAssignment::query()
                ->where('student_id', $student->id)
                ->whereIn('slip', array_keys(SlipCatalog::studentRequestForms()))
                ->whereIn('status', ['pending', 'submitted'])
                ->pluck('slip')
                ->unique()
                ->all(),
        ]);
    }
}

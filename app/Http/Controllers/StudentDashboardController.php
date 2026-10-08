<?php

namespace App\Http\Controllers;

use App\Models\SlipAssignment;
use App\Models\SlipSubmission;
use App\Support\SlipCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user();
        abort_unless($student->role === 'student', 403);

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

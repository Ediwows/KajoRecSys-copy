<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\SlipAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['student', 'guidance_counselor'], true), 403);

        $appointments = Appointment::query()
            ->with(['requester', 'reviewer'])
            ->when(
                $user->role !== 'guidance_counselor',
                fn ($query) => $query->where('user_id', $user->id),
            )
            ->latest()
            ->get();

        return view('appointments.index', compact('appointments'));
    }

    public function appointFromReferral(Request $request, SlipAssignment $referral): RedirectResponse
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);
        abort_unless($referral->slip === 'student-referral-slip' && in_array($referral->status, ['submitted', 'approved'], true), 404);

        $validated = $request->validate([
            'scheduled_date' => ['required', 'date_format:Y-m-d'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'in:30,60,90,120'],
            'concerns' => ['required', 'string', 'max:10000'],
        ]);
        $scheduledAt = \Illuminate\Support\Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['scheduled_date'].' '.$validated['scheduled_time'],
            config('app.timezone'),
        );

        if ($scheduledAt->isPast()) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'Choose a future appointment date and time.',
            ]);
        }

        Appointment::create([
            'user_id' => $referral->student_id,
            'referral_id' => $referral->id,
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $validated['duration_minutes'],
            'purpose' => $validated['concerns'],
            'concerns' => $validated['concerns'],
            'status' => 'accepted',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'The referral appointment was scheduled for the student.');
    }

    public function updateFromReferral(Request $request, SlipAssignment $referral, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);

        $appointment = Appointment::query()
            ->whereKey($appointment->id)
            ->where('referral_id', $referral->id)
            ->firstOrFail();

        $validated = $request->validate([
            'scheduled_date' => ['required', 'date_format:Y-m-d'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'in:30,60,90,120'],
            'concerns' => ['required', 'string', 'max:10000'],
        ]);
        $scheduledAt = \Illuminate\Support\Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['scheduled_date'].' '.$validated['scheduled_time'],
            config('app.timezone'),
        );

        if ($scheduledAt->isPast()) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'Choose a future appointment date and time.',
            ]);
        }

        $appointment->update([
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $validated['duration_minutes'],
            'purpose' => $validated['concerns'],
            'concerns' => $validated['concerns'],
            'status' => 'accepted',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'The referral appointment was updated.');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'student', 403);

        $validated = $request->validate([
            'purpose' => ['required', 'string', 'max:2000'],
        ]);

        Appointment::create([
            ...$validated,
            'scheduled_at' => null,
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Your appointment request has been sent to the Guidance Office.');
    }

    public function accept(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);

        $validated = $request->validate([
            'scheduled_date' => ['nullable', 'required_without:scheduled_at', 'date'],
            'scheduled_time' => ['nullable', 'required_without:scheduled_at', 'date_format:H:i'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'in:30,60,90,120'],
            'guidance_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $scheduledAt = ! empty($validated['scheduled_at'])
            ? \Illuminate\Support\Carbon::parse($validated['scheduled_at'])
            : \Illuminate\Support\Carbon::parse($validated['scheduled_date'].' '.$validated['scheduled_time']);

        if ($scheduledAt->isPast()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'scheduled_date' => 'Choose a future appointment date and time.',
            ]);
        }

        DB::transaction(function () use ($request, $appointment, $scheduledAt, $validated) {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless(in_array($lockedAppointment->status, ['pending', 'reschedule_requested'], true), 409);

            $lockedAppointment->update([
                'status' => 'accepted',
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $validated['duration_minutes'] ?? 60,
                'guidance_message' => $validated['guidance_message'] ?? null,
                'reschedule_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('appointments.index')
            ->with('status', 'The appointment request has been accepted.');
    }

    public function decline(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);

        $validated = $request->validate([
            'decline_reason' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $appointment, $validated) {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless(in_array($lockedAppointment->status, ['pending', 'reschedule_requested'], true), 409);

            $lockedAppointment->update([
                'status' => 'declined',
                'decline_reason' => $validated['decline_reason'],
                'reschedule_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('appointments.index')
            ->with('status', 'The appointment request has been declined and the reason was shared with the requester.');
    }

    public function complete(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);

        $validated = $request->validate([
            'action_taken' => ['required', 'string', 'max:10000'],
            'concerns' => ['nullable', 'string', 'max:10000'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'],
        ]);

        $files = collect($request->file('attachments', []))
            ->map(fn ($file) => [
                'name' => $file->getClientOriginalName(),
                'path' => $file->store('appointment-attachments', 'local'),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ])
            ->values()
            ->all();

        DB::transaction(function () use ($appointment, $validated, $files, $request): void {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($lockedAppointment->status === 'accepted', 409);

            $lockedAppointment->update([
                'status' => 'done',
                'action_taken' => $validated['action_taken'],
                'concerns' => $validated['concerns'] ?? $lockedAppointment->purpose,
                'completed_at' => now(),
                'attachments' => $files,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('appointments.index')
            ->with('status', 'The appointment was marked as done and the session record was saved.');
    }

    public function markMissed(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);

        DB::transaction(function () use ($request, $appointment): void {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($lockedAppointment->status === 'accepted', 409);
            abort_unless($lockedAppointment->scheduled_at?->isPast(), 409);

            $lockedAppointment->update([
                'status' => 'missed',
                'action_taken' => 'Client failed to attend',
                'completed_at' => now(),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('appointments.index')
            ->with('status', 'The appointment was recorded as missed.');
    }

    public function updateActionTaken(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->role === 'guidance_counselor', 403);

        $validated = $request->validate([
            'action_taken' => ['required', 'string', 'max:10000'],
            'concerns' => ['nullable', 'string', 'max:10000'],
        ]);

        $updated = Appointment::query()
            ->whereKey($appointment->id)
            ->whereIn('status', ['accepted', 'done', 'missed'])
            ->update([
                'status' => 'done',
                'action_taken' => $validated['action_taken'],
                'concerns' => $validated['concerns'] ?? $appointment->purpose,
                'completed_at' => $appointment->completed_at ?? now(),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

        abort_unless($updated === 1, 409);

        return redirect()
            ->route('guidance.records.show', $appointment->user_id)
            ->with('status', 'The appointment was marked as done and the action taken was saved to the CIF.');
    }

    public function downloadAttachment(Request $request, Appointment $appointment, int $attachment)
    {
        abort_unless(
            $request->user()->role === 'guidance_counselor' || $appointment->user_id === $request->user()->id,
            403,
        );

        $file = $appointment->attachments[$attachment] ?? null;
        abort_unless($file && Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name'], [
            'Content-Type' => $file['mime'] ?? 'application/octet-stream',
        ]);
    }

    public function reappoint(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->role === 'student', 403);

        $validated = $request->validate([
            'reschedule_reason' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $appointment, $validated): void {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->id)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(in_array($lockedAppointment->status, ['accepted', 'declined'], true), 409);

            $lockedAppointment->update([
                'status' => 'reschedule_requested',
                'scheduled_at' => null,
                'duration_minutes' => null,
                'reschedule_reason' => $validated['reschedule_reason'],
                'decline_reason' => null,
                'guidance_message' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);
        });

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Your re-appointment request has been sent to the Guidance Office.');
    }
}

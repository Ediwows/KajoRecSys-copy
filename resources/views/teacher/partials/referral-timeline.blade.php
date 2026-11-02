@php
    $admission = $referral->followups->firstWhere('slip', 'class-admission-slip');
    $steps = [
        [
            'title' => 'Referral sent to student',
            'detail' => 'Teacher: '.($referral->assignedBy?->name ?? 'Unknown').', student: '.($referral->student?->name ?? 'Unknown').', sent: '.($referral->created_at?->format('M j, Y g:i A') ?? 'Time unavailable'),
            'complete' => true,
        ],
        [
            'title' => 'Student sent referral to Guidance',
            'detail' => $referral->completed_at ? 'Student: '.($referral->student?->name ?? 'Unknown').', sent: '.$referral->completed_at->format('M j, Y g:i A') : 'Waiting for the student to submit the referral.',
            'complete' => (bool) $referral->completed_at,
        ],
        [
            'title' => 'Referral received by Guidance',
            'detail' => $admission ? 'Counselor: '.($admission->assignedBy?->name ?? 'Unknown').', received: '.($admission->created_at?->format('M j, Y g:i A') ?? 'Time unavailable') : 'Waiting for Guidance to receive the referral.',
            'complete' => (bool) $admission,
        ],
    ];

    if ($admission) {
        $steps = [...$steps,
            [
                'title' => 'Class Admission form sent to student',
                'detail' => 'Counselor: '.($admission->assignedBy?->name ?? 'Unknown').', student: '.($referral->student?->name ?? 'Unknown').', sent: '.($admission->created_at?->format('M j, Y g:i A') ?? 'Time unavailable'),
                'complete' => true,
            ],
            [
                'title' => 'Student sent Class Admission to Guidance',
                'detail' => $admission->completed_at ? 'Student: '.($referral->student?->name ?? 'Unknown').', sent: '.$admission->completed_at->format('M j, Y g:i A') : 'Waiting for the student to submit Class Admission.',
                'complete' => (bool) $admission->completed_at,
            ],
            [
                'title' => 'Counselor sent completed result to student',
                'detail' => $admission->approved_at ? 'Counselor: '.($admission->approver?->name ?? 'Unknown').', student: '.($referral->student?->name ?? 'Unknown').', sent: '.$admission->approved_at->format('M j, Y g:i A') : 'Waiting for Guidance to issue and send the result.',
                'complete' => (bool) $admission->approved_at,
            ],
            [
                'title' => 'Student sent result to teacher',
                'detail' => $admission->student_sent_to_teacher_at ? 'Student: '.($referral->student?->name ?? 'Unknown').', sent: '.$admission->student_sent_to_teacher_at->format('M j, Y g:i A') : 'Waiting for the student to forward the approved result.',
                'complete' => (bool) $admission->student_sent_to_teacher_at,
            ],
            [
                'title' => 'Result received by teacher',
                'detail' => $referral->teacher_acknowledged_at ? 'Teacher: '.($referral->assignedBy?->name ?? 'Unknown').', received: '.$referral->teacher_acknowledged_at->format('M j, Y g:i A') : 'Waiting for the teacher to acknowledge the result.',
                'complete' => (bool) $referral->teacher_acknowledged_at,
            ],
        ];
    } else {
        $steps[] = [
            'title' => 'Class Admission',
            'detail' => 'Not issued yet.',
            'complete' => false,
        ];
    }
@endphp

<section>
    <h3 class="mb-4 text-base font-bold">Referral process</h3>
    <ol class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @foreach ($steps as $step)
            <li class="rounded-xl border p-3 {{ $step['complete'] ? 'border-emerald-300 bg-emerald-50/60 dark:border-emerald-900 dark:bg-emerald-950/20' : 'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50' }}">
                <p class="text-sm font-semibold"><span class="mr-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-700 text-[10px] text-white">{{ $loop->iteration }}</span>{{ $step['title'] }}</p>
                <p class="mt-1.5 text-xs leading-5 text-zinc-700 dark:text-zinc-300">{{ $step['detail'] }}</p>
                @if (!$loop->last)
                    <div class="mt-3 flex justify-end border-t border-zinc-200 pt-2 text-xs font-bold text-emerald-700 dark:border-zinc-700 dark:text-emerald-300">
                        <span class="sm:hidden">Next step ↓</span>
                        <span class="hidden sm:inline">Next step {{ $loop->iteration % 2 === 1 ? '→' : '↙' }}</span>
                    </div>
                @endif
            </li>
        @endforeach
    </ol>
</section>

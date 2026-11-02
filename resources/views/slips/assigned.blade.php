<x-layouts::app :title="__('Assigned Slips')">
    <main class="min-h-screen bg-[#f6f5f1] px-4 py-8 text-zinc-900 dark:bg-[#111712] dark:text-zinc-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <div>
                <flux:heading size="xl">Assigned Slips</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-300">
                    Complete forms sent by your teacher or the Guidance Office, and track forms you requested.
                </flux:text>
            </div>

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900 dark:border-green-900 dark:bg-green-950 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($assignments->isEmpty())
                <section class="rounded-xl bg-white p-6 text-sm text-zinc-600 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700">
                    You do not have any assigned slips yet.
                </section>
            @else
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-zinc-100 text-xs uppercase text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <tr>
                                    <th class="px-5 py-3">Form</th>
                                    <th class="px-5 py-3">Source</th>
                                    <th class="px-5 py-3">Date</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($assignments as $assignment)
                                    @php
                                        $title = $assignment->slip === 'cns'
                                            ? 'Certificate of No Scholarship'
                                            : ($slips[$assignment->slip]['title'] ?? 'Assigned Slip');
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</td>
                                        <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                            @if ($assignment->assigned_by === auth()->id())
                                                Requested by you
                                            @else
                                                {{ $assignment->assignedBy->name }}
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $assignment->created_at->format('M j, Y') }}</td>
                                        <td class="px-5 py-4">
                                            <span @class([
                                                'rounded-full px-3 py-1 text-xs font-medium',
                                                'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' => in_array($assignment->status, ['pending', 'submitted'], true),
                                                'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200' => $assignment->status === 'approved',
                                            ])>
                                                {{ $assignment->status === 'pending' ? 'Not started' : ($assignment->status === 'submitted' ? 'Awaiting approval' : ucfirst($assignment->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            @if ($assignment->slip === 'student-referral-slip' && ($assignment->slipSubmission?->data['submitted_by_teacher'] ?? false))
                                                @if ($assignment->status === 'pending')
                                                    <a
                                                        href="{{ route('assigned-slips.fillup', $assignment) }}"
                                                        class="inline-flex items-center justify-center rounded-lg bg-[#09ba44] px-4 py-2 text-xs font-semibold text-[#092010] hover:bg-[#079b38] dark:bg-[#20783e] dark:text-white dark:hover:bg-[#28894a]"
                                                    >
                                                        Review and submit referral
                                                    </a>
                                                @else
                                                    <a
                                                        href="{{ route('slips.submissions.show', $assignment->slipSubmission) }}"
                                                        class="font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]"
                                                    >
                                                        View submitted referral
                                                    </a>
                                                @endif
                                            @elseif (in_array($assignment->status, ['pending', 'submitted'], true))
                                                <a
                                                    href="{{ route('assigned-slips.fillup', $assignment) }}"
                                                    class="inline-flex items-center justify-center rounded-lg bg-[#09ba44] px-4 py-2 text-xs font-semibold text-[#092010] hover:bg-[#079b38] dark:bg-[#20783e] dark:text-white dark:hover:bg-[#28894a]"
                                                >
                                                    {{ $assignment->status === 'submitted' ? 'Edit submission' : 'Fill up' }}
                                                </a>
                                            @elseif ($assignment->slip === 'class-admission-slip' && $assignment->status === 'approved' && ($assignment->relatedAssignment?->slipSubmission?->data['submitted_by_teacher'] ?? false))
                                                <div class="flex flex-wrap items-center justify-end gap-3">
                                                    @if ($assignment->slipSubmission)
                                                        <a href="{{ route('slips.submissions.show', $assignment->slipSubmission) }}" class="font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]">View completed result</a>
                                                    @endif
                                                    @if ($assignment->student_sent_to_teacher_at)
                                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 dark:bg-green-950 dark:text-green-200">Sent to teacher</span>
                                                    @else
                                                        <form method="POST" action="{{ route('assigned-slips.send-result-to-teacher', $assignment) }}" onsubmit="return confirm('Send this Class Admission result to your referring teacher?')">
                                                            @csrf
                                                            <button type="submit" class="rounded-lg bg-[#09ba44] px-4 py-2 text-xs font-semibold text-[#092010] hover:bg-[#079b38] dark:bg-[#20783e] dark:text-white">Send result to teacher</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @elseif ($assignment->slip === 'cns' && $assignment->cnsRequest)
                                                <a
                                                    href="{{ route('cns.requests.show', $assignment->cnsRequest) }}"
                                                    class="font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]"
                                                >
                                                    View {{ $assignment->status === 'approved' ? 'approved' : 'submitted' }} form
                                                </a>
                                            @elseif ($assignment->slipSubmission)
                                                <a
                                                    href="{{ route('slips.submissions.show', $assignment->slipSubmission) }}"
                                                    class="font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]"
                                                >
                                                    View {{ $assignment->status === 'approved' ? 'approved' : 'submitted' }} form
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </main>
</x-layouts::app>

<x-layouts::app :title="__('Slip Approvals')">
    <main class="min-h-screen bg-zinc-50 px-4 py-8 text-zinc-900 dark:bg-[#111712] dark:text-zinc-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <div>
                <flux:heading size="xl" class="text-zinc-900 dark:text-zinc-100">Slip Approvals</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-300">
                    Review student submissions. Approval locks a form against further student edits.
                </flux:text>
            </div>

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900 dark:border-green-900 dark:bg-green-950/50 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($assignments->isEmpty())
                <section class="rounded-xl bg-white p-6 text-sm text-zinc-600 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700">
                    There are no forms awaiting approval.
                </section>
            @else
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-zinc-100 text-xs uppercase text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <tr>
                                    <th class="px-5 py-3">Form</th>
                                    <th class="px-5 py-3">Student</th>
                                    <th class="px-5 py-3">Sent by / Requested by</th>
                                    <th class="px-5 py-3">Submitted</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($assignments as $assignment)
                                    @php
                                        $title = $assignment->slip === 'cns'
                                            ? 'Certificate of No Scholarship'
                                            : ($slips[$assignment->slip]['title'] ?? 'Assigned Slip');
                                        $showUrl = $assignment->slip === 'cns' && $assignment->cnsRequest
                                            ? route('cns.requests.show', $assignment->cnsRequest)
                                            : ($assignment->slipSubmission
                                                ? route('slips.submissions.show', $assignment->slipSubmission)
                                                : null);
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</td>
                                        <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                            {{ $assignment->student->name }}
                                            <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ $assignment->student->id_number }}</span>
                                        </td>
                                        <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                            @if ($assignment->assigned_by === $assignment->student_id)
                                                Student request
                                            @else
                                                {{ $assignment->assignedBy->name }}
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $assignment->completed_at?->format('M j, Y g:i A') }}</td>
                                        <td class="space-x-3 px-5 py-4 text-right">
                                            @if ($showUrl)
                                                <a href="{{ $showUrl }}" class="font-semibold text-green-700 hover:underline dark:text-emerald-400">
                                                    Review
                                                </a>
                                            @endif
                                            @if ($assignment->slip !== 'class-admission-slip')
                                                <form method="POST" action="{{ route('guidance.slip-review.approve', $assignment) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg bg-green-700 px-4 py-2 text-xs font-semibold text-white hover:bg-green-800">
                                                        Approve
                                                    </button>
                                                </form>
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

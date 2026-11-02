<x-layouts::app :title="__('Referral History')">
    <main class="min-h-screen bg-[#f6f5f1] p-5 text-gray-900 dark:bg-[#111712] dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header>
                <h1 class="text-2xl font-bold">Referral History</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                    Referrals with an issued Class Admission result that you acknowledged.
                </p>
            </header>

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900 dark:border-green-900 dark:bg-green-950/50 dark:text-green-200">{{ session('status') }}</div>
            @endif

            <section class="overflow-x-auto rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                @if ($referrals->isEmpty())
                    <p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">
                        You have not sent any student referrals yet.
                    </p>
                @else
                    <div class="space-y-4">
                    @foreach ($referrals->groupBy('student_id') as $studentReferrals)
                        @php($student = $studentReferrals->first()->student)
                        <details class="group overflow-hidden rounded-2xl border border-emerald-200 bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800/60">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 transition hover:bg-emerald-50 dark:hover:bg-zinc-800">
                                <span class="flex items-center gap-3"><span class="text-xl" aria-hidden="true">📁</span><span><span class="block font-semibold">{{ $student->name }}</span><span class="block text-xs text-slate-500 dark:text-zinc-400">{{ $student->id_number ?: 'Student profile' }}</span></span></span>
                                <span class="text-xs text-slate-500 dark:text-zinc-400">{{ $studentReferrals->count() }} {{ str()->plural('slip', $studentReferrals->count()) }} <span class="ml-2 text-base transition group-open:rotate-180" aria-hidden="true">⌄</span></span>
                            </summary>
                            <div class="overflow-x-auto border-t border-emerald-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-zinc-400">Student</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-zinc-400">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-zinc-400">Referral Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-zinc-400">Notes</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-zinc-400">Class Admission Slip</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-zinc-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                            @foreach ($studentReferrals as $referral)
                                @php($admission = $referral->followups->firstWhere('slip', 'class-admission-slip'))
                                <tr>
                                    <td class="px-4 py-4 whitespace-nowrap font-medium">{{ $referral->student->name }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-zinc-400">{{ $referral->created_at->format('M j, Y') }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">{{ match ($referral->status) {
                                        'pending' => 'Awaiting student',
                                        'submitted' => 'Received by Guidance',
                                        'approved' => 'Approved',
                                        'cancelled' => 'Canceled',
                                        default => ucfirst($referral->status),
                                    } }}</td>
                                    <td class="min-w-64 px-4 py-4">
                                        <form method="POST" action="{{ route('teacher.referrals.notes', $referral) }}" class="space-y-2">
                                            @csrf @method('PATCH')
                                            <textarea name="notes" rows="3" maxlength="2000" data-word-limit="50" aria-label="Teacher comment for {{ $referral->student->name }}" placeholder="Add a private comment (50 words max)" class="w-full rounded-lg border-gray-300 bg-white p-2 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:ring-green-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">{{ $referral->notes }}</textarea>
                                            <div class="flex items-center justify-between gap-2"><span class="text-xs text-gray-500 dark:text-zinc-400"><span data-word-count>0</span>/50 words · Enter to save, Shift+Enter for a new line</span><button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Save comment</button></div>
                                        </form>
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        @if (!$admission)
                                            <span class="text-gray-500 dark:text-zinc-400">Not issued</span>
                                        @elseif ($admission->status === 'approved')
                                            <span class="font-semibold text-green-700 dark:text-green-300">Processed / Approved</span>
                                            @if ($admission->slipSubmission)
                                                @include('teacher.partials.submission-preview-modal', ['submission' => $admission->slipSubmission])
                                            @endif
                                        @elseif ($admission->status === 'submitted')
                                            <span class="text-amber-700 dark:text-amber-300">Submitted for processing</span>
                                            @if ($admission->slipSubmission)
                                                @include('teacher.partials.submission-preview-modal', ['submission' => $admission->slipSubmission])
                                            @endif
                                        @else
                                            <span class="text-gray-500 dark:text-zinc-400">Awaiting student</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        @if ($referral->slipSubmission)
                                            @include('teacher.partials.submission-preview-modal', ['submission' => $referral->slipSubmission])
                                        @else
                                            <span class="text-gray-500 dark:text-zinc-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 pb-5">
                                        @include('teacher.partials.referral-timeline-modal', ['referral' => $referral])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                            </div>
                        </details>
                    @endforeach
                    </div>
                @endif
            </section>
        </div>
    </main>
    <script>
        document.querySelectorAll('[data-open-timeline]').forEach((button) => {
            button.addEventListener('click', () => document.getElementById(button.dataset.openTimeline)?.showModal());
        });
        document.querySelectorAll('dialog[id^="referral-timeline-"]').forEach((dialog) => {
            dialog.querySelector('[data-close-timeline]').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        });
        document.querySelectorAll('[data-open-submission]').forEach((button) => {
            button.addEventListener('click', () => document.getElementById(button.dataset.openSubmission)?.showModal());
        });
        document.querySelectorAll('dialog[id^="submission-preview-"]').forEach((dialog) => {
            dialog.querySelector('[data-close-submission]').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        });

        document.querySelectorAll('textarea[data-word-limit]').forEach((textarea) => {
            const form = textarea.closest('form');
            const counter = form.querySelector('[data-word-count]');
            const limit = Number(textarea.dataset.wordLimit);
            const countWords = () => (textarea.value.trim().match(/\S+/g) || []).length;
            const updateCounter = () => { counter.textContent = countWords(); };

            textarea.addEventListener('input', updateCounter);
            textarea.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.shiftKey) return;
                event.preventDefault();
                if (countWords() <= limit) form.requestSubmit();
                else {
                    textarea.setCustomValidity(`Please keep the comment to ${limit} words or fewer.`);
                    textarea.reportValidity();
                }
            });
            textarea.addEventListener('input', () => textarea.setCustomValidity(''));
            form.addEventListener('submit', (event) => {
                if (countWords() > limit) {
                    event.preventDefault();
                    textarea.setCustomValidity(`Please keep the comment to ${limit} words or fewer.`);
                    textarea.reportValidity();
                }
            });
            updateCounter();
        });
    </script>
</x-layouts::app>

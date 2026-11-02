<x-layouts::app :title="__('Referral I sent')">
    <main class="min-h-screen bg-[#f6f5f1] p-5 text-gray-900 dark:bg-[#111712] dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header><h1 class="text-2xl font-bold">Referral I sent</h1><p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Active referrals stay here until the Class Admission result is issued and you acknowledge it.</p></header>
            @if (session('status'))<div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900 dark:border-green-900 dark:bg-green-950/50 dark:text-green-200">{{ session('status') }}</div>@endif
            <section class="space-y-4">
                @forelse ($referrals as $referral)
                    @php($admission = $referral->followups->firstWhere('slip', 'class-admission-slip'))
                    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex flex-wrap items-start justify-between gap-4"><div><h2 class="font-semibold">{{ $referral->student->name }}</h2><p class="text-sm text-gray-500 dark:text-zinc-400">{{ $referral->student->id_number ?: 'Student profile' }} · Sent {{ $referral->created_at->format('M j, Y') }}</p></div><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">{{ $admission?->status === 'approved' ? ($admission->student_sent_to_teacher_at ? 'Result sent by student · acknowledgment needed' : 'Result issued · waiting for student') : ($admission ? 'Class Admission processing' : 'Awaiting Class Admission result') }}</span></div>
                        <div class="mt-4 flex flex-wrap items-center gap-4">
                            @if ($admission?->status === 'approved' && $admission->student_sent_to_teacher_at)
                                @if ($admission->slipSubmission)@include('teacher.partials.submission-preview-modal', ['submission' => $admission->slipSubmission])@endif
                                <form method="POST" action="{{ route('teacher.referrals.acknowledge', $referral) }}">@csrf<button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">OK</button></form>
                            @elseif ($admission?->status === 'approved')
                                <span class="text-sm text-amber-700 dark:text-amber-300">Waiting for {{ $referral->student->name }} to send the result to you.</span>
                            @elseif ($referral->slipSubmission)
                                @include('teacher.partials.submission-preview-modal', ['submission' => $referral->slipSubmission])
                            @endif
                        </div>
                        <form method="POST" action="{{ route('teacher.referrals.notes', $referral) }}" class="mt-5 border-t border-gray-200 pt-4 dark:border-zinc-700">
                            @csrf @method('PATCH')
                            <label for="notes-{{ $referral->id }}" class="mb-2 block text-sm font-semibold">Teacher comment</label>
                            <textarea id="notes-{{ $referral->id }}" name="notes" rows="3" maxlength="2000" data-word-limit="50" placeholder="Add a private comment about this referral (50 words max)" class="w-full rounded-lg border-gray-300 bg-white p-3 text-sm text-gray-900 shadow-sm focus:border-green-600 focus:ring-green-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">{{ $referral->notes }}</textarea>
                            <div class="mt-2 flex items-center justify-between gap-3"><span class="text-xs text-gray-500 dark:text-zinc-400"><span data-word-count>0</span>/50 words · Enter to save, Shift+Enter for a new line</span><button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Save comment</button></div>
                        </form>
                        @include('teacher.partials.referral-timeline-modal', ['referral' => $referral])
                    </article>
                @empty
                    <p class="rounded-lg bg-white p-5 text-sm text-gray-600 dark:bg-zinc-900 dark:text-zinc-300">No active referrals. Referrals with issued results will appear here until you acknowledge them.</p>
                @endforelse
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

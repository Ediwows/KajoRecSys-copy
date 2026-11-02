@php($isReferral = $submission->slip === 'student-referral-slip')
<button type="button" data-open-submission="submission-preview-{{ $submission->id }}" class="inline-flex items-center rounded-lg font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]">
    {{ $isReferral ? 'View referral' : 'View result' }}
</button>
<dialog id="submission-preview-{{ $submission->id }}" style="width: min(96vw, 96rem); max-width: 96vw; height: 94vh; max-height: 94vh; margin: auto;" class="overscroll-contain overflow-hidden rounded-2xl border border-zinc-200 bg-white p-0 text-zinc-900 shadow-2xl backdrop:bg-black/60 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" aria-labelledby="submission-preview-title-{{ $submission->id }}">
    <header class="flex items-start justify-between gap-4 border-b border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 sm:p-5">
        <div><h2 id="submission-preview-title-{{ $submission->id }}" class="text-lg font-bold">{{ $isReferral ? 'Student Referral' : 'Class Admission result' }}</h2><p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $submission->user?->name ?? 'Student' }} · {{ $submission->created_at?->format('M j, Y g:i A') }}</p></div>
        <button type="button" data-close-submission aria-label="Close document preview" class="rounded-lg px-3 py-1 text-2xl hover:bg-zinc-100 dark:hover:bg-zinc-800">&times;</button>
    </header>
    <div style="height: calc(94vh - 5rem);" class="overscroll-contain overflow-y-auto p-3 sm:p-5">
        <iframe src="{{ $isReferral ? route('slips.submissions.preview-pdf', $submission) : route('slips.submissions.pdf', $submission) }}" title="{{ $isReferral ? 'Student Referral' : 'Class Admission result' }} PDF" style="height: calc(94vh - 8rem); min-height: 32rem;" class="w-full rounded-lg border border-zinc-300 bg-white dark:border-zinc-700"></iframe>
        @if (!$isReferral && !empty($submission->data['_attachments']))
            <section class="mt-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <h3 class="font-semibold">Supporting evidence</h3>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($submission->data['_attachments'] as $index => $attachment)
                        <li><a href="{{ route('slips.submissions.attachment', [$submission, $index]) }}" target="_blank" rel="noopener" class="inline-flex rounded-lg border border-zinc-300 px-3 py-2 text-sm font-medium hover:bg-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-800">{{ $attachment['original_name'] ?? 'Open attachment' }}</a></li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</dialog>

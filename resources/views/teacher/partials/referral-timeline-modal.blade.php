<button type="button" data-open-timeline="referral-timeline-{{ $referral->id }}" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-600 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
    View referral process
</button>
<dialog id="referral-timeline-{{ $referral->id }}" class="w-[min(90rem,calc(100%-2rem))] max-h-[90vh] overscroll-contain overflow-y-auto rounded-2xl border border-zinc-200 bg-white p-0 text-zinc-900 shadow-2xl backdrop:bg-black/60 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" aria-labelledby="referral-timeline-title-{{ $referral->id }}">
    <header class="sticky top-0 z-30 flex items-start justify-between gap-4 border-b border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
        <div><h2 id="referral-timeline-title-{{ $referral->id }}" class="text-lg font-bold">Referral process timeline</h2><p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $referral->student?->name }} · {{ $referral->student?->id_number ?: 'Student profile' }}</p></div>
        <button type="button" data-close-timeline aria-label="Close timeline" class="rounded-lg px-3 py-1 text-2xl hover:bg-zinc-100 dark:hover:bg-zinc-800">&times;</button>
    </header>
    <div class="p-5 sm:p-6">@include('teacher.partials.referral-timeline', ['referral' => $referral])</div>
</dialog>

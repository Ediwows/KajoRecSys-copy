<x-layouts::app>
    <div class="mx-auto w-full max-w-none space-y-5 p-2 sm:p-4 lg:p-6">
        <div class="flex items-center justify-between gap-4">
            <div><a href="{{ route('guidance.cif.index') }}" class="text-sm font-semibold text-green-700 underline dark:text-green-300">← Back to Student CIF</a><h1 class="mt-3 text-2xl font-bold text-neutral-900 dark:text-white">{{ $submission->user?->name ?? 'Student' }} — Client Information Form</h1></div>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/40 dark:text-green-300">Soft copy</span>
                <a href="{{ route('guidance.cif.docx', $submission) }}" class="inline-flex items-center rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Download Word CIF</a>
            </div>
        </div>
        <main class="overflow-hidden rounded-xl border border-neutral-200 bg-neutral-100 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <iframe
                src="{{ route('slips.submissions.pdf', $submission) }}#page=3&toolbar=0&navpanes=0&zoom=page-width"
                title="{{ $submission->user?->name ?? 'Student' }} Client Information Form"
                class="w-full border-0"
                style="height: calc(100vh - 8rem); min-height: 700px;"
            ></iframe>
        </main>
        <section id="counseling-history" class="scroll-mt-6 overflow-hidden rounded-xl border border-blue-200 bg-blue-50 shadow-sm dark:border-blue-900 dark:bg-blue-950/20">
            <div class="border-b border-blue-200 px-5 py-4 dark:border-blue-900">
                <p class="text-xs font-bold uppercase tracking-wide text-blue-700 dark:text-blue-300">Counseling history</p>
                <h2 class="mt-1 text-lg font-bold text-blue-950 dark:text-blue-100">Appointment records</h2>
                <p class="mt-1 text-sm text-blue-800 dark:text-blue-200">These records are shown directly to the counselor and do not require downloading the CIF.</p>
            </div>
            @if ($appointments->isEmpty())
                <p class="px-5 py-6 text-sm text-blue-900/70 dark:text-blue-100/70">No appointment records are available for this student.</p>
            @else
                <div class="overflow-x-auto bg-white dark:bg-neutral-900">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-100 text-xs uppercase tracking-wide text-blue-900 dark:bg-blue-950/50 dark:text-blue-100">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Date</th>
                                <th class="px-5 py-3 font-semibold">Concerns</th>
                                <th class="px-5 py-3 font-semibold">Interventions / Actions Taken</th>
                                <th class="px-5 py-3 font-semibold">Attending Counselor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-100 dark:divide-neutral-800">
                            @foreach ($appointments as $appointment)
                                <tr class="align-top text-neutral-800 dark:text-neutral-200">
                                    <td class="whitespace-nowrap px-5 py-4">{{ ($appointment->scheduled_at ?? $appointment->completed_at)?->format('m/d/Y') ?? '—' }}</td>
                                    <td class="min-w-48 px-5 py-4">{{ $appointment->concerns ?? $appointment->purpose ?? '—' }}</td>
                                    <td class="min-w-64 px-5 py-4">{{ $appointment->action_taken ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">{{ $appointment->reviewer?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts::app>

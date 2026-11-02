<x-layouts::app>
    <div class="mx-auto w-full max-w-7xl space-y-6 p-6 lg:p-8">
        <div>
            <a href="{{ route('guidance.dashboard') }}" class="text-sm font-semibold text-green-700 underline dark:text-green-300">← Back to dashboard</a>
            <p class="mt-5 text-sm font-semibold uppercase tracking-wide text-green-700 dark:text-green-300">Guidance records</p>
            <h1 class="mt-1 text-3xl font-bold text-neutral-900 dark:text-white">Student CIF</h1>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">View submitted Client Information Forms and counselor session records.</p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-800"><tr><th class="px-5 py-3 text-left text-xs font-bold uppercase">Student</th><th class="px-5 py-3 text-left text-xs font-bold uppercase">Student ID</th><th class="px-5 py-3 text-left text-xs font-bold uppercase">Submitted</th><th class="px-5 py-3 text-right text-xs font-bold uppercase">Action</th></tr></thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($submissions as $submission)
                        <tr><td class="px-5 py-4 text-sm font-semibold">{{ $submission->user?->name ?? 'Unknown student' }}</td><td class="px-5 py-4 text-sm">{{ $submission->user?->id_number ?? '—' }}</td><td class="px-5 py-4 text-sm">{{ $submission->created_at?->format('M j, Y g:i A') }}</td><td class="px-5 py-4 text-right"><a href="{{ route('guidance.cif.show', $submission) }}" class="rounded-lg bg-green-700 px-3 py-2 text-xs font-semibold text-white hover:bg-green-800">Open CIF</a></td></tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-neutral-500">No submitted CIF records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>

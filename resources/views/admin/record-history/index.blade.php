<x-layouts::app :title="$type === 'all' ? __('Appointment & Slip History') : ($type === 'slips' ? __('Slip History') : __('Appointment History'))">
    <main class="min-h-screen bg-[#f6f5f1] p-5 text-slate-900 dark:bg-[#111712] dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-5xl space-y-6">
            <header>
                <p class="text-sm font-medium text-[#28643a] dark:text-[#91dfa6]">Guidance Office</p>
                <h1 class="mt-1 text-3xl font-bold">{{ $type === 'all' ? 'Appointment & Slip History' : ($type === 'slips' ? 'Slip History' : 'Appointment History') }}</h1>
                <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">Select a student to open their profile and review their appointment and slip records. Students without saved records are listed too.</p>
            </header>

            <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                @forelse ($students as $student)
                    <a href="{{ route('guidance.records.show', $student) }}" class="flex items-center justify-between gap-4 border-b border-neutral-100 px-5 py-4 last:border-b-0 hover:bg-green-50 dark:border-neutral-800 dark:hover:bg-neutral-800">
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $student->name }}</p>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ $student->id_number ?: $student->email }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3 text-xs text-neutral-500">
                            @if ($type === 'slips')
                                <span>{{ $student->slip_submissions_count + $student->cns_requests_count + $student->slip_assignments_count }} slip records</span>
                            @elseif ($type === 'appointments')
                                <span>{{ $student->appointments_count }} appointments</span>
                            @else
                                <span>{{ $student->appointments_count + $student->slip_submissions_count + $student->cns_requests_count + $student->slip_assignments_count }} total records</span>
                            @endif
                            <span aria-hidden="true" class="text-lg">›</span>
                        </div>
                    </a>
                @empty
                    <p class="px-6 py-12 text-center text-sm text-neutral-500">{{ $type === 'all' ? 'No active student accounts found.' : ($type === 'slips' ? 'No students have slip records yet.' : 'No students have appointment records yet.') }}</p>
                @endforelse
            </section>
        </div>
    </main>
</x-layouts::app>

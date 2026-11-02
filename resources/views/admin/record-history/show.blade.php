<x-layouts::app :title="__('Student Record History')">
    <main class="min-h-screen bg-[#f6f5f1] p-5 text-slate-900 dark:bg-[#111712] dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-5xl space-y-6">
            <a href="{{ url()->previous() }}" class="text-sm font-medium text-[#28643a] hover:underline dark:text-[#91dfa6]">← Back to student list</a>
            <header class="rounded-2xl bg-white p-6 shadow-sm dark:bg-neutral-900">
                <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Student record history</p>
                <h1 class="mt-1 text-2xl font-bold">{{ $student->name }}</h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ $student->id_number ?: $student->email }}</p>
            </header>

            @if ($referralTracking->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-purple-200 bg-white shadow-sm dark:border-purple-900/60 dark:bg-neutral-900">
                    <div class="border-b border-purple-100 bg-purple-50/60 px-6 py-4 dark:border-purple-900/60 dark:bg-purple-950/20">
                        <h2 class="font-semibold text-purple-950 dark:text-purple-100">Referral Tracking</h2>
                        <p class="mt-1 text-sm text-purple-900/70 dark:text-purple-200/70">Referral stages and follow-up slips connected to this student’s appointment and slip records.</p>
                    </div>
                    <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        @foreach ($referralTracking as $tracking)
                            <div class="px-6 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-semibold">Student Referral Slip</p>
                                        <p class="mt-1 text-xs text-neutral-500">Received {{ $tracking['referral']->completed_at?->format('M j, Y g:i A') ?? 'Date not set' }}</p>
                                    </div>
                                    <span class="w-fit rounded-full bg-purple-100 px-2.5 py-1 text-xs font-semibold capitalize text-purple-800 dark:bg-purple-950 dark:text-purple-200">{{ $tracking['status'] }}</span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($tracking['steps'] as $step)
                                        <span class="rounded-lg border border-neutral-200 px-3 py-2 text-xs dark:border-neutral-700"><span class="font-semibold">{{ $step->slip === 'student-referral-slip' ? 'Referral' : str($step->slip)->replace('-', ' ')->title() }}</span> · {{ str_replace('_', ' ', $step->status) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <details class="group overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-sm dark:border-blue-900/60 dark:bg-neutral-900">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b border-blue-100 bg-blue-50/60 px-6 py-5 transition hover:bg-blue-100/70 dark:border-blue-900/60 dark:bg-blue-950/20 dark:hover:bg-blue-950/40">
                    <span class="flex items-center gap-3">
                        <span class="text-2xl" aria-hidden="true">📁</span>
                        <span>
                            <span class="block font-semibold">Appointment History <span class="ml-1 text-sm font-normal text-neutral-500">({{ $appointmentRecords->count() }})</span></span>
                            <span class="mt-1 block text-sm text-neutral-500">Click to review scheduled appointments and outcomes.</span>
                        </span>
                    </span>
                    <span class="text-xl text-neutral-500 transition group-open:rotate-180" aria-hidden="true">⌄</span>
                </summary>
                @forelse ($appointmentRecords as $record)
                    <article class="border-b border-neutral-100 px-6 py-5 last:border-b-0 dark:border-neutral-800">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold">{{ $record['title'] }}</h3>
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold capitalize text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ $record['status'] === 'missed' ? 'Client failed to attend' : str_replace('_', ' ', $record['status']) }}</span>
                                </div>
                                <p class="mt-2 max-w-2xl text-sm text-neutral-600 dark:text-neutral-300">{{ $record['details'] }}</p>
                                <p class="mt-1 text-xs text-neutral-500">{{ $record['date']?->format('M j, Y g:i A') ?? 'Date not set' }}</p>
                                @if ($referralTracking->isNotEmpty())
                                    <p class="mt-2 text-xs font-semibold text-purple-700 dark:text-purple-300">Referral tracking available above</p>
                                @endif
                            </div>
                        </div>
                        @if (in_array($record['status'], ['done', 'missed'], true))
                            <form method="POST" action="{{ route('appointments.action-taken.update', $record['appointment_id']) }}" class="mt-4 rounded-xl border border-green-200 bg-green-50/70 p-4 dark:border-green-900 dark:bg-green-950/20">
                                @csrf
                                @method('PATCH')
                                <label for="history_concerns_edit_{{ $record['appointment_id'] }}" class="block text-sm font-semibold">Concerns</label>
                                <textarea id="history_concerns_edit_{{ $record['appointment_id'] }}" name="concerns" rows="2" maxlength="10000" class="mt-2 w-full rounded-lg border border-green-200 bg-white p-3 text-sm dark:border-green-900 dark:bg-zinc-800">{{ $record['details'] }}</textarea>
                                <label for="history_action_taken_edit_{{ $record['appointment_id'] }}" class="mt-3 block text-sm font-semibold">Interventions / Actions Taken</label>
                                <textarea id="history_action_taken_edit_{{ $record['appointment_id'] }}" name="action_taken" rows="3" maxlength="10000" required class="mt-2 w-full rounded-lg border border-green-200 bg-white p-3 text-sm dark:border-green-900 dark:bg-zinc-800">{{ $record['action_taken'] }}</textarea>
                                @error('action_taken')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="mt-3 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Done</button>
                            </form>
                        @endif
                        @if ($record['status'] === 'accepted')
                            <form method="POST" action="{{ route('appointments.complete', $record['appointment_id']) }}" class="mt-4 rounded-xl border border-green-200 bg-green-50/70 p-4 dark:border-green-900 dark:bg-green-950/20">
                                @csrf
                                <label for="history_concerns_{{ $record['appointment_id'] }}" class="block text-sm font-semibold">Concerns</label>
                                <textarea id="history_concerns_{{ $record['appointment_id'] }}" name="concerns" rows="2" maxlength="10000" class="mt-2 w-full rounded-lg border border-green-200 bg-white p-3 text-sm dark:border-green-900 dark:bg-zinc-800">{{ $record['details'] }}</textarea>
                                <label for="history_action_taken_{{ $record['appointment_id'] }}" class="mt-3 block text-sm font-semibold">Interventions / Actions Taken</label>
                                <textarea id="history_action_taken_{{ $record['appointment_id'] }}" name="action_taken" rows="3" maxlength="10000" required class="mt-2 w-full rounded-lg border border-green-200 bg-white p-3 text-sm dark:border-green-900 dark:bg-zinc-800" placeholder="Write the action taken during this appointment."></textarea>
                                <button type="submit" class="mt-3 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Done</button>
                            </form>
                            <form method="POST" action="{{ route('appointments.missed', $record['appointment_id']) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950/30">Client failed to attend</button>
                            </form>
                        @endif
                    </article>
                @empty
                    <p class="px-6 py-12 text-center text-sm text-neutral-500">This student has no appointment history.</p>
                @endforelse
            </details>

            <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    <h2 class="font-semibold">Other Records <span class="ml-1 text-sm font-normal text-neutral-500">({{ $otherRecords->count() }})</span></h2>
                    <p class="mt-1 text-sm text-neutral-500">Slip submissions and other student records.</p>
                </div>
                @php($referralNumber = 0)
                @forelse ($otherRecords->groupBy(fn (array $record): string => $record['bundle_id'] ?? 'unbundled') as $bundleKey => $bundleRecords)
                    @php($isReferralBundle = $bundleKey !== 'unbundled')
                    @if ($isReferralBundle) @php($referralNumber++) @endif
                    <details class="group border-b border-neutral-200 last:border-b-0 dark:border-neutral-700">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-neutral-50 px-6 py-4 transition hover:bg-neutral-100 dark:bg-neutral-800 dark:hover:bg-neutral-700">
                            <span class="flex items-center gap-3"><span class="text-xl" aria-hidden="true">📁</span><span><span class="block text-sm font-semibold">{{ $isReferralBundle ? $student->name : 'Other records' }}</span><span class="block text-xs text-neutral-500 dark:text-neutral-400">{{ $isReferralBundle ? 'Referral '.$referralNumber : 'Not part of a referral bundle' }}</span></span></span>
                            <span class="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400">{{ $bundleRecords->count() }} {{ str()->plural('record', $bundleRecords->count()) }} <span class="text-base transition group-open:rotate-180" aria-hidden="true">⌄</span></span>
                        </summary>
                        <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            @foreach ($bundleRecords as $record)
                                <article class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-medium dark:bg-neutral-800">{{ $record['type'] }}</span><h3 class="font-semibold">{{ $record['title'] }}</h3></div>@if ($record['details'])<p class="mt-2 max-w-2xl text-sm text-neutral-600 dark:text-neutral-300">{{ $record['details'] }}</p>@endif<p class="mt-1 text-xs text-neutral-500">{{ $record['date']?->format('M j, Y g:i A') ?? 'Date not set' }}</p></div>
                                    <div class="flex shrink-0 items-center gap-3"><span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold capitalize text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ str_replace('_', ' ', $record['status']) }}</span>@if ($record['url'])<a href="{{ $record['url'] }}" class="text-sm font-medium text-[#28643a] hover:underline dark:text-[#91dfa6]">View</a>@endif</div>
                                </article>
                            @endforeach
                        </div>
                    </details>
                @empty
                    <p class="px-6 py-12 text-center text-sm text-neutral-500">This student has no other records.</p>
                @endforelse
            </section>
        </div>
    </main>
</x-layouts::app>

<x-layouts::app :title="__('Student Dashboard')">
    <main class="min-h-screen bg-[#f2f8f4] p-5 text-slate-900 transition-colors duration-200 dark:bg-zinc-950 dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header>
                <h1 class="text-2xl font-extrabold text-[#0fa344] dark:text-[#54d180]">
                    Student Dashboard
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                    Welcome, {{ auth()->user()->name }}.
                </p>
            </header>

            <section>
                <h2 class="mb-4 text-lg font-bold">Activities</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <article class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-[0_8px_24px_rgba(15,163,68,0.07)] transition-shadow hover:shadow-[0_12px_30px_rgba(15,163,68,0.12)] dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm text-slate-500 dark:text-zinc-400">Pending Assigned Slips</p>
                        <p class="mt-2 text-3xl font-bold">{{ $pendingAssignmentsCount }}</p>
                        <a href="{{ route('assigned-slips.index') }}" class="mt-4 inline-block text-xs font-semibold text-[#0fa344] hover:underline dark:text-[#54d180]">
                            View assigned slips →
                        </a>
                    </article>

                    <article class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-[0_8px_24px_rgba(15,163,68,0.07)] transition-shadow hover:shadow-[0_12px_30px_rgba(15,163,68,0.12)] dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm text-slate-500 dark:text-zinc-400">Awaiting Approval</p>
                        <p class="mt-2 text-3xl font-bold">{{ $awaitingApprovalCount }}</p>
                        <a href="{{ route('slip-history.index') }}" class="mt-4 inline-block text-xs font-semibold text-[#0fa344] hover:underline dark:text-[#54d180]">
                            View submitted slips →
                        </a>
                    </article>

                    <article class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-[0_8px_24px_rgba(15,163,68,0.07)] transition-shadow hover:shadow-[0_12px_30px_rgba(15,163,68,0.12)] dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm text-slate-500 dark:text-zinc-400">Submitted Slips</p>
                        <p class="mt-2 text-3xl font-bold">{{ $submittedSlipsCount }}</p>
                        <a href="{{ route('slip-history.index') }}" class="mt-4 inline-block text-xs font-semibold text-[#0fa344] hover:underline dark:text-[#54d180]">
                            View slip history →
                        </a>
                    </article>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,163,68,0.07)] dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-lg font-bold">Assigned Slips</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                        Forms assigned to you by a teacher.
                    </p>

                    @if ($assignments->isEmpty())
                        <p class="mt-5 rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">
                            You do not have any assigned slips yet.
                        </p>
                    @else
                        <ul class="mt-4 divide-y divide-emerald-100 dark:divide-zinc-800">
                            @foreach ($assignments as $assignment)
                                @php
                                    $title = $assignment->slip === 'cns'
                                        ? 'Certificate of No Scholarship'
                                        : ($slips[$assignment->slip]['title'] ?? 'Assigned Slip');
                                @endphp
                                <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                                    <div>
                                        <p class="font-medium">{{ $title }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                                            @if ($assignment->assigned_by === auth()->id())
                                                Requested by you from Guidance
                                            @else
                                                Sent by {{ $assignment->assignedBy->name }}
                                            @endif
                                            · {{ $assignment->created_at->format('M j, Y') }}
                                        </p>
                                    </div>

                                    @if (in_array($assignment->status, ['pending', 'submitted'], true))
                                        <a href="{{ route('assigned-slips.fillup', $assignment) }}" class="rounded-lg bg-[#0fa344] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0b8537] dark:bg-[#54d180] dark:text-zinc-950 dark:hover:bg-[#6be495]">
                                            {{ $assignment->status === 'submitted' ? 'Edit submission' : 'Fill up' }}
                                        </a>
                                    @elseif ($assignment->slip === 'cns' && $assignment->cnsRequest)
                                        <a href="{{ route('cns.requests.show', $assignment->cnsRequest) }}" class="text-xs font-semibold text-[#0fa344] hover:underline dark:text-[#54d180]">
                                            View completed
                                        </a>
                                    @elseif ($assignment->slipSubmission)
                                        <a href="{{ route('slips.submissions.show', $assignment->slipSubmission) }}" class="text-xs font-semibold text-[#0fa344] hover:underline dark:text-[#54d180]">
                                            View completed
                                        </a>
                                    @else
                                        <span class="text-xs font-medium text-gray-500">{{ $assignment->status === 'approved' ? 'Approved' : ucfirst($assignment->status) }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <a href="{{ route('assigned-slips.index') }}" class="mt-3 inline-block text-sm font-semibold text-[#0fa344] hover:underline dark:text-[#54d180]">
                        View all assigned slips →
                    </a>
                </section>

                <section class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,163,68,0.07)] dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold">Slip History</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                                Your recently completed and submitted forms.
                            </p>
                        </div>
                        <a href="{{ route('slip-history.index') }}" class="text-sm font-semibold text-[#0fa344] hover:underline dark:text-[#54d180]">
                            View all
                        </a>
                    </div>

                    @if ($history->isEmpty())
                        <p class="mt-5 rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">
                            You have not submitted any slips yet.
                        </p>
                    @else
                        <ul class="mt-4 divide-y divide-emerald-100 dark:divide-zinc-800">
                            @foreach ($history as $entry)
                                <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                                    <div>
                                        <p class="font-medium">{{ $entry['title'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                                            {{ $entry['date']->format('M j, Y g:i A') }}
                                            · {{ ucfirst($entry['status']) }}
                                        </p>
                                    </div>
                                    <a href="{{ $entry['url'] }}" class="text-xs font-semibold text-[#0fa344] hover:underline dark:text-[#54d180]">
                                        View
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            <section class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,163,68,0.07)] dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-bold">Request a Form from Guidance</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                    Request these forms directly from the Guidance Office, then complete and submit them for counselor review.
                </p>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    @foreach ($requestableForms as $slug => $form)
                        <article class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4 transition hover:border-[#0fa344] dark:border-zinc-700 dark:bg-zinc-800/40 dark:hover:border-[#54d180]">
                            <h3 class="font-semibold">{{ $form['title'] }}</h3>
                            @if (in_array($slug, $activeRequestSlugs, true))
                                <p class="mt-3 text-sm text-amber-700 dark:text-amber-400">
                                    You already have an active request for this form.
                                </p>
                            @else
                                <form method="POST" action="{{ route('student.slip-requests.store', $slug) }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-[#0fa344] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0b8537] dark:bg-[#54d180] dark:text-zinc-950 dark:hover:bg-[#6be495]">
                                        Request from Guidance
                                    </button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-[0_8px_24px_rgba(15,163,68,0.07)] dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-bold">Appointment Guidance Counseling</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                    Appointment scheduling is reserved for a future update. No sample appointment data is shown.
                </p>
            </section>
        </div>
    </main>
</x-layouts::app>



<x-layouts::app :title="__('Teacher Dashboard')">
    <main class="min-h-screen bg-[#f6f5f1] p-5 text-slate-900 dark:bg-[#111712] dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="rounded-3xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-lime-50 p-6 dark:border-emerald-800 dark:from-zinc-900 dark:to-emerald-950">
                <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Teacher portal</p>
                <h1 class="mt-2 text-2xl font-extrabold text-[#28643a] dark:text-[#91dfa6]">Teacher Dashboard</h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-zinc-300">Refer students to the Guidance Office and track their referral progress.</p>
                <a href="{{ route('slips.fillup', 'student-referral-slip') }}" class="mt-4 inline-flex rounded-lg bg-[#09ba44] px-4 py-2 text-sm font-semibold text-white hover:bg-[#079b38]">Create Student Referral</a>
            </header>
            @if (session('status'))<div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900 dark:border-green-900 dark:bg-green-950/50 dark:text-green-200">{{ session('status') }}</div>@endif
            @php
                $awaitingCount = $referrals->whereIn('status', ['pending', 'submitted'])->count();
                $approvedCount = $referrals->where('status', 'approved')->count();
            @endphp
            <section>
                <h2 class="mb-4 text-lg font-bold text-slate-800 dark:text-zinc-100">Activities</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <article class="rounded-2xl border border-emerald-300 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Recent Referrals</p><p class="mt-2 text-3xl font-bold">{{ $referrals->count() }}</p><a href="{{ route('teacher.referrals-sent') }}" class="mt-4 inline-block text-xs font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]">View active referrals →</a></article>
                    <article class="rounded-2xl border border-amber-300 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm font-medium text-amber-700 dark:text-amber-300">Awaiting Response</p><p class="mt-2 text-3xl font-bold">{{ $awaitingCount }}</p><a href="{{ route('teacher.referrals-sent') }}" class="mt-4 inline-block text-xs font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]">View active referrals →</a></article>
                    <article class="rounded-2xl border border-sky-300 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm font-medium text-sky-700 dark:text-sky-300">Approved Referrals</p><p class="mt-2 text-3xl font-bold">{{ $approvedCount }}</p><a href="{{ route('teacher.referral-history') }}" class="mt-4 inline-block text-xs font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]">View referral history →</a></article>
                </div>
            </section>
            <div class="grid gap-5 md:grid-cols-2">
                <section class="rounded-2xl border border-emerald-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-lg font-bold">Student Referral Slip</h2><p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Create a referral and send it to the student and Guidance.</p>
                    <a href="{{ route('slips.fillup', 'student-referral-slip') }}" class="mt-4 inline-flex font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]">Create a referral →</a>
                </section>
                <section class="rounded-2xl border border-sky-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-lg font-bold">Referral History</h2><p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">View referrals after acknowledging the issued Class Admission result.</p>
                    <a href="{{ route('teacher.referral-history') }}" class="mt-4 inline-flex font-semibold text-[#28643a] hover:underline dark:text-[#91dfa6]">View referral history →</a>
                </section>
            </div>
            @if ($referralAppointments->isNotEmpty())
                <section class="rounded-2xl border border-blue-200 bg-white p-6 dark:border-blue-900 dark:bg-zinc-900">
                    <h2 class="text-lg font-bold">Guidance appointments</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Schedule and status for appointments created from your referrals. Counselor CIF notes are private.</p>
                    <div class="mt-4 divide-y divide-slate-200 dark:divide-zinc-700">
                        @foreach ($referralAppointments as $appointment)
                            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                                <div><p class="font-semibold">{{ $appointment->requester->name }}</p><p class="text-sm text-slate-500 dark:text-zinc-400">{{ $appointment->scheduled_at?->format('M j, Y g:i A') }} · {{ $appointment->duration_minutes }} minutes</p></div>
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold capitalize text-blue-800 dark:bg-blue-950/50 dark:text-blue-200">{{ $appointment->status }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </main>
</x-layouts::app>

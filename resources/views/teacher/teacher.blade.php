<x-layouts::app :title="__('Teacher Dashboard')">
    <main class="min-h-screen bg-green-50 p-5 text-gray-900 dark:bg-zinc-950 dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <header class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Teacher Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                        Refer a student to the Guidance Office using the Student Referral Slip.
                    </p>
                </div>
                <a href="{{ route('slips.index', ['preview' => 'student-referral-slip']) }}" class="rounded-lg bg-[#176b3a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#12552e]">
                    Create Student Referral
                </a>
            </header>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div>
                    <h2 class="text-lg font-bold">Student Referral Slip</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                        Select the student who needs support. The student completes the assigned slip and submits it to Guidance.
                    </p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('slips.index', ['preview' => 'student-referral-slip']) }}" class="inline-flex rounded-lg border border-green-700 px-4 py-2 text-sm font-semibold text-green-800 hover:bg-green-50 dark:text-green-300 dark:hover:bg-zinc-800">
                        Preview and assign
                    </a>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div>
                    <h2 class="text-lg font-bold">Referrals I Sent</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                        Current status of your Student Referral Slip assignments.
                    </p>
                </div>

                @if ($referrals->isEmpty())
                    <p class="mt-5 rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">
                        You have not sent any student referrals yet.
                    </p>
                @else
                    <ul class="mt-4 divide-y divide-gray-100 dark:divide-zinc-800">
                        @foreach ($referrals as $referral)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                                <div>
                                    <p class="font-medium">{{ $referral->student->name }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">
                                        {{ $referral->created_at->format('M j, Y') }} · {{ ucfirst($referral->status) }}
                                    </p>
                                </div>
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-medium',
                                    'bg-amber-100 text-amber-800' => in_array($referral->status, ['pending', 'submitted'], true),
                                    'bg-green-100 text-green-800' => $referral->status === 'approved',
                                ])>
                                    {{ $referral->status === 'pending' ? 'Awaiting student' : ($referral->status === 'submitted' ? 'Received by Guidance' : 'Approved') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </main>
</x-layouts::app>

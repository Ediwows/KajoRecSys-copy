<x-layouts::app :title="__('Guidance Counselor Dashboard')">
    <main class="min-h-screen bg-[#f5f8f6] p-5 text-gray-900 dark:bg-zinc-950 dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <header class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Guidance Counselor Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                        Receive student referrals and issue only the follow-up forms each student needs.
                    </p>
                </div>
                <a href="{{ route('guidance.slip-review') }}" class="rounded-lg bg-[#176b3a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#12552e]">
                    Review submissions ({{ $pendingApprovalsCount }})
                </a>
            </header>

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                    {{ session('status') }}
                </div>
            @endif

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div>
                    <h2 class="text-lg font-bold">Received Student Referral Slips</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                        Issue a Client Information Form only to first-time students. A Class Admission Slip may be issued for each referral.
                    </p>
                </div>

                @if ($referrals->isEmpty())
                    <p class="mt-5 rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">
                        There are no received referrals needing follow-up forms.
                    </p>
                @else
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-zinc-800 dark:text-zinc-400">
                                <tr>
                                    <th class="px-4 py-3">Student</th>
                                    <th class="px-4 py-3">Referral received</th>
                                    <th class="px-4 py-3">Client Information Form</th>
                                    <th class="px-4 py-3">Class Admission Slip</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @foreach ($referrals as $row)
                                    @php($referral = $row['assignment'])
                                    <tr>
                                        <td class="px-4 py-4">
                                            <p class="font-semibold">{{ $referral->student->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-zinc-400">{{ $referral->student->id_number }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-gray-600 dark:text-zinc-300">
                                            {{ $referral->completed_at?->format('M j, Y g:i A') ?? 'Received' }}
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($row['can_issue_client_information'])
                                                <form method="POST" action="{{ route('guidance.slip-review.issue', [$referral, 'qf-bo-oss-04-client-information-form']) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg border border-green-700 px-3 py-2 text-xs font-semibold text-green-800 hover:bg-green-50 dark:text-green-300 dark:hover:bg-zinc-800">
                                                        Issue CIF (first-time)
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-500 dark:text-zinc-400">Not needed — CIF already issued</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($row['can_issue_class_admission'])
                                                <form method="POST" action="{{ route('guidance.slip-review.issue', [$referral, 'class-admission-slip']) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg bg-[#176b3a] px-3 py-2 text-xs font-semibold text-white hover:bg-[#12552e]">
                                                        Issue Class Admission
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-500 dark:text-zinc-400">Already issued for this referral</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </main>
</x-layouts::app>

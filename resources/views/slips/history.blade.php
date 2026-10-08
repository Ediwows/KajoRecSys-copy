<x-layouts::app :title="__('Slip History')">
    <main class="min-h-screen bg-zinc-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <div>
                <flux:heading size="xl">Slip History</flux:heading>
                <flux:text class="mt-2 text-zinc-600">
                    Review the forms you have completed and submitted.
                </flux:text>
            </div>

            @if ($history->isEmpty())
                <section class="rounded-xl bg-white p-6 text-sm text-zinc-600 shadow-sm ring-1 ring-zinc-200">
                    You have not completed any assigned slips yet.
                </section>
            @else
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-200">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-zinc-100 text-xs uppercase text-zinc-600">
                                <tr>
                                    <th class="px-5 py-3">Form</th>
                                    <th class="px-5 py-3">Submitted</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @foreach ($history as $entry)
                                    <tr>
                                        <td class="px-5 py-4 font-medium text-zinc-900">{{ $entry['title'] }}</td>
                                        <td class="px-5 py-4 text-zinc-600">{{ $entry['submitted_at']->format('M j, Y g:i A') }}</td>
                                        <td class="px-5 py-4 text-zinc-600">{{ ucfirst($entry['status']) }}</td>
                                        <td class="space-x-4 px-5 py-4 text-right">
                                            <a href="{{ $entry['show_url'] }}" class="font-semibold text-green-700 hover:underline">
                                                View
                                            </a>
                                            <a href="{{ $entry['pdf_url'] }}" class="font-semibold text-green-700 hover:underline">
                                                PDF
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </main>
</x-layouts::app>

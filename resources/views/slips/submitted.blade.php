<x-layouts::app :title="__('Slip Request Submitted')">
    <main class="min-h-screen bg-zinc-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-6">
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-200">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading size="xl">Request Submitted</flux:heading>
                        <flux:text class="mt-2 text-zinc-600">
                            Your {{ $slip['title'] }} was submitted with status: {{ ucfirst($submission->review_status) }}.
                            @if ($submission->review_status !== 'approved')
                                You can edit it until it is approved by the Guidance Office.
                            @endif
                        </flux:text>
                    </div>
                    <div class="flex gap-3">
                        @if ($assignment && $submission->review_status !== 'approved' && auth()->user()->role === 'student' && !($submission->slip === 'student-referral-slip' && ($submission->data['submitted_by_teacher'] ?? false)))
                            <a
                                href="{{ route('assigned-slips.fillup', $assignment) }}"
                                class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-800"
                            >
                                Edit submission
                            </a>
                        @endif
                        <a
                            href="{{ auth()->user()->role === 'student' ? route('student.dashboard') : route('slips.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                        >
                            Done
                        </a>
                        @if ($submission->slip === 'qf-bo-oss-04-client-information-form')
                            <a
                                href="{{ route('slips.submissions.docx', $submission) }}"
                                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700"
                            >
                                Open Word CIF
                            </a>
                        @elseif ($referralPdfReady)
                            <a
                                href="{{ route('slips.submissions.pdf', $submission) }}"
                                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700"
                            >
                                Open PDF
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            @if ($submission->slip === 'class-admission-slip' && !empty($submission->data['_attachments']))
                <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-200">
                    <flux:heading size="lg">Supporting evidence</flux:heading>
                    <flux:text class="mt-1 text-zinc-600">Attachments submitted with this Class Admission Slip.</flux:text>
                    <ul class="mt-4 space-y-2">
                        @foreach ($submission->data['_attachments'] as $index => $attachment)
                            <li class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 text-sm">
                                <span class="truncate">{{ $attachment['original_name'] ?? 'Attachment' }}</span>
                                <a href="{{ route('slips.submissions.attachment', [$submission, $index]) }}" target="_blank" class="shrink-0 font-semibold text-green-700 hover:underline">Open attachment</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if (auth()->user()->role === 'guidance_counselor' && $submission->slip === 'class-admission-slip' && $submission->review_status !== 'approved' && $assignment)
                <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-200">
                    <div class="mb-5">
                        <flux:heading size="lg">Guidance Office Review</flux:heading>
                        <flux:text class="mt-1 text-zinc-600">
                            Complete the office section and approve the Class Admission Slip.
                        </flux:text>
                    </div>

                    @if ($errors->any())
                        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('guidance.slip-review.approve', $assignment) }}" class="space-y-5">
                        @csrf
                        @foreach ($guidanceFields as $field)
                            @if ($field['type'] === 'checkboxes')
                                @php($selectedValues = old('details.'.$field['key'], $submission->data[$field['key']] ?? []))
                                @php($selectedValues = is_array($selectedValues) ? $selectedValues : (filled($selectedValues) ? [$selectedValues] : []))
                                <fieldset class="space-y-2">
                                    <legend class="text-sm font-medium text-zinc-700">
                                        {{ $field['label'] }}{{ $field['required'] ? ' *' : '' }}
                                    </legend>
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        @foreach ($field['options'] as $value => $label)
                                            <label class="flex items-center gap-2 text-sm text-zinc-700">
                                                <input
                                                    type="checkbox"
                                                    name="details[{{ $field['key'] }}][]"
                                                    value="{{ $value }}"
                                                    @checked(in_array($value, $selectedValues, true))
                                                    class="rounded border-zinc-300 text-emerald-700 focus:ring-emerald-700"
                                                >
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @elseif ($field['type'] === 'select')
                                <label class="block space-y-2">
                                    <span class="text-sm font-medium text-zinc-700">{{ $field['label'] }}{{ $field['required'] ? ' *' : '' }}</span>
                                    <select
                                        name="details[{{ $field['key'] }}]"
                                        @required($field['required'])
                                        class="w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 shadow-sm focus:border-green-700 focus:ring-green-700"
                                    >
                                        <option value="">Choose an option</option>
                                        @foreach ($field['options'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('details.'.$field['key'], $submission->data[$field['key']] ?? '') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @else
                                <flux:input
                                    name="details[{{ $field['key'] }}]"
                                    label="{{ $field['label'] }}"
                                    type="{{ $field['type'] }}"
                                    value="{{ old('details.'.$field['key'], $submission->data[$field['key']] ?? null) }}"
                                    :required="$field['required']"
                                />
                            @endif
                        @endforeach

                        <div class="flex justify-end">
                            <flux:button variant="primary" type="submit">Save Guidance Details &amp; Approve</flux:button>
                        </div>
                    </form>
                </section>
            @endif

            @if ($submission->slip === 'qf-bo-oss-04-client-information-form' || $referralPdfReady)
                <iframe
                    src="{{ route('slips.submissions.pdf', $submission) }}"
                    title="Completed form PDF"
                    class="h-[80vh] w-full rounded-lg border border-zinc-300 bg-white"
                ></iframe>
            @elseif ($submission->slip === 'student-referral-slip')
                <section class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
                    The referral PDF is not printable yet. Guidance can issue the Class Admission Slip while waiting for the document-control values. The PDF will be available here after all four values are entered.
                </section>
                <iframe
                    src="{{ route('slips.submissions.preview-pdf', $submission) }}#toolbar=0&navpanes=0"
                    title="Referral form preview"
                    class="h-[80vh] w-full rounded-xl border border-amber-200 bg-white"
                ></iframe>
            @endif
        </div>
    </main>
</x-layouts::app>

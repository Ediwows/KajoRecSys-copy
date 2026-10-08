<x-layouts::app :title="$slip['title']">
    <main class="min-h-screen bg-zinc-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <div class="mb-6">
                <flux:heading size="xl">{{ $slip['title'] }}</flux:heading>
                <flux:text class="mt-2 text-zinc-600">
                    Fill out the sections intended for you. You can edit this submission until it is approved by the Guidance Office.
                </flux:text>
            </div>

            @if ($selectedStudent)
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                    Assigned to: <strong>{{ $selectedStudent->name }}</strong>
                    <span class="ml-2">{{ $selectedStudent->id_number }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <p class="font-medium">Please correct the following:</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('assigned-slips.submit', $assignment) }}" class="space-y-6">
                @csrf
                @if ($selectedStudent)
                    <input type="hidden" name="referred_student_id" value="{{ $selectedStudent->id }}">
                @endif

                @foreach ($slip['groups'] as $group)
                    <section class="space-y-5 rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-200">
                        <flux:heading size="lg">{{ $group['title'] }}</flux:heading>

                        <div class="grid gap-5 sm:grid-cols-2">
                            @foreach ($group['fields'] as $field)
                                <div class="{{ in_array($field['type'], ['textarea', 'checkboxes'], true) ? 'sm:col-span-2' : '' }}">
                                    @if ($field['type'] === 'textarea')
                                        <flux:textarea
                                            name="details[{{ $field['key'] }}]"
                                            label="{{ $field['label'] }}"
                                            rows="{{ max(3, $field['rows']) }}"
                                            :required="$field['required']"
                                        >{{ old('details.'.$field['key'], $existingSubmission?->data[$field['key']] ?? null) }}</flux:textarea>
                                    @elseif ($field['type'] === 'checkboxes')
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
                                                            @checked(in_array($value, old('details.'.$field['key'], $existingSubmission?->data[$field['key']] ?? []), true))
                                                            class="rounded border-zinc-300 text-green-700 focus:ring-green-700"
                                                        >
                                                        <span>{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </fieldset>
                                    @else
                                        <flux:input
                                            name="details[{{ $field['key'] }}]"
                                            label="{{ $field['label'] }}"
                                            type="{{ $field['type'] }}"
                                            value="{{ old('details.'.$field['key'], $existingSubmission?->data[$field['key']] ?? $defaults[$field['key']] ?? null) }}"
                                            :required="$field['required']"
                                        />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div class="flex justify-end gap-3">
                    <a
                        href="{{ route('student.dashboard') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                    >
                        Cancel
                    </a>
                    <flux:button variant="primary" type="submit">
                        {{ $existingSubmission ? 'Save Changes' : 'Submit' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </main>
</x-layouts::app>

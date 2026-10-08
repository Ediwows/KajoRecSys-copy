<x-layouts::app :title="__('Select a Student')">
    <main class="min-h-screen bg-zinc-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 sm:p-8">
                <div class="mb-6">
                    <flux:heading size="xl">Select a Student</flux:heading>
                    <flux:text class="mt-2 text-zinc-600">
                        Choose the student who should receive {{ $title }}.
                    </flux:text>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if ($students->isEmpty())
                    <p class="rounded-lg bg-amber-50 p-4 text-sm text-amber-900">
                        There are no student accounts available to select.
                    </p>
                    <div class="mt-6 flex justify-end">
                        <a
                            href="{{ $cancelUrl }}"
                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                        >
                            Cancel
                        </a>
                    </div>
                @else
                    <form method="POST" action="{{ $selectAction }}" class="space-y-6">
                        @csrf
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-zinc-700">Student</span>
                            <select
                                name="student_id"
                                required
                                class="w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-green-700 focus:ring-green-700"
                            >
                                <option value="">Choose a student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>
                                        {{ $student->name }} — {{ $student->id_number ?: 'No ID number' }} — {{ $student->email }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <div class="flex justify-end gap-3">
                            <a
                                href="{{ $cancelUrl }}"
                                class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                            >
                                Cancel
                            </a>
                            <flux:button variant="primary" type="submit">Send Form</flux:button>
                        </div>
                    </form>
                @endif
            </section>
        </div>
    </main>
</x-layouts::app>

<x-layouts::app :title="__('CNS Request Form')">
    <main class="min-h-screen bg-zinc-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <div class="mb-6">
                <a
                    href="{{ route('student.dashboard') }}"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900"
                >
                    &larr; Cancel
                </a>
            </div>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 sm:p-8">
                <div class="mb-6">
                    <flux:heading size="xl">Certificate of No Scholarship Request</flux:heading>
                    <flux:text class="mt-2 text-zinc-600">
                        Enter your information below. You can edit this request until it is approved by the Guidance Office.
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

                <form method="POST" action="{{ route('assigned-slips.cns-submit', $assignment) }}" class="space-y-5">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <flux:input
                            name="last_name"
                            label="Last Name"
                            placeholder="Enter your last name"
                            value="{{ old('last_name', $existingCnsRequest?->last_name ?? $defaults['last_name'] ?? null) }}"
                            required
                        />
                        <flux:input
                            name="first_name"
                            label="First Name"
                            placeholder="Enter your first name"
                            value="{{ old('first_name', $existingCnsRequest?->first_name ?? $defaults['first_name'] ?? null) }}"
                            required
                        />
                        <flux:input
                            name="middle_initial"
                            label="Middle Initial"
                            placeholder="Optional"
                            value="{{ old('middle_initial', $existingCnsRequest?->middle_initial ?? $defaults['middle_initial'] ?? null) }}"
                        />
                        <flux:input
                            name="student_id"
                            label="Student ID"
                            placeholder="Enter your student ID"
                            value="{{ old('student_id', $existingCnsRequest?->student_id ?? $defaults['student_id'] ?? null) }}"
                            required
                        />
                        <flux:input
                            name="email"
                            label="Email Address"
                            type="email"
                            placeholder="Enter your email"
                            value="{{ old('email', $existingCnsRequest?->email ?? $defaults['email'] ?? null) }}"
                            required
                        />
                        <flux:input
                            name="phone"
                            label="Phone Number"
                            type="tel"
                            placeholder="Enter your contact number"
                            value="{{ old('phone', $existingCnsRequest?->phone) }}"
                        />
                        <flux:select name="degree" label="College Degree" required>
                            <flux:select.option value="">Select your degree</flux:select.option>
                            <flux:select.option value="BSCrim" :selected="old('degree', $existingCnsRequest?->degree) === 'BSCrim'">BS Criminology</flux:select.option>
                            <flux:select.option value="BIT" :selected="old('degree', $existingCnsRequest?->degree) === 'BIT'">Bachelor of Industrial Technology (BIT)</flux:select.option>
                            <flux:select.option value="BSIT" :selected="old('degree', $existingCnsRequest?->degree) === 'BSIT'">BS Information Technology</flux:select.option>
                            <flux:select.option value="BSEntrep" :selected="old('degree', $existingCnsRequest?->degree) === 'BSEntrep'">BS Entrepreneurship</flux:select.option>
                            <flux:select.option value="BPA" :selected="old('degree', $existingCnsRequest?->degree) === 'BPA'">Bachelor of Public Administration</flux:select.option>
                            <flux:select.option value="BTVTED" :selected="old('degree', $existingCnsRequest?->degree) === 'BTVTED'">BTVTED</flux:select.option>
                            <flux:select.option value="BTLED" :selected="old('degree', $existingCnsRequest?->degree) === 'BTLED'">BTLED</flux:select.option>
                            <flux:select.option value="BSE" :selected="old('degree', $existingCnsRequest?->degree) === 'BSE'">BS Education</flux:select.option>
                            <flux:select.option value="BCAE" :selected="old('degree', $existingCnsRequest?->degree) === 'BCAE'">BCAE</flux:select.option>
                            <flux:select.option value="BEE" :selected="old('degree', $existingCnsRequest?->degree) === 'BEE'">BEE</flux:select.option>
                            <flux:select.option value="BSIE" :selected="old('degree', $existingCnsRequest?->degree) === 'BSIE'">BSIE</flux:select.option>
                        </flux:select>
                        <flux:select name="year_level" label="Year Level" required>
                            <flux:select.option value="">Select your year level</flux:select.option>
                            <flux:select.option value="First" :selected="old('year_level', $existingCnsRequest?->year_level) === 'First'">First</flux:select.option>
                            <flux:select.option value="Second" :selected="old('year_level', $existingCnsRequest?->year_level) === 'Second'">Second</flux:select.option>
                            <flux:select.option value="Third" :selected="old('year_level', $existingCnsRequest?->year_level) === 'Third'">Third</flux:select.option>
                            <flux:select.option value="Fourth" :selected="old('year_level', $existingCnsRequest?->year_level) === 'Fourth'">Fourth</flux:select.option>
                        </flux:select>
                    </div>

                    <flux:input
                        name="school_year"
                        label="Term & School Year Graduated or Last Attended"
                        placeholder="For example: First Semester, 2025-2026"
                        value="{{ old('school_year', $existingCnsRequest?->school_year) }}"
                        required
                    />

                    <flux:select name="purpose" label="Specific Purpose of Certification" required>
                        <flux:select.option value="">Select the purpose</flux:select.option>
                        <flux:select.option value="educational_assistance" :selected="old('purpose', $existingCnsRequest?->purpose) === 'educational_assistance'">Educational Assistance Application</flux:select.option>
                        <flux:select.option value="scholarship_application" :selected="old('purpose', $existingCnsRequest?->purpose) === 'scholarship_application'">Scholarship Application</flux:select.option>
                        <flux:select.option value="other" :selected="old('purpose', $existingCnsRequest?->purpose) === 'other'">Other</flux:select.option>
                    </flux:select>

                    <flux:input
                        name="purpose_other"
                        label="Please specify if you selected Other"
                        placeholder="Enter the purpose"
                        value="{{ old('purpose_other', $existingCnsRequest?->purpose_other) }}"
                    />

                    <div class="flex justify-end gap-3">
                        <a
                            href="{{ route('assigned-slips.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                        >
                            Cancel
                        </a>
                        <flux:button variant="primary" type="submit">
                            {{ $existingCnsRequest ? 'Save Changes' : 'Submit' }}
                        </flux:button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</x-layouts::app>

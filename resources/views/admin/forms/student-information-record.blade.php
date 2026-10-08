<x-layouts::app :title="__('Student Information Request')">

    <div class="min-h-screen bg-[#f5f8f6] px-4 py-8 sm:px-6 lg:px-8">

        {{-- PAGE HEADER --}}
        <div class="mx-auto mb-6 max-w-5xl">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl" class="text-[#176b3a]">
                        Student Information Request
                    </flux:heading>

                    <flux:text class="mt-1 text-zinc-600">
                        Complete the form below to request student information.
                    </flux:text>
                </div>

                <a
                    href="{{ route('guidance.forms') }}"
                    class="hidden rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 sm:inline-flex"
                >
                    ← Back to Forms
                </a>
            </div>
        </div>


        {{-- FORM DOCUMENT --}}
        <div class="mx-auto max-w-5xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 sm:p-10">

            {{-- ========================================== --}}
            {{-- SCHOOL HEADER --}}
            {{-- ========================================== --}}

            <div class="border-b-4 border-zinc-700 pb-4">

                <div class="flex items-center gap-4">

                    <img
                        src="{{ asset('images/BSUB-logo.jpg') }}"
                        alt="Benguet State University Logo"
                        class="h-16 w-16 object-contain"
                    >

                    <div>
                        <p class="text-sm font-medium text-zinc-700">
                            BENGUET STATE UNIVERSITY
                        </p>

                        <p class="text-xs text-zinc-600">
                            Bokod Campus
                        </p>

                        <p class="text-xs text-zinc-600">
                            Guidance Office
                        </p>
                    </div>

                </div>

            </div>


            {{-- ========================================== --}}
            {{-- FORM TITLE --}}
            {{-- ========================================== --}}

            <div class="py-6 text-center">

                <h1 class="text-xl font-bold uppercase tracking-wide text-zinc-800">
                    Student Information Request
                </h1>

                <p class="mx-auto mt-3 max-w-3xl text-sm leading-6 text-zinc-600">
                    This form is used to request student information from the
                    Guidance Office. Please provide the necessary information
                    and indicate the purpose of the request.
                </p>

            </div>


            <form method="POST" action="#">

                @csrf


                {{-- ========================================== --}}
                {{-- REQUESTER INFORMATION --}}
                {{-- ========================================== --}}

                <div class="mb-6 overflow-hidden rounded-lg border border-zinc-300">

                    <div class="border-b border-zinc-300 bg-zinc-100 px-4 py-2">
                        <h2 class="text-sm font-bold uppercase text-zinc-800">
                            Requester Information
                        </h2>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">

                        {{-- Name --}}
                        <div>
                            <label
                                for="requester_name"
                                class="mb-1.5 block text-sm font-medium text-zinc-700"
                            >
                                Requester's Name
                            </label>

                            <input
                                type="text"
                                id="requester_name"
                                name="requester_name"
                                value="{{ auth()->user()->name }}"
                                class="w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                                required
                            >
                        </div>


                        {{-- ID Number --}}
                        <div>
                            <label
                                for="requester_id"
                                class="mb-1.5 block text-sm font-medium text-zinc-700"
                            >
                                ID Number
                            </label>

                            <input
                                type="text"
                                id="requester_id"
                                name="requester_id"
                                value="{{ auth()->user()->id_number }}"
                                class="w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                            >
                        </div>


                        {{-- Office / Department --}}
                        <div>
                            <label
                                for="office"
                                class="mb-1.5 block text-sm font-medium text-zinc-700"
                            >
                                Office / Department
                            </label>

                            <input
                                type="text"
                                id="office"
                                name="office"
                                class="w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                            >
                        </div>


                        {{-- Contact --}}
                        <div>
                            <label
                                for="contact"
                                class="mb-1.5 block text-sm font-medium text-zinc-700"
                            >
                                Contact Information
                            </label>

                            <input
                                type="text"
                                id="contact"
                                name="contact"
                                class="w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                            >
                        </div>

                    </div>

                </div>


                {{-- ========================================== --}}
                {{-- STUDENT INFORMATION --}}
                {{-- ========================================== --}}

                <div class="mb-6 overflow-hidden rounded-lg border border-zinc-300">

                    <div class="border-b border-zinc-300 bg-zinc-100 px-4 py-2">
                        <h2 class="text-sm font-bold uppercase text-zinc-800">
                            Student Information
                        </h2>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">

                        {{-- Student Name --}}
                        <div class="md:col-span-2">

                            <label
                                for="student_name"
                                class="mb-1.5 block text-sm font-medium text-zinc-700"
                            >
                                Student Name
                            </label>

                            <input
                                type="text"
                                id="student_name"
                                name="student_name"
                                placeholder="Enter student's full name"
                                class="w-full rounded-md border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                                required
                            >

                        </div>


                        {{-- Student ID --}}
                        <div>

                            <label
                                for="student_id"
                                class="mb-1.5 block text-sm font-medium text-zinc-700"
                            >
                                Student ID Number
                            </label>

                            <input
                                type="text"
                                id="student_id"
                                name="student_id"
                                placeholder="Enter student ID"
                                class="w-full rounded-md border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                                required
                            >

                        </div>


                        {{-- Program / Course --}}
                        <div>

                            <label
                                for="program"
                                class="mb-1.5 block text-sm font-medium text-zinc-700"
                            >
                                Program / Course
                            </label>

                            <input
                                type="text"
                                id="program"
                                name="program"
                                placeholder="Enter program or course"
                                class="w-full rounded-md border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                            >

                        </div>


                        {{-- Year Level --}}
                        <div>

                            <label
                                for="year_level"
                                class="mb-1.5 block text-sm font-medium text-zinc-700"
                            >
                                Year Level
                            </label>

                            <select
                                id="year_level"
                                name="year_level"
                                class="w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                            >
                                <option value="">Select year level</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>

                        </div>


                        {{-- Academic Year --}}
                        <div>

                            <label
                                for="academic_year"
                                class="mb-1.5 block text-sm font-medium text-zinc-700"
                            >
                                Academic Year
                            </label>

                            <input
                                type="text"
                                id="academic_year"
                                name="academic_year"
                                placeholder="e.g. 2026-2027"
                                class="w-full rounded-md border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                            >

                        </div>

                    </div>

                </div>


                {{-- ========================================== --}}
                {{-- INFORMATION REQUEST --}}
                {{-- ========================================== --}}

                <div class="mb-6 overflow-hidden rounded-lg border border-zinc-300">

                    <div class="border-b border-zinc-300 bg-zinc-100 px-4 py-2">
                        <h2 class="text-sm font-bold uppercase text-zinc-800">
                            Information Requested
                        </h2>
                    </div>

                    <div class="p-5">

                        <label
                            for="information_requested"
                            class="mb-1.5 block text-sm font-medium text-zinc-700"
                        >
                            Specify the information being requested
                        </label>

                        <textarea
                            id="information_requested"
                            name="information_requested"
                            rows="4"
                            placeholder="Enter the student information you are requesting..."
                            class="w-full rounded-md border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                            required
                        ></textarea>

                    </div>

                </div>


                {{-- ========================================== --}}
                {{-- PURPOSE --}}
                {{-- ========================================== --}}

                <div class="mb-6 overflow-hidden rounded-lg border border-zinc-300">

                    <div class="border-b border-zinc-300 bg-zinc-100 px-4 py-2">
                        <h2 class="text-sm font-bold uppercase text-zinc-800">
                            Purpose of Request
                        </h2>
                    </div>

                    <div class="p-5">

                        <textarea
                            id="purpose"
                            name="purpose"
                            rows="4"
                            placeholder="State the purpose of requesting the student's information..."
                            class="w-full rounded-md border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                            required
                        ></textarea>

                    </div>

                </div>


                {{-- ========================================== --}}
                {{-- DATE --}}
                {{-- ========================================== --}}

                <div class="mb-8 grid gap-5 md:grid-cols-2">

                    <div>

                        <label
                            for="request_date"
                            class="mb-1.5 block text-sm font-medium text-zinc-700"
                        >
                            Date of Request
                        </label>

                        <input
                            type="date"
                            id="request_date"
                            name="request_date"
                            value="{{ date('Y-m-d') }}"
                            class="w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#176b3a] focus:ring-[#176b3a]"
                            required
                        >

                    </div>

                </div>


                {{-- ========================================== --}}
                {{-- ACTION BUTTONS --}}
                {{-- ========================================== --}}

                <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-6 sm:flex-row sm:items-center sm:justify-between">

                    <a
                        href="{{ route('guidance.forms') }}"
                        class="inline-flex items-center justify-center rounded-md bg-red-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600"
                    >
                        Cancel
                    </a>

                    <div class="flex flex-col gap-3 sm:flex-row">

                        <button
                            type="reset"
                            class="inline-flex items-center justify-center rounded-md border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                        >
                            Reset
                        </button>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-[#176b3a] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0f5132]"
                        >
                            Submit Request
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</x-layouts::app>
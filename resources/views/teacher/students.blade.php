<x-layouts::app :title="__('Students')">
    <main class="min-h-screen bg-[#f6f5f1] p-5 text-gray-900 dark:bg-[#111712] dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header>
                <h1 class="text-2xl font-bold">Students</h1>
                <p class="mt-1 text-base text-gray-500 dark:text-zinc-400">Browse registered student accounts arranged by course.</p>
            </header>

            <section class="space-y-5 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                <label class="block">
                    <span class="mb-2 block text-base font-semibold">Search students</span>
                    <input id="student_search" type="search" autocomplete="off" placeholder="Search by student name, ID, or course" class="w-full rounded-lg border-zinc-300 bg-white px-4 py-3 text-base shadow-sm focus:border-green-700 focus:ring-green-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>
                <p class="text-sm text-gray-500 dark:text-zinc-400"><span id="student_count">{{ $students->count() }}</span> registered {{ str()->plural('student', $students->count()) }}</p>

                <div id="course_groups" class="space-y-3">
                    @forelse ($students->groupBy(fn ($student) => $studentReferences->get($student->id_number)?->course ?: 'Course not listed')->sortKeys() as $course => $courseStudents)
                        <details class="course-group group overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700" data-course-group>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 bg-zinc-50 px-5 py-4 hover:bg-emerald-50 dark:bg-zinc-800 dark:hover:bg-zinc-700">
                                <span><span class="block text-base font-semibold">{{ $course }}</span><span class="text-sm text-zinc-500 dark:text-zinc-400"><span data-group-count>{{ $courseStudents->count() }}</span> {{ str()->plural('student', $courseStudents->count()) }}</span></span>
                                <span class="text-zinc-500 transition group-open:rotate-180" aria-hidden="true">⌄</span>
                            </summary>
                            <div class="space-y-5 border-t border-zinc-200 p-4 dark:border-zinc-700">
                                @foreach ($courseStudents->groupBy(fn ($student) => $studentReferences->get($student->id_number)?->year ?: 'Year level not listed')->sortKeys() as $yearLevel => $yearStudents)
                                    <section class="year-level-group" data-year-group>
                                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">{{ $yearLevel }}</h3>
                                        <div class="grid gap-3 md:grid-cols-2">
                                            @foreach ($yearStudents as $student)
                                                @php($reference = $studentReferences->get($student->id_number))
                                                <article class="student-card rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" data-search="{{ strtolower($student->name.' '.$student->id_number.' '.$student->email.' '.$course.' '.$reference?->year) }}">
                                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                                        <h2 class="text-xl font-bold">{{ $student->name }}</h2>
                                                        <a href="{{ route('slips.fillup', ['slip' => 'student-referral-slip', 'student_id' => $student->id]) }}" class="inline-flex items-center rounded-lg bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:bg-emerald-600 dark:hover:bg-emerald-500">Assign referral</a>
                                                    </div>
                                                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                                                        <div class="flex flex-wrap gap-x-1"><dt class="font-semibold">Student ID:</dt><dd>{{ $student->id_number ?: 'Not provided' }}</dd></div>
                                                        <div class="flex flex-wrap gap-x-1"><dt class="font-semibold">Year Lvl:</dt><dd>{{ $reference?->year ?: 'Not listed' }}</dd></div>
                                                        <div class="flex flex-wrap gap-x-1"><dt class="font-semibold">Gender:</dt><dd>{{ $student->gender ? ucfirst($student->gender) : 'Not provided' }}</dd></div>
                                                        <div class="flex flex-wrap gap-x-1"><dt class="font-semibold">Email:</dt><dd class="break-all">{{ $student->email }}</dd></div>
                                                    </dl>
                                                </article>
                                            @endforeach
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                        </details>
                    @empty
                        <p class="rounded-lg bg-zinc-50 p-5 text-base text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">No student accounts are registered yet.</p>
                    @endforelse
                </div>
                <p id="no_student_matches" class="hidden rounded-lg bg-zinc-50 p-5 text-center text-base text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">No students match your search.</p>
            </section>
        </div>
    </main>
    <script>
        const searchInput = document.getElementById('student_search');
        const groups = Array.from(document.querySelectorAll('[data-course-group]'));
        const resultCount = document.getElementById('student_count');
        const noMatches = document.getElementById('no_student_matches');

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLocaleLowerCase();
            let totalVisible = 0;

            groups.forEach((group) => {
                const cards = Array.from(group.querySelectorAll('.student-card'));
                let visibleInGroup = 0;
                cards.forEach((card) => {
                    const matches = card.dataset.search.includes(query);
                    card.hidden = !matches;
                    if (matches) visibleInGroup += 1;
                });
                group.hidden = visibleInGroup === 0;
                group.querySelector('[data-group-count]').textContent = visibleInGroup;
                group.querySelectorAll('[data-year-group]').forEach((yearGroup) => {
                    yearGroup.hidden = !yearGroup.querySelector('.student-card:not([hidden])');
                });
                if (query && visibleInGroup > 0) group.open = true;
                totalVisible += visibleInGroup;
            });

            resultCount.textContent = totalVisible;
            noMatches.classList.toggle('hidden', totalVisible > 0);
        });
    </script>
</x-layouts::app>

<x-layouts::app :title="__('Files of Graduated/Deactivated Users')">
    <main class="min-h-screen bg-[#f6f5f1] p-5 text-slate-900 dark:bg-[#111712] dark:text-zinc-100 sm:p-6 lg:p-8">
        <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
            <header>
                <a href="{{ route('guidance.dashboard') }}" class="text-sm font-medium text-[#28643a] hover:underline dark:text-[#91dfa6]">← Back to dashboard</a>
                <h1 class="mt-3 text-3xl font-bold">Files of Graduated/Deactivated Users</h1>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">These accounts are not accessible to students, but their profiles and guidance files are retained for reference.</p>
            </header>

            <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                @forelse ($students as $student)
                    <a href="{{ route('guidance.records.show', $student) }}" class="flex items-center justify-between gap-4 border-b border-neutral-100 px-6 py-5 last:border-b-0 hover:bg-amber-50 dark:border-neutral-800 dark:hover:bg-amber-950/20">
                        <div>
                            <p class="font-semibold">{{ $student->name }}</p>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ $student->id_number ?: $student->email }}</p>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2 text-xs">
                            @if ($student->is_graduated)<span class="rounded-full bg-blue-100 px-2.5 py-1 font-semibold text-blue-800">Graduated</span>@endif
                            @if (! $student->is_active)<span class="rounded-full bg-gray-200 px-2.5 py-1 font-semibold text-gray-700">Deactivated</span>@endif
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 font-semibold text-amber-800">Open files →</span>
                        </div>
                    </a>
                @empty
                    <p class="px-6 py-12 text-center text-sm text-neutral-500">No graduated or deactivated student files.</p>
                @endforelse
            </section>
        </div>
    </main>
</x-layouts::app>

<x-layouts::app :title="__('Guidance Slips')">
    <main class="min-h-screen bg-zinc-50 px-4 py-8 text-zinc-900 dark:bg-[#111712] dark:text-zinc-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <div class="mb-6">
                <flux:heading size="xl" class="text-zinc-900 dark:text-zinc-100">Guidance and Counseling Slips</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-300">
                    @if (auth()->user()->role === 'teacher')
                        Refer a student by assigning the Student Referral Slip.
                    @else
                        Issue a Client Information Form to first-time students or a Class Admission Slip after receiving a referral.
                    @endif
                </flux:text>
            </div>

            @if (session('status'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900 dark:border-green-900 dark:bg-green-950/50 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($slips as $slug => $slip)
                    <article class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
                        <flux:heading size="md" class="text-zinc-900 dark:text-zinc-100">{{ $slip['title'] }}</flux:heading>
                        <flux:text class="mt-2 text-zinc-600 dark:text-zinc-300">
                            @if ($slug === 'student-referral-slip')
                                Assign this slip to a student so they can complete it and submit it to Guidance.
                            @elseif ($slug === 'qf-bo-oss-04-client-information-form')
                                Issue only when the referred student is visiting the Guidance Office for the first time.
                            @else
                                Issue this slip to a student after receiving their referral.
                            @endif
                        </flux:text>
                        <div class="mt-5">
                            <flux:modal.trigger name="{{ $slug }}-modal">
                                <flux:button variant="primary">Preview slip</flux:button>
                            </flux:modal.trigger>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        @foreach ($slips as $slug => $slip)
            @include('forms.'.$slug)
        @endforeach

        @if ($previewModal)
            <flux:modal.trigger
                :name="$previewModal"
                x-init="$nextTick(() => $dispatch('modal-show', { name: '{{ $previewModal }}' }))"
            >
                <span class="hidden"></span>
            </flux:modal.trigger>
        @endif
    </main>
</x-layouts::app>

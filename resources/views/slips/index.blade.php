<x-layouts::app :title="__('Guidance Slips')">
    <main class="min-h-screen bg-zinc-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <div class="mb-6">
                <flux:heading size="xl">Guidance and Counseling Slips</flux:heading>
                <flux:text class="mt-2 text-zinc-600">
                    @if (auth()->user()->role === 'teacher')
                        Refer a student by assigning the Student Referral Slip.
                    @else
                        Issue a Client Information Form to first-time students or a Class Admission Slip after receiving a referral.
                    @endif
                </flux:text>
            </div>

            @if (session('status'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($slips as $slug => $slip)
                    <article class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-zinc-200">
                        <flux:heading size="md">{{ $slip['title'] }}</flux:heading>
                        <flux:text class="mt-2">
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

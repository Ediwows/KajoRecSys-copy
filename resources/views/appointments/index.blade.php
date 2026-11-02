<x-layouts::app :title="__('Appointments')">
    <main class="min-h-screen bg-[#f6f5f1] p-4 text-gray-900 dark:bg-[#111712] dark:text-zinc-100 sm:p-6 lg:p-10">
        <div class="mx-auto max-w-6xl space-y-8">
            <header class="flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-zinc-800 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-700 dark:text-green-400">Guidance Office</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Appointments</h1>
                    <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-zinc-400 sm:text-base">
                    @if (auth()->user()->role === 'guidance_counselor')
                        Review appointment requests sent by students and teachers.
                    @else
                        Request a meeting with the Guidance Office and track its status.
                    @endif
                </p>
            </header>

            @if (session('status'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900 shadow-sm dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            @if (auth()->user()->role !== 'guidance_counselor')
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                    <div class="flex items-start gap-4">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-green-100 text-green-700 dark:bg-green-950/50 dark:text-green-300">+</div>
                        <div>
                            <h2 class="text-xl font-bold">Request an appointment</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                        Tell us what kind of appointment you need. The Guidance Office will review your request and set the date and time.
                    </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('appointments.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="purpose" class="mb-2 block text-sm font-semibold">How can we help?</label>
                        </div>
                        <div>
                            <textarea
                                id="purpose"
                                name="purpose"
                                rows="4"
                                maxlength="2000"
                                required
                                class="w-full rounded-xl border-gray-300 bg-gray-50 p-4 text-sm leading-6 shadow-sm focus:border-green-600 focus:ring-green-600 dark:border-zinc-700 dark:bg-zinc-800"
                                placeholder="Example: academic counseling, personal concern, or career guidance."
                            >{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-xl bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 dark:bg-green-600 dark:hover:bg-green-500">
                            Send appointment request
                        </button>
                    </form>
                </section>
            @endif

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400">Requests</p>
                        <h2 class="mt-1 text-xl font-bold">
                    {{ auth()->user()->role === 'guidance_counselor' ? 'Received appointment requests' : 'My appointment requests' }}
                        </h2>
                    </div>
                    @if ($appointments->isNotEmpty())
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $appointments->count() }} {{ Str::plural('request', $appointments->count()) }}</span>
                    @endif
                </div>

                @if ($appointments->isEmpty())
                    <p class="mt-4 rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ auth()->user()->role === 'guidance_counselor' ? 'There are no appointment requests yet.' : 'You have not requested an appointment yet.' }}
                    </p>
                @else
                    <ul class="mt-6 space-y-4">
                        @foreach ($appointments as $appointment)
                            <li class="rounded-xl border border-gray-200 p-5 transition hover:border-green-300 hover:shadow-sm dark:border-zinc-800 dark:hover:border-green-800 sm:p-6">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        @if (!(auth()->user()->role === 'guidance_counselor' && in_array($appointment->status, ['pending', 'reschedule_requested'], true)))
                                        @if (auth()->user()->role === 'guidance_counselor')
                                            <p class="text-base font-bold">{{ $appointment->requester->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-zinc-400">
                                                {{ ucfirst($appointment->requester->role) }} · {{ $appointment->requester->id_number }}
                                            </p>
                                        @endif
                                        @if ($appointment->scheduled_at)
                                            <p class="mt-3 inline-flex rounded-lg bg-green-50 px-3 py-1.5 text-sm font-semibold text-green-800 dark:bg-green-950/40 dark:text-green-300">
                                                Scheduled for {{ $appointment->scheduled_at->format('M j, Y g:i A') }}
                                                @if ($appointment->duration_minutes)
                                                    – {{ $appointment->scheduled_at->copy()->addMinutes($appointment->duration_minutes)->format('g:i A') }}
                                                @endif
                                            </p>
                                        @else
                                            <p class="mt-2 text-sm text-gray-500 dark:text-zinc-400">Date and time will be set by the Guidance Office.</p>
                                        @endif
                                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-zinc-300">{{ $appointment->purpose }}</p>
                                        @if ($appointment->guidance_message && auth()->user()->role !== 'guidance_counselor')
                                            <div class="mt-3 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-900 dark:border-green-900 dark:bg-green-950/40 dark:text-green-200">
                                                <span class="font-semibold">Message from Guidance:</span>
                                                <span class="whitespace-pre-line">{{ $appointment->guidance_message }}</span>
                                            </div>
                                        @endif
                                        @if ($appointment->status === 'declined')
                                            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-900 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200">
                                                <span class="font-semibold">Reason declined:</span>
                                                {{ $appointment->decline_reason }}
                                            </div>
                                        @endif
                                        @if ($appointment->status === 'reschedule_requested')
                                            <div class="mt-3 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-200">
                                                <span class="font-semibold">Reason for re-appointment:</span>
                                                {{ $appointment->reschedule_reason }}
                                            </div>
                                        @endif
                                        @if (in_array($appointment->status, ['done', 'missed'], true))
                                            <div class="mt-3 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-900 dark:border-green-900 dark:bg-green-950/40 dark:text-green-200">
                                                <p class="font-semibold">{{ $appointment->status === 'missed' ? 'Appointment outcome' : 'Action taken' }}</p>
                                                <p class="mt-1 whitespace-pre-line">{{ $appointment->action_taken }}</p>
                                                @if ($appointment->reviewer)
                                                    <p class="mt-3 text-xs">Attending counselor: <span class="font-semibold">{{ $appointment->reviewer->name }}</span></p>
                                                @endif
                                                @if ($appointment->attachments)
                                                    <div class="mt-3 space-y-1">
                                                        <p class="font-semibold">Session attachments</p>
                                                        @foreach ($appointment->attachments as $attachment)
                                                            <a href="{{ route('appointments.attachments.download', [$appointment, $loop->index]) }}" target="_blank" rel="noopener" class="block underline">{{ $attachment['name'] }}</a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        @endif
                                    </div>
                                    <span @class([
                                        'rounded-full px-3 py-1.5 text-xs font-bold capitalize',
                                        'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300' => in_array($appointment->status, ['pending', 'reschedule_requested'], true),
                                        'bg-green-100 text-green-800 dark:bg-green-950/50 dark:text-green-300' => $appointment->status === 'accepted',
                                        'bg-green-100 text-green-800 dark:bg-green-950/50 dark:text-green-300' => $appointment->status === 'done',
                                        'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-300' => $appointment->status === 'missed',
                                        'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-300' => $appointment->status === 'declined',
                                    ])>
                                        {{ $appointment->status === 'reschedule_requested' ? 'Re-appointment requested' : ($appointment->status === 'missed' ? 'Client failed to attend' : $appointment->status) }}
                                    </span>
                                </div>

                                @if (auth()->user()->role === 'student' && in_array($appointment->status, ['accepted', 'declined'], true))
                                    <form method="POST" action="{{ route('appointments.reappoint', $appointment) }}" class="mt-5 rounded-xl border border-blue-200 bg-blue-50/70 p-4 dark:border-blue-900 dark:bg-blue-950/20">
                                        @csrf
                                        <label for="reschedule_reason_{{ $appointment->id }}" class="block text-sm font-semibold text-blue-950 dark:text-blue-200">Need a different appointment time?</label>
                                        <textarea id="reschedule_reason_{{ $appointment->id }}" name="reschedule_reason" rows="2" maxlength="2000" required class="mt-2 w-full rounded-lg border-blue-200 bg-white p-3 text-sm leading-6 dark:border-blue-800 dark:bg-zinc-800" placeholder="Tell the counselor why you need to re-appoint.">{{ old('reschedule_reason') }}</textarea>
                                        @error('reschedule_reason')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        <button type="submit" class="mt-3 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Re-appoint</button>
                                    </form>
                                @endif

                                @if (auth()->user()->role === 'guidance_counselor' && in_array($appointment->status, ['pending', 'reschedule_requested'], true))
                                    @php
                                        $appointmentTimeSlots = collect(range(0, 1439, 10))->map(function (int $minutes) {
                                            return [
                                                'value' => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60),
                                                'label' => date('g:i A', mktime(intdiv($minutes, 60), $minutes % 60)),
                                            ];
                                        });
                                    @endphp
                                    <div class="mt-6 grid grid-cols-1 gap-6 border-t border-gray-100 pt-5 dark:border-zinc-800 lg:grid-cols-3">
                                        <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900 lg:col-span-2">
                                            <div class="border-b border-neutral-200 px-6 py-5 dark:border-neutral-700">
                                            <p class="text-xs font-bold uppercase tracking-wide text-green-700 dark:text-green-400">Appointment management</p>
                                            <h3 class="mt-3 text-2xl font-bold tracking-tight">{{ $appointment->requester->name }}</h3>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">{{ ucfirst($appointment->requester->role) }} · {{ $appointment->requester->id_number }}</p>
                                            </div>
                                            <div class="space-y-5 p-6">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Appointment details</p>
                                                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-neutral-700 dark:text-neutral-200">{{ $appointment->purpose }}</p>
                                            </div>
                                            <form id="accept-appointment-{{ $appointment->id }}" method="POST" action="{{ route('appointments.accept', $appointment) }}" class="space-y-4">
                                            @csrf
                                                <label for="guidance_message_{{ $appointment->id }}" class="block text-sm font-semibold">Notes <span class="font-normal text-neutral-500">(optional)</span></label>
                                                <textarea
                                                    id="guidance_message_{{ $appointment->id }}"
                                                    name="guidance_message"
                                                    rows="4"
                                                    maxlength="2000"
                                                    class="mt-2 w-full rounded-xl border border-neutral-300 bg-neutral-50 p-3 text-sm leading-6 shadow-sm focus:border-green-600 focus:ring-green-600 dark:border-neutral-700 dark:bg-neutral-800"
                                                    placeholder="Add instructions or a message for the requester."
                                                >{{ old('guidance_message') }}</textarea>
                                                @error('guidance_message')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                                <div class="flex flex-wrap gap-3 border-t border-neutral-200 pt-5 dark:border-neutral-700">
                                                    <button type="submit" class="rounded-xl bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 dark:bg-green-600 dark:hover:bg-green-500">Accept appointment</button>
                                                    <button type="button" onclick="document.getElementById('decline-modal-{{ $appointment->id }}').showModal()" class="rounded-xl border border-red-300 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950/30">Decline</button>
                                                </div>
                                            </form>
                                        </div>
                                        </section>

                                        <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                                            <div class="border-b border-neutral-200 px-6 py-5 dark:border-neutral-700">
                                            <p class="text-xs font-bold uppercase tracking-wide text-blue-700 dark:text-blue-400">Schedule</p>
                                            <h3 class="mt-1 text-lg font-semibold">Choose date and time</h3>
                                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Set the appointment schedule.</p>
                                            </div>
                                            <div class="space-y-5 p-6">
                                                <div>
                                                    <label for="scheduled_date_{{ $appointment->id }}" class="mb-2 block text-sm font-semibold">Appointment date</label>
                                                    <input id="scheduled_date_{{ $appointment->id }}" name="scheduled_date" form="accept-appointment-{{ $appointment->id }}" type="date" min="{{ now()->format('Y-m-d') }}" value="{{ old('scheduled_date') }}" required class="block w-full rounded-xl border border-neutral-300 bg-neutral-50 px-3 py-3 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100" />
                                                    @error('scheduled_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                    <p class="mt-3 text-xs leading-5 text-blue-800/70 dark:text-blue-200/70">Choose the appointment date.</p>
                                                </div>
                                                <div>
                                                    <label for="scheduled_time_{{ $appointment->id }}" class="mb-2 block text-sm font-semibold">Appointment time</label>
                                                    <select id="scheduled_time_{{ $appointment->id }}" name="scheduled_time" form="accept-appointment-{{ $appointment->id }}" required class="block max-h-64 w-full rounded-xl border border-neutral-300 bg-neutral-50 px-3 py-3 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100">
                                                        <option value="" disabled @selected(! old('scheduled_time'))>Select a time</option>
                                                        @foreach ($appointmentTimeSlots as $timeSlot)
                                                            <option value="{{ $timeSlot['value'] }}" @selected(old('scheduled_time') === $timeSlot['value'])>{{ $timeSlot['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('scheduled_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                </div>
                                                <div>
                                                    <label for="duration_minutes_{{ $appointment->id }}" class="mb-2 block text-sm font-semibold">Duration</label>
                                                    <select id="duration_minutes_{{ $appointment->id }}" name="duration_minutes" form="accept-appointment-{{ $appointment->id }}" required class="block w-full rounded-xl border border-neutral-300 bg-neutral-50 px-3 py-3 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100">
                                                        <option value="" disabled @selected(! old('duration_minutes'))>Select duration</option>
                                                        @foreach ([30 => '30 minutes', 60 => '1 hour', 90 => '1 hour 30 minutes', 120 => '2 hours'] as $minutes => $label)
                                                            <option value="{{ $minutes }}" @selected((string) old('duration_minutes') === (string) $minutes)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('duration_minutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                </div>
                                            </div>
                                            <div class="rounded-xl border border-amber-200 p-3 dark:border-blue-900 dark:bg-blue-950/30">
                                                <p class="text-xs leading-5 text-blue-800 dark:text-blue-200"><span class="font-semibold">Reminder:</span> Select the date and time before accepting the appointment. Time slots are available every 10 minutes.</p>
                                            </div>
                                            </div>
                                        </section>
                                    </div>

                                    <dialog id="decline-modal-{{ $appointment->id }}" class="w-[min(32rem,calc(100%-2rem))] overscroll-contain rounded-2xl border border-red-200 bg-white p-0 shadow-2xl backdrop:bg-black/50 dark:border-red-900 dark:bg-zinc-900">
                                        <form method="POST" action="{{ route('appointments.decline', $appointment) }}" class="p-6">
                                            @csrf
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-red-700 dark:text-red-300">Decline appointment</p>
                                                    <h3 class="mt-2 text-lg font-bold">Reason for declining</h3>
                                                </div>
                                                <button type="button" onclick="document.getElementById('decline-modal-{{ $appointment->id }}').close()" class="text-2xl leading-none text-gray-400 hover:text-gray-700 dark:hover:text-zinc-100" aria-label="Close">&times;</button>
                                            </div>
                                            <textarea id="decline_reason_{{ $appointment->id }}" name="decline_reason" rows="5" maxlength="2000" required class="mt-5 w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm leading-6 dark:border-zinc-700 dark:bg-zinc-800" placeholder="Explain why this appointment cannot be accepted.">{{ old('decline_reason') }}</textarea>
                                            @error('decline_reason')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                            <div class="mt-5 flex justify-end gap-3">
                                                <button type="button" onclick="document.getElementById('decline-modal-{{ $appointment->id }}').close()" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold hover:bg-gray-50 dark:border-zinc-700 dark:hover:bg-zinc-800">Cancel</button>
                                                <button type="submit" class="rounded-lg bg-red-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-800">Decline appointment</button>
                                            </div>
                                        </form>
                                    </dialog>
                                @endif

                                @if (auth()->user()->role === 'guidance_counselor' && $appointment->status === 'accepted')
                                    <form method="POST" action="{{ route('appointments.missed', $appointment) }}" class="mt-6 rounded-xl border border-red-200 bg-red-50/70 p-5 dark:border-red-900 dark:bg-red-950/20">
                                        @csrf
                                        <p class="text-sm text-red-900 dark:text-red-200">Use this when the scheduled appointment has passed and the client did not attend.</p>
                                        <button type="submit" class="mt-4 rounded-xl border border-red-300 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950/40">Client failed to attend</button>
                                    </form>
                                    <form method="POST" action="{{ route('appointments.complete', $appointment) }}" enctype="multipart/form-data" class="mt-6 rounded-xl border border-green-200 bg-green-50/70 p-5 dark:border-green-900 dark:bg-green-950/20">
                                        @csrf
                                        <p class="text-xs font-bold uppercase tracking-wide text-green-700 dark:text-green-400">Session record</p>
                                        <label for="concerns_{{ $appointment->id }}" class="mt-2 block text-sm font-semibold">Concerns</label>
                                        <textarea id="concerns_{{ $appointment->id }}" name="concerns" rows="4" maxlength="10000" required class="mt-2 w-full rounded-xl border border-green-200 bg-white p-3 text-sm leading-6 dark:border-green-900 dark:bg-zinc-800" placeholder="Record the student's concern discussed during the appointment.">{{ old('concerns', $appointment->concerns ?? $appointment->purpose) }}</textarea>
                                        @error('concerns') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        <label for="action_taken_{{ $appointment->id }}" class="mt-4 block text-sm font-semibold">Interventions / Actions Taken</label>
                                        <textarea id="action_taken_{{ $appointment->id }}" name="action_taken" rows="5" maxlength="10000" required class="mt-2 w-full rounded-xl border border-green-200 bg-white p-3 text-sm leading-6 dark:border-green-900 dark:bg-zinc-800" placeholder="Write down every action taken during this session.">{{ old('action_taken') }}</textarea>
                                        @error('action_taken') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        <label for="attachments_{{ $appointment->id }}" class="mt-4 block text-sm font-semibold">Attach images or documents <span class="font-normal text-neutral-500">(optional, up to 10 files; 10 MB each)</span></label>
                                        <input id="attachments_{{ $appointment->id }}" name="attachments[]" type="file" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip" class="mt-2 block w-full rounded-lg border border-green-200 bg-white p-2 text-sm dark:border-green-900 dark:bg-zinc-800">
                                        @error('attachments') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        @error('attachments.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        <button type="submit" class="mt-4 rounded-xl bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 dark:bg-green-600 dark:hover:bg-green-500">Mark appointment as done</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </main>
</x-layouts::app>

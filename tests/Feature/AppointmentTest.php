<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_and_teachers_can_request_appointments_and_only_see_their_own(): void
    {
        $student = User::factory()->create([
            'id_number' => 'APPT-STUDENT-1',
            'gender' => 'female',
            'role' => 'student',
        ]);
        $teacher = User::factory()->create([
            'id_number' => 'APPT-TEACHER-1',
            'gender' => 'male',
            'role' => 'teacher',
        ]);
        $appointmentDate = now()->addDays(2)->startOfHour();

        $this->actingAs($student)
            ->get(route('appointments.index'))
            ->assertOk()
            ->assertSee('Request an appointment');

        $this->actingAs($student)
            ->post(route('appointments.store'), [
                'scheduled_at' => $appointmentDate->format('Y-m-d\TH:i'),
                'purpose' => 'Discuss academic support',
            ])
            ->assertRedirect(route('appointments.index'))
            ->assertSessionHas('status');

        $appointment = Appointment::query()->firstOrFail();
        $this->assertSame($student->id, $appointment->user_id);
        $this->assertSame('pending', $appointment->status);

        $this->actingAs($teacher)
            ->post(route('appointments.store'), [
                'scheduled_at' => now()->addDays(3)->startOfHour()->format('Y-m-d\TH:i'),
                'purpose' => 'Discuss a student referral',
            ])
            ->assertRedirect(route('appointments.index'));

        $this->actingAs($student)
            ->get(route('appointments.index'))
            ->assertSee('Discuss academic support')
            ->assertDontSee('Discuss a student referral');
    }

    public function test_guidance_can_accept_or_decline_appointment_requests_and_declines_require_a_reason(): void
    {
        $student = User::factory()->create([
            'id_number' => 'APPT-STUDENT-2',
            'gender' => 'female',
            'role' => 'student',
        ]);
        $counselor = User::factory()->create([
            'id_number' => 'APPT-COUNSELOR-1',
            'gender' => 'male',
            'role' => 'guidance_counselor',
        ]);
        $acceptedAppointment = Appointment::create([
            'user_id' => $student->id,
            'scheduled_at' => now()->addDays(2),
            'purpose' => 'Accepted meeting',
        ]);
        $declinedAppointment = Appointment::create([
            'user_id' => $student->id,
            'scheduled_at' => now()->addDays(4),
            'purpose' => 'Declined meeting',
        ]);

        $this->actingAs($counselor)
            ->get(route('appointments.index'))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Reason for declining');
        $this->actingAs($counselor)
            ->get(route('guidance.dashboard'))
            ->assertOk()
            ->assertSee('Appointment requests (2)');

        $this->actingAs($counselor)
            ->post(route('appointments.accept', $acceptedAppointment))
            ->assertRedirect(route('appointments.index'));
        $this->assertSame('accepted', $acceptedAppointment->fresh()->status);
        $this->assertSame($counselor->id, $acceptedAppointment->fresh()->reviewed_by);

        $this->actingAs($counselor)
            ->post(route('appointments.decline', $declinedAppointment), [])
            ->assertSessionHasErrors('decline_reason');
        $this->assertSame('pending', $declinedAppointment->fresh()->status);

        $this->actingAs($counselor)
            ->post(route('appointments.decline', $declinedAppointment), [
                'decline_reason' => 'That time is unavailable. Please choose another time.',
            ])
            ->assertRedirect(route('appointments.index'));
        $this->assertSame('declined', $declinedAppointment->fresh()->status);
        $this->assertSame($counselor->id, $declinedAppointment->fresh()->reviewed_by);

        $this->actingAs($student)
            ->get(route('appointments.index'))
            ->assertSee('That time is unavailable. Please choose another time.')
            ->assertDontSee('Reason for declining');
    }

    public function test_only_students_and_teachers_can_request_and_only_guidance_can_review(): void
    {
        $counselor = User::factory()->create([
            'id_number' => 'APPT-COUNSELOR-2',
            'gender' => 'female',
            'role' => 'guidance_counselor',
        ]);
        $appointment = Appointment::create([
            'user_id' => $counselor->id,
            'scheduled_at' => now()->addDays(2),
            'purpose' => 'Test request',
        ]);
        $student = User::factory()->create([
            'id_number' => 'APPT-STUDENT-3',
            'gender' => 'male',
            'role' => 'student',
        ]);

        $this->actingAs($counselor)
            ->post(route('appointments.store'), [
                'scheduled_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'purpose' => 'Invalid requester',
            ])
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('appointments.accept', $appointment))
            ->assertForbidden();
        $this->actingAs($student)
            ->post(route('appointments.decline', $appointment), ['decline_reason' => 'Not allowed'])
            ->assertForbidden();
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_appointment_requests_must_be_scheduled_for_a_future_time(): void
    {
        $student = User::factory()->create([
            'id_number' => 'APPT-STUDENT-4',
            'gender' => 'female',
            'role' => 'student',
        ]);

        $this->actingAs($student)
            ->post(route('appointments.store'), [
                'scheduled_at' => now()->subMinute()->format('Y-m-d\TH:i'),
                'purpose' => 'Past meeting',
            ])
            ->assertSessionHasErrors('scheduled_at');

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_guidance_can_record_that_a_client_failed_to_attend(): void
    {
        $counselor = User::factory()->create([
            'id_number' => 'APPT-COUNSELOR-MISSED',
            'gender' => 'female',
            'role' => 'guidance_counselor',
        ]);
        $student = User::factory()->create([
            'id_number' => 'APPT-STUDENT-MISSED',
            'gender' => 'male',
            'role' => 'student',
        ]);
        $appointment = Appointment::create([
            'user_id' => $student->id,
            'scheduled_at' => now()->subHour(),
            'purpose' => 'Missed meeting',
            'status' => 'accepted',
        ]);

        $this->actingAs($counselor)
            ->post(route('appointments.missed', $appointment))
            ->assertRedirect(route('appointments.index'));

        $this->assertSame('missed', $appointment->fresh()->status);
        $this->assertSame('Client failed to attend', $appointment->fresh()->action_taken);
    }
}

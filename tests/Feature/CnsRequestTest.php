<?php

namespace Tests\Feature;

use App\Models\CnsRequest;
use App\Models\SlipAssignment;
use App\Models\User;
use App\Support\SlipCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CnsRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_cns_form_is_not_available_in_the_updated_guidance_flow(): void
    {
        $teacher = User::factory()->create([
            'id_number' => 'TEACHER-CNS-1001',
            'gender' => 'female',
            'role' => 'teacher',
        ]);
        $student = User::factory()->create([
            'id_number' => 'STU-CNS-1001',
            'gender' => 'female',
            'role' => 'student',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertDontSee('Certificate of No Scholarship');
        $this->actingAs($teacher)
            ->get(route('slips.index'))
            ->assertOk()
            ->assertDontSee('Certificate of No Scholarship');
        $this->actingAs($teacher)
            ->get('/cns')
            ->assertNotFound();
        $this->actingAs($teacher)
            ->get('/cns/fill-up')
            ->assertNotFound();
        $this->actingAs($teacher)
            ->post(route('slips.assign', 'cns'), ['student_id' => $student->id])
            ->assertNotFound();
        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertDontSee('Certificate of No Scholarship');

        $this->assertArrayNotHasKey('case-referral-slip', SlipCatalog::all());
        $this->assertArrayNotHasKey('qf-bo-oss-19-scholars-accomplishment-report', SlipCatalog::all());

        $this->assertDatabaseCount('slip_assignments', 0);
    }

    public function test_student_cannot_view_another_users_cns_request(): void
    {
        $owner = User::factory()->create([
            'id_number' => 'STU-CNS-1002',
            'gender' => 'female',
            'role' => 'student',
        ]);
        $otherStudent = User::factory()->create([
            'id_number' => 'STU-CNS-1003',
            'gender' => 'male',
            'role' => 'student',
        ]);
        $request = CnsRequest::create([
            'user_id' => $owner->id,
            'full_name' => $owner->name,
            'last_name' => null,
            'first_name' => null,
            'student_id' => 'STU-CNS-1002',
            'email' => $owner->email,
            'degree' => 'BSCrim',
            'year_level' => 'First',
            'school_year' => '2025-2026',
            'purpose' => 'scholarship_application',
            'pdf_path' => 'cns-requests/test.pdf',
        ]);

        $this->actingAs($otherStudent)
            ->get(route('cns.requests.show', $request))
            ->assertForbidden();
    }

    public function test_legacy_cns_assignments_cannot_be_filled_or_submitted(): void
    {
        $teacher = User::factory()->create([
            'id_number' => 'TEACHER-CNS-3001',
            'gender' => 'female',
            'role' => 'teacher',
        ]);
        $student = User::factory()->create([
            'id_number' => 'STU-CNS-3001',
            'gender' => 'male',
            'role' => 'student',
        ]);
        $assignment = SlipAssignment::create([
            'assigned_by' => $teacher->id,
            'student_id' => $student->id,
            'slip' => 'cns',
        ]);

        $this->actingAs($student)
            ->get(route('assigned-slips.fillup', $assignment))
            ->assertNotFound();
        $this->actingAs($student)
            ->post("/assigned-slips/{$assignment->id}/cns-submit", [])
            ->assertNotFound();

        $this->assertDatabaseCount('cns_requests', 0);
    }
}

<?php

namespace Tests\Feature;

use App\Models\SlipAssignment;
use App\Models\SlipSubmission;
use App\Models\User;
use App\Support\SlipCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SlipSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_slip_can_be_submitted_and_its_pdf_viewed(): void
    {
        Storage::fake('local');
        $teacher = User::factory()->create([
            'id_number' => 'SLIP-1001',
            'gender' => 'female',
            'role' => 'teacher',
        ]);
        $user = User::factory()->create([
            'id_number' => 'SLIP-STUDENT-1001',
            'gender' => 'female',
            'role' => 'student',
        ]);

        $this->actingAs($teacher)
            ->get(route('slips.index'))
            ->assertOk()
            ->assertSee('Guidance and Counseling Slips');

        $this->actingAs($user)
            ->get(route('slips.index'))
            ->assertRedirect(route('student.dashboard'));
        $this->actingAs($user)
            ->get('/cns')
            ->assertNotFound();

        foreach (SlipCatalog::all() as $slug => $slip) {
            $assignment = SlipAssignment::create([
                'assigned_by' => $teacher->id,
                'student_id' => $user->id,
                'slip' => $slug,
            ]);

            $this->actingAs($user)
                ->get(route('assigned-slips.fillup', $assignment))
                ->assertOk()
                ->assertSee($slip['title']);

            $details = [];

            foreach ($slip['groups'] as $group) {
                foreach ($group['fields'] as $field) {
                    if ($field['type'] === 'checkboxes') {
                        $details[$field['key']] = $field['required']
                            ? [array_key_first($field['options'])]
                            : [];
                    } elseif ($field['required']) {
                        $details[$field['key']] = $field['type'] === 'date'
                            ? '2026-10-07'
                            : 'Sample answer';
                    }
                }
            }

            $response = $this->actingAs($user)->post(route('assigned-slips.submit', $assignment), [
                'details' => $details,
            ]);
            $response->assertSessionHasNoErrors();

            $submission = SlipSubmission::query()
                ->where('user_id', $user->id)
                ->where('slip', $slug)
                ->latest('id')
                ->firstOrFail();

            $response->assertRedirect(route('slips.submissions.show', $submission));
            $this->assertSame('submitted', $assignment->fresh()->status);
            Storage::disk('local')->assertExists($submission->pdf_path);
            $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($submission->pdf_path));

            $this->actingAs($user)
                ->get(route('slips.submissions.show', $submission))
                ->assertOk()
                ->assertSee('Completed slip PDF')
                ->assertSee(route('student.dashboard'), false);
            $this->actingAs($user)
                ->get(route('slips.submissions.pdf', $submission))
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
            $this->actingAs($user)
                ->get(route('slip-history.index'))
                ->assertOk()
                ->assertSee($slip['title'])
                ->assertSee('Slip History');
        }
    }

    public function test_teacher_referral_sidebar_lists_forms_and_opens_the_selected_preview(): void
    {
        $teacher = User::factory()->create([
            'id_number' => 'TEACHER-1001',
            'gender' => 'female',
            'role' => 'teacher',
        ]);

        $dashboard = $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Referral')
            ->assertSee('Student Referral Slip')
            ->assertDontSee('Certificate of No Scholarship')
            ->assertDontSee('Client Information Form')
            ->assertDontSee('Class Admission Slip')
            ->assertDontSee('Student Leave of Absence Slip');

        $this->assertStringStartsWith('/guidance-slips?', route('slips.index', ['preview' => 'student-referral-slip'], false));

        $this->actingAs($teacher)
            ->get(route('slips.index', ['preview' => 'student-referral-slip']))
            ->assertOk()
            ->assertSee('modal-show')
            ->assertSee('student-referral-slip-modal')
            ->assertDontSee('qf-bo-oss-04-client-information-form-modal');

        $counselor = User::factory()->create([
            'id_number' => 'COUNSELOR-SLIPS-1',
            'gender' => 'male',
            'role' => 'guidance_counselor',
        ]);
        $this->actingAs($counselor)
            ->get(route('slips.index'))
            ->assertRedirect(route('guidance.dashboard'));
    }

    public function test_only_teachers_can_assign_forms_to_students(): void
    {
        $teacher = User::factory()->create([
            'id_number' => 'FLOW-TEACHER-1',
            'gender' => 'female',
            'role' => 'teacher',
        ]);
        $student = User::factory()->create([
            'id_number' => 'FLOW-STUDENT-1',
            'gender' => 'female',
            'role' => 'student',
        ]);
        $counselor = User::factory()->create([
            'id_number' => 'FLOW-COUNSELOR-1',
            'gender' => 'male',
            'role' => 'guidance_counselor',
        ]);

        $this->actingAs($student)
            ->post(route('slips.assign', 'student-referral-slip'), ['student_id' => $student->id])
            ->assertForbidden();
        $this->actingAs($counselor)
            ->post(route('slips.assign', 'student-referral-slip'), ['student_id' => $student->id])
            ->assertForbidden();
        $this->actingAs($teacher)
            ->post(route('slips.assign', 'class-admission-slip'), ['student_id' => $student->id])
            ->assertNotFound();

        $this->assertDatabaseCount('slip_assignments', 0);
    }

    public function test_counselor_issues_cif_only_once_and_class_admission_after_a_received_referral(): void
    {
        Storage::fake('local');
        $teacher = User::factory()->create([
            'id_number' => 'FLOW-TEACHER-2',
            'gender' => 'female',
            'role' => 'teacher',
        ]);
        $counselor = User::factory()->create([
            'id_number' => 'FLOW-COUNSELOR-2',
            'gender' => 'male',
            'role' => 'guidance_counselor',
        ]);
        $student = User::factory()->create([
            'name' => 'First Visit Student',
            'id_number' => 'FLOW-STUDENT-2',
            'gender' => 'female',
            'role' => 'student',
        ]);

        $this->actingAs($teacher)
            ->post(route('slips.assign', 'student-referral-slip'), ['student_id' => $student->id])
            ->assertRedirect(route('slips.index'));
        $referral = SlipAssignment::firstOrFail();

        $this->actingAs($student)
            ->post(route('assigned-slips.submit', $referral), [
                'details' => [
                    'student_id' => $student->id_number,
                    'student_name' => $student->name,
                    'program_year' => 'BSIT - First Year',
                    'referral_reasons' => ['academic_deficiencies'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($counselor)
            ->get(route('guidance.dashboard'))
            ->assertOk()
            ->assertSee('Received Student Referral Slips')
            ->assertSee($student->name)
            ->assertSee('Issue CIF (first-time)')
            ->assertSee('Issue Class Admission');

        $this->actingAs($counselor)
            ->post(route('guidance.slip-review.issue', [$referral, 'qf-bo-oss-04-client-information-form']))
            ->assertRedirect(route('guidance.dashboard'));
        $clientInformation = SlipAssignment::query()
            ->where('slip', 'qf-bo-oss-04-client-information-form')
            ->firstOrFail();

        $this->assertSame($referral->id, $clientInformation->related_assignment_id);

        $this->actingAs($counselor)
            ->post(route('guidance.slip-review.issue', [$referral, 'qf-bo-oss-04-client-information-form']))
            ->assertStatus(409);

        $this->actingAs($teacher)
            ->post(route('slips.assign', 'student-referral-slip'), ['student_id' => $student->id])
            ->assertRedirect(route('slips.index'));
        $secondReferral = SlipAssignment::query()->latest('id')->firstOrFail();
        $this->actingAs($student)
            ->post(route('assigned-slips.submit', $secondReferral), [
                'details' => [
                    'student_id' => $student->id_number,
                    'student_name' => $student->name,
                    'program_year' => 'BSIT - First Year',
                    'referral_reasons' => ['academic_deficiencies'],
                ],
            ])
            ->assertSessionHasNoErrors();
        $this->actingAs($counselor)
            ->post(route('guidance.slip-review.issue', [$secondReferral, 'qf-bo-oss-04-client-information-form']))
            ->assertStatus(409);

        $this->actingAs($counselor)
            ->post(route('guidance.slip-review.issue', [$referral, 'class-admission-slip']))
            ->assertRedirect(route('guidance.dashboard'));
        $classAdmission = SlipAssignment::query()
            ->where('slip', 'class-admission-slip')
            ->firstOrFail();

        $this->actingAs($student)
            ->get(route('assigned-slips.fillup', $classAdmission))
            ->assertOk()
            ->assertSee("Student's Reason / Supporting Proof (Excused or Not)");

        $this->actingAs($student)
            ->post(route('assigned-slips.submit', $classAdmission), [
                'details' => [
                    'student_id' => $student->id_number,
                    'student_name' => $student->name,
                    'program_year' => 'BSIT - First Year',
                    'date_absent' => '2026-10-01',
                    'days_absent' => '1 day',
                    'absence_causes' => 'I was ill and am providing my medical certificate for review.',
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(
            'I was ill and am providing my medical certificate for review.',
            $classAdmission->fresh()->slipSubmission->data['absence_causes']
        );
    }

    public function test_students_can_request_only_their_leave_withdrawal_and_readmission_forms(): void
    {
        $student = User::factory()->create([
            'id_number' => 'FLOW-STUDENT-3',
            'gender' => 'female',
            'role' => 'student',
        ]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Request a Form from Guidance')
            ->assertSee('Student Leave of Absence Slip')
            ->assertSee('Student Withdrawal / Dropping / Shifting Counseling Slip')
            ->assertSee('Student Readmission Slip')
            ->assertDontSee('Student Referral Slip');

        $response = $this->actingAs($student)
            ->post(route('student.slip-requests.store', 'student-leaveof-absence-slip'));
        $assignment = SlipAssignment::firstOrFail();

        $response->assertRedirect(route('assigned-slips.fillup', $assignment));
        $this->assertSame($student->id, $assignment->assigned_by);
        $this->assertSame($student->id, $assignment->student_id);
        $this->assertSame('pending', $assignment->status);

        $this->actingAs($student)
            ->post(route('student.slip-requests.store', 'class-admission-slip'))
            ->assertNotFound();
        $this->actingAs($student)
            ->post(route('student.slip-requests.store', 'student-leaveof-absence-slip'))
            ->assertStatus(409);
    }

    public function test_student_dashboard_uses_only_database_assignments_and_submissions(): void
    {
        $teacher = User::factory()->create([
            'id_number' => 'DASH-TEACHER-1',
            'gender' => 'female',
            'role' => 'teacher',
        ]);
        $student = User::factory()->create([
            'id_number' => 'DASH-STUDENT-1',
            'gender' => 'male',
            'role' => 'student',
        ]);
        $pending = SlipAssignment::create([
            'assigned_by' => $teacher->id,
            'student_id' => $student->id,
            'slip' => 'student-leaveof-absence-slip',
        ]);
        SlipAssignment::create([
            'assigned_by' => $teacher->id,
            'student_id' => $student->id,
            'slip' => 'case-referral-slip',
        ]);
        $completedSubmission = SlipSubmission::create([
            'user_id' => $student->id,
            'slip' => 'student-referral-slip',
            'data' => [],
            'pdf_path' => 'slip-submissions/dashboard-test.pdf',
        ]);
        SlipAssignment::create([
            'assigned_by' => $teacher->id,
            'student_id' => $student->id,
            'slip' => 'student-referral-slip',
            'status' => 'submitted',
            'slip_submission_id' => $completedSubmission->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Pending Assigned Slips')
            ->assertSee('Awaiting Approval')
            ->assertSee('Submitted Slips')
            ->assertSee('Student Referral Slip')
            ->assertSee('Student Leave of Absence Slip')
            ->assertDontSee('Case Referral Slip')
            ->assertDontSee('September 15, 2026')
            ->assertDontSee('Academic Concern');
        $response->assertViewHas('pendingAssignmentsCount', 1);
        $response->assertViewHas('awaitingApprovalCount', 1);
        $response->assertViewHas('submittedSlipsCount', 1);

        $this->actingAs($teacher)
            ->get(route('slip-history.index'))
            ->assertForbidden();
    }

    public function test_teacher_assigns_a_student_who_completes_the_slip(): void
    {
        Storage::fake('local');
        $teacher = User::factory()->create([
            'id_number' => 'TEACHER-2001',
            'gender' => 'female',
            'role' => 'teacher',
        ]);
        $student = User::factory()->create([
            'name' => 'Selected Student',
            'id_number' => 'STU-2001',
            'gender' => 'male',
            'role' => 'student',
        ]);
        $otherStudent = User::factory()->create([
            'id_number' => 'STU-2002',
            'gender' => 'female',
            'role' => 'student',
        ]);

        $this->actingAs($teacher)
            ->get(route('slips.fillup', 'student-referral-slip'))
            ->assertOk()
            ->assertSee('Select a Student')
            ->assertSee('Selected Student')
            ->assertDontSee('<option value="'.$teacher->id.'"', false);

        $this->actingAs($teacher)
            ->post(route('slips.assign', 'student-referral-slip'), ['student_id' => $student->id])
            ->assertRedirect(route('slips.index'));

        $assignment = SlipAssignment::firstOrFail();
        $this->assertSame($teacher->id, $assignment->assigned_by);
        $this->assertSame($student->id, $assignment->student_id);
        $this->assertSame('pending', $assignment->status);

        $this->actingAs($student)
            ->get(route('assigned-slips.index'))
            ->assertOk()
            ->assertSee('Student Referral Slip')
            ->assertSee('Not started');

        $this->actingAs($student)
            ->get(route('assigned-slips.fillup', $assignment))
            ->assertOk()
            ->assertSee('Assigned to: <strong>'.$student->name.'</strong>', false)
            ->assertSee($student->id_number);
        $this->actingAs($otherStudent)
            ->get(route('assigned-slips.fillup', $assignment))
            ->assertForbidden();

        $response = $this->actingAs($student)->post(route('assigned-slips.submit', $assignment), [
            'details' => [
                'student_id' => $student->id_number,
                'student_name' => 'Forged Name',
                'program_year' => 'BSIT',
                'referral_reasons' => ['academic_deficiencies'],
            ],
        ]);

        $submission = SlipSubmission::firstOrFail();

        $response->assertRedirect(route('slips.submissions.show', $submission));
        $this->assertSame($student->id, $submission->user_id);
        $this->assertSame($student->id, $submission->data['referred_student_id']);
        $this->assertSame($student->name, $submission->data['student_name']);
        $this->assertSame('submitted', $assignment->fresh()->status);

        $this->actingAs($teacher)
            ->post(route('assigned-slips.submit', $assignment), ['details' => []])
            ->assertForbidden();
        $this->actingAs($teacher)
            ->post(route('guidance.slip-review.approve', $assignment))
            ->assertRedirect(route('teacher.dashboard'));

        $this->actingAs($student)
            ->get(route('assigned-slips.fillup', $assignment))
            ->assertOk()
            ->assertSee('Save Changes')
            ->assertSee('Academic Deficiencies');

        $this->actingAs($student)
            ->post(route('assigned-slips.submit', $assignment), [
                'details' => [
                    'student_id' => $student->id_number,
                    'student_name' => 'Forged Name',
                    'program_year' => 'BSIT',
                    'referral_reasons' => ['academic_deficiencies'],
                ],
            ])
            ->assertRedirect(route('slips.submissions.show', $submission));

        $this->assertSame(1, SlipSubmission::count());
        $this->assertSame(['academic_deficiencies'], $submission->fresh()->data['referral_reasons']);

        $counselor = User::factory()->create([
            'id_number' => 'GUIDANCE-2001',
            'gender' => 'female',
            'role' => 'guidance_counselor',
        ]);

        $this->actingAs($counselor)
            ->get(route('guidance.slip-review'))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Student Referral Slip');

        $this->actingAs($counselor)
            ->post(route('guidance.slip-review.approve', $assignment))
            ->assertRedirect(route('guidance.slip-review'));
        $this->assertSame('approved', $assignment->fresh()->status);
        $this->assertSame('approved', $submission->fresh()->review_status);

        $this->actingAs($student)
            ->get(route('assigned-slips.fillup', $assignment))
            ->assertForbidden();
    }

    public function test_student_referral_stays_editable_until_the_counselor_approves_it(): void
    {
        Storage::fake('local');
        $teacher = User::factory()->create([
            'id_number' => 'EDIT-TEACHER-1',
            'gender' => 'female',
            'role' => 'teacher',
        ]);
        $student = User::factory()->create([
            'id_number' => 'EDIT-STUDENT-1',
            'gender' => 'male',
            'role' => 'student',
        ]);
        $counselor = User::factory()->create([
            'id_number' => 'EDIT-COUNSELOR-1',
            'gender' => 'female',
            'role' => 'guidance_counselor',
        ]);

        $this->actingAs($teacher)
            ->post(route('slips.assign', 'student-referral-slip'), ['student_id' => $student->id])
            ->assertRedirect(route('slips.index'));
        $referral = SlipAssignment::firstOrFail();

        $this->actingAs($student)
            ->post(route('assigned-slips.submit', $referral), [
                'details' => [
                    'student_id' => $student->id_number,
                    'student_name' => $student->name,
                    'program_year' => 'BSIT - First Year',
                    'referral_reasons' => ['academic_deficiencies'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $submission = SlipSubmission::firstOrFail();

        $this->actingAs($counselor)
            ->get(route('guidance.slip-review'))
            ->assertOk()
            ->assertSee('Student Referral Slip')
            ->assertSee($student->name);

        $this->actingAs($student)
            ->get(route('assigned-slips.index'))
            ->assertOk()
            ->assertSee('Awaiting approval')
            ->assertSee('Edit submission');
        $this->actingAs($student)
            ->get(route('assigned-slips.fillup', $referral))
            ->assertOk()
            ->assertSee('Save Changes');

        $this->actingAs($student)
            ->post(route('assigned-slips.submit', $referral), [
                'details' => [
                    'student_id' => $student->id_number,
                    'student_name' => $student->name,
                    'program_year' => 'BSIT - First Year',
                    'referral_reasons' => ['psychological_testing'],
                ],
            ])
            ->assertRedirect(route('slips.submissions.show', $submission));
        $this->assertSame(['psychological_testing'], $submission->fresh()->data['referral_reasons']);
        $this->assertSame('submitted', $referral->fresh()->status);

        $this->actingAs($counselor)
            ->post(route('guidance.slip-review.approve', $referral))
            ->assertRedirect(route('guidance.slip-review'));

        $this->actingAs($student)
            ->get(route('assigned-slips.fillup', $referral))
            ->assertForbidden();
    }

    public function test_student_cannot_open_another_students_slip_submission(): void
    {
        $owner = User::factory()->create([
            'id_number' => 'SLIP-1002',
            'gender' => 'female',
            'role' => 'student',
        ]);
        $otherStudent = User::factory()->create([
            'id_number' => 'SLIP-1003',
            'gender' => 'male',
            'role' => 'student',
        ]);
        $submission = SlipSubmission::create([
            'user_id' => $owner->id,
            'slip' => 'student-referral-slip',
            'data' => [],
            'pdf_path' => 'slip-submissions/test.pdf',
        ]);

        $this->actingAs($otherStudent)
            ->get(route('slips.submissions.show', $submission))
            ->assertForbidden();

        $this->actingAs($otherStudent)
            ->get(route('slip-history.index'))
            ->assertOk()
            ->assertDontSee('student-referral-slip');
    }
}

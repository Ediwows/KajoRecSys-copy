<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guidance_can_manage_teacher_and_student_accounts_with_role_appropriate_actions(): void
    {
        $counselor = $this->createUser([
            'role' => 'guidance_counselor',
        ]);
        $teacher = $this->createUser([
            'name' => 'Teacher Account',
            'role' => 'teacher',
        ]);
        $student = $this->createUser([
            'name' => 'Student Account',
            'role' => 'student',
        ]);
        $this->assertTrue($counselor->is_active);

        $response = $this->actingAs($counselor)->get(route('admin.users.index'));
        $this->assertSame(200, $response->getStatusCode(), $response->headers->get('Location') ?? $response->getContent());
        $response->assertSee('data-system-theme', false)
            ->assertSee('Manage Users')
            ->assertSee('Teacher Account')
            ->assertSee('Student Account')
            ->assertSee('Deactivate')
            ->assertSee('Mark as graduate');

        $this->actingAs($counselor)
            ->post(route('admin.users.status', $teacher), ['action' => 'deactivate'])
            ->assertRedirect(route('admin.users.index'));
        $this->assertFalse($teacher->fresh()->is_active);

        $this->actingAs($counselor)
            ->post(route('admin.users.status', $student), ['action' => 'graduate'])
            ->assertRedirect(route('admin.users.index'));
        $this->assertTrue($student->fresh()->is_graduated);

        $this->actingAs($counselor)
            ->post(route('admin.users.status', $teacher), ['action' => 'activate'])
            ->assertRedirect(route('admin.users.index'));
        $this->assertTrue($teacher->fresh()->is_active);
    }

    public function test_teacher_accounts_cannot_be_marked_as_graduates(): void
    {
        $counselor = $this->createUser(['role' => 'guidance_counselor']);
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($counselor)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.status', $teacher), ['action' => 'graduate'])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('action');

        $this->assertFalse($teacher->fresh()->is_graduated);
    }

    public function test_students_cannot_access_user_management(): void
    {
        $student = $this->createUser(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_deactivated_users_cannot_log_in_or_continue_an_existing_session(): void
    {
        $user = $this->createUser([
            'email' => 'inactive@example.com',
            'password' => 'password',
            'is_active' => false,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * The role and account-state fields are intentionally guarded on User.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(array $attributes): User
    {
        $user = User::factory()->create();
        $user->forceFill($attributes)->save();

        return $user;
    }
}

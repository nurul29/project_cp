<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\Teaching;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_page_displays_school_and_role_specific_account_context(): void
    {
        $classroom = Classroom::factory()->create(['name' => 'VIII A']);
        $student = User::factory()->create([
            'classroom_id' => $classroom->id,
            'username' => 'siswa.081',
            'is_active' => true,
        ]);
        SchoolSetting::query()->updateOrCreate(
            ['id' => 1],
            ['school_name' => 'SMP Negeri 16 Depok', 'school_year' => '2026/2027', 'semester' => 'Ganjil'],
        );

        $this->actingAs($student)
            ->get('/profile')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Edit')
                ->where('profileContext.name', $student->name)
                ->where('profileContext.username', 'siswa.081')
                ->where('profileContext.role', 'Siswa')
                ->where('profileContext.isActive', true)
                ->where('profileContext.school', 'SMP Negeri 16 Depok')
                ->where('profileContext.classroom', 'VIII A')
                ->where('profileContext.subjects', [])
                ->where('profileContext.teachingClassrooms', []));
    }

    public function test_teacher_profile_displays_assigned_subjects_and_classrooms(): void
    {
        $teacher = User::factory()->teacher()->create();
        $subject = Subject::factory()->create(['name' => 'Matematika']);
        $classroom = Classroom::factory()->create(['name' => 'VIII B']);
        Teaching::factory()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
        ]);

        $this->actingAs($teacher)
            ->get('/profile')
            ->assertInertia(fn (Assert $page) => $page
                ->where('profileContext.role', 'Guru')
                ->where('profileContext.subjects', ['Matematika'])
                ->where('profileContext.teachingClassrooms', ['VIII B']));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}

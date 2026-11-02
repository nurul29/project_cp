<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_visiting_home_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_student_visiting_home_is_redirected_to_student_dashboard(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
        ]);

        $response = $this->actingAs($student)->get('/');

        $response->assertRedirect(route('siswa.dashboard'));
    }

    public function test_authenticated_teacher_visiting_home_is_redirected_to_teacher_panel(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get('/');

        $response->assertRedirect(route('filament.guru.pages.dashboard'));
    }

    public function test_authenticated_admin_visiting_home_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('filament.admin.pages.dashboard'));
    }

    public function test_authenticated_student_visiting_legacy_dashboard_url_is_redirected_to_student_dashboard(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
        ]);

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertRedirect(route('siswa.dashboard'));
    }

    public function test_student_can_access_student_dashboard(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
        ]);

        $response = $this->actingAs($student)->get('/siswa/dashboard');

        $response->assertStatus(200);
    }

    public function test_teacher_cannot_access_student_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get('/siswa/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_student_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/siswa/dashboard');

        $response->assertStatus(403);
    }
}

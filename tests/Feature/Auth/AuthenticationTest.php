<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    public function test_student_can_authenticate_using_username_and_redirects_to_student_dashboard(): void
    {
        $student = User::factory()->create([
            'username' => 'siswa.081',
            'password' => bcrypt('password'),
            'role' => UserRole::Student,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'siswa.081',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($student);
        $response->assertRedirect(route('siswa.dashboard', absolute: false));
    }

    public function test_student_login_ignores_a_stale_intended_url_and_goes_to_student_dashboard(): void
    {
        $student = User::factory()->create([
            'username' => 'siswa.dashboard',
            'password' => bcrypt('password'),
            'role' => UserRole::Student,
        ]);

        $this->get('/guru')->assertRedirect('/login');

        $response = $this->post('/login', [
            'username' => $student->username,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($student);
        $response->assertRedirect(route('siswa.dashboard', absolute: false));
    }

    public function test_seeded_student_account_can_authenticate(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'username' => 'siswa.081',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs(User::where('username', 'siswa.081')->firstOrFail());
        $response->assertRedirect(route('siswa.dashboard', absolute: false));
    }

    public function test_teacher_can_authenticate_using_email_or_username_and_redirects_to_teacher_panel(): void
    {
        $teacher = User::factory()->create([
            'username' => 'pak.arif',
            'email' => 'arif@sundigital.sch.id',
            'password' => bcrypt('password'),
            'role' => UserRole::Teacher,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'arif@sundigital.sch.id',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($teacher);
        $response->assertRedirect('/guru');
        $this->get('/guru')->assertOk()->assertSee('Keluar');
    }

    public function test_inertia_teacher_login_performs_a_full_page_redirect_to_filament(): void
    {
        $teacher = User::factory()->teacher()->create([
            'username' => 'pak.arif',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->withHeaders(['X-Inertia' => 'true'])->post('/login', [
            'username' => $teacher->username,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($teacher);
        $response->assertStatus(409);
        $response->assertHeader('X-Inertia-Location', '/guru');
    }

    public function test_admin_can_authenticate_using_username_and_redirects_to_admin_panel(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@sundigital.sch.id',
            'password' => bcrypt('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Keluar');
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $inactiveStudent = User::factory()->create([
            'username' => 'siswa.nonaktif',
            'password' => bcrypt('password'),
            'role' => UserRole::Student,
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'username' => 'siswa.nonaktif',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'siswa.081',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'username' => 'siswa.081',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}

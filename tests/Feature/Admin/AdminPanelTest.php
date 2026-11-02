<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Filament\Admin\Pages\SchoolSettingsPage;
use App\Filament\Admin\Resources\AccountRequestResource\Pages\EditAccountRequest;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Filament\Admin\Widgets\AdminOverview;
use App\Models\AccountRequest;
use App\Models\ActivityLog;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_panel_and_management_resources(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/users/create')->assertOk();
        $this->actingAs($admin)->get('/admin/classrooms')->assertOk();
        $this->actingAs($admin)->get('/admin/classrooms/create')->assertOk();
        $this->actingAs($admin)->get('/admin/subjects')->assertOk();
        $this->actingAs($admin)->get('/admin/subjects/create')->assertOk();
        $this->actingAs($admin)->get('/admin/account-requests')->assertOk();
        $this->actingAs($admin)->get('/admin/activity-logs')->assertOk();
        $this->actingAs($admin)->get('/admin/school-settings')->assertOk();
    }

    public function test_students_and_teachers_cannot_access_admin_panel(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($student)->get('/admin')->assertForbidden();
        $this->actingAs($teacher)->get('/admin')->assertForbidden();
    }

    public function test_admin_dashboard_displays_school_overview_and_activity_sections(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(AdminOverview::class)
            ->assertSee('Perpustakaan yang tertata')
            ->assertSee('Perlu ditindaklanjuti')
            ->assertSee('Akses cepat')
            ->assertSee('Aktivitas terbaru');
    }

    public function test_admin_settings_can_be_saved(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(SchoolSettingsPage::class)
            ->fillForm([
                'school_name' => 'SMP Contoh',
                'school_year' => '2027/2028',
                'semester' => 'Genap',
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas('school_settings', [
            'school_name' => 'SMP Contoh',
            'school_year' => '2027/2028',
            'semester' => 'Genap',
        ]);
    }

    public function test_admin_can_process_an_account_request(): void
    {
        $admin = User::factory()->admin()->create();
        $request = AccountRequest::query()->create([
            'name' => 'Calon Guru',
            'username' => 'calonguru',
            'email' => 'calon@example.test',
            'status' => 'pending',
        ]);
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(EditAccountRequest::class, [
                'record' => $request->getRouteKey(),
            ])
            ->fillForm([
                'status' => 'resolved',
                'admin_notes' => 'Akun sudah dipulihkan.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('account_requests', [
            'id' => $request->id,
            'status' => 'resolved',
            'handled_by' => $admin->id,
            'admin_notes' => 'Akun sudah dipulihkan.',
        ]);
    }

    public function test_admin_can_reset_a_user_password_from_the_user_list(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableAction('resetPassword', $user, ['password' => 'newpass123'])
            ->assertHasNoTableActionErrors();

        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }

    public function test_admin_activity_is_audited_without_recording_password_values(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $request = AccountRequest::query()->create([
            'name' => 'Calon Guru',
            'username' => 'calonguru',
            'email' => 'calon@example.test',
            'message' => 'Mohon bantuan akses',
        ]);

        $log = ActivityLog::query()->where('subject_id', $request->id)->firstOrFail();

        $this->assertSame($admin->id, $log->actor_id);
        $this->assertSame('created', $log->event);
        $this->assertArrayNotHasKey('password', $log->properties['fields']);
    }

    public function test_material_cannot_be_deleted_while_linked_to_a_published_quiz(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $material = Material::factory()->create();

        Quiz::factory()->create([
            'material_id' => $material->id,
            'status' => 'published',
        ]);

        try {
            $material->delete();
            $this->fail('The linked material should not have been deleted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('record', $exception->errors());
        }

        $this->assertDatabaseHas('materials', ['id' => $material->id]);
    }

    public function test_classroom_and_subject_cannot_be_deleted_while_assigned(): void
    {
        $classroom = Classroom::factory()->create();
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $classroom->teachings()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);

        foreach ([$classroom, $subject] as $record) {
            try {
                $record->delete();
                $this->fail('A classroom or subject with teaching assignments should not be deleted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('record', $exception->errors());
            }
        }

        $this->assertDatabaseHas('classrooms', ['id' => $classroom->id]);
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    public function test_account_and_classroom_deletion_are_blocked_when_users_depend_on_them(): void
    {
        $teacher = User::factory()->teacher()->create();
        $classroom = Classroom::factory()->create([
            'homeroom_teacher_id' => $teacher->id,
        ]);
        $student = User::factory()->create([
            'classroom_id' => $classroom->id,
            'is_active' => true,
        ]);

        foreach ([$teacher, $classroom] as $record) {
            try {
                $record->delete();
                $this->fail('The account or classroom should not have been deleted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('record', $exception->errors());
            }
        }

        $this->assertDatabaseHas('users', ['id' => $student->id]);
        $this->assertDatabaseHas('classrooms', ['id' => $classroom->id]);
    }
}

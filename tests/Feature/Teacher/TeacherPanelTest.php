<?php

namespace Tests\Feature\Teacher;

use App\Filament\Teacher\Widgets\TeacherOverview;
use App\Filament\Teacher\Widgets\TeacherStatsOverview;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Subject;
use App\Models\Teaching;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_access_teacher_panel_dashboard(): void
    {
        $this->assertLessThan(TeacherOverview::getSort(), TeacherStatsOverview::getSort());

        $teacher = User::factory()->teacher()->create();

        $classroom = Classroom::factory()->create();
        $subject = Subject::factory()->create();
        Teaching::create([
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);
        Material::factory()->create([
            'created_by' => $teacher->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'title' => 'Modul terbaru guru',
        ]);
        $this->actingAs($teacher)
            ->get('/guru')
            ->assertOk()
            ->assertSee('Tambah koleksi');

        Filament::setCurrentPanel(Filament::getPanel('guru'));
        $html = Livewire::actingAs($teacher)
            ->test(TeacherOverview::class)
            ->html();

        $this->assertStringContainsString('Bangun koleksi yang', $html);
        $this->assertStringContainsString('Koleksi saya', $html);
        $this->assertStringContainsString('Koleksi siap dibaca', $html);
        $this->assertStringContainsString('Modul terbaru guru', $html);
        $this->assertStringContainsString('Peran guru di perpustakaan', $html);
        $this->assertStringContainsString('href="http://localhost/guru/materials"', $html);

        Livewire::actingAs($teacher)
            ->test(TeacherStatsOverview::class)
            ->assertSee('Buku & bacaan')
            ->assertSee('Koleksi terbit')
            ->assertSee('Draf koleksi')
            ->assertSee('Materi pendukung');
    }

    public function test_student_cannot_access_teacher_panel(): void
    {
        $student = User::factory()->create(); // default role is Student

        $response = $this->actingAs($student)->get('/guru');

        // Filament returns 403 for authenticated users who fail canAccessPanel()
        $response->assertForbidden();
    }

    public function test_teacher_can_access_grade_report_page(): void
    {
        $teacher = User::factory()->teacher()->create();
        $classroom = Classroom::factory()->create();
        $subject = Subject::factory()->create();

        Teaching::create([
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($teacher)->get('/guru/grade-report');

        $response->assertOk();
    }

    public function test_teacher_can_access_materials_resource(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get('/guru/materials');

        $response->assertOk();
    }

    public function test_teacher_can_access_quizzes_resource(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get('/guru/quizzes');

        $response->assertOk();
    }
}

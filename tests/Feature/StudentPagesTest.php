<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\PortfolioItem;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Teaching;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_open_each_learning_page(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $material = Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
        ]);
        $assignment = Assignment::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
        ]);
        $quiz = Quiz::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
        ]);
        Question::factory()->create(['quiz_id' => $quiz->id]);
        PortfolioItem::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
        ]);

        $this->actingAs($student);

        foreach ([
            route('siswa.dashboard'),
            route('siswa.subjects.index'),
            route('siswa.library.index'),
            route('siswa.catalog.index'),
            route('siswa.shelf.index'),
            route('siswa.reading-history.index'),
            route('siswa.library.show', $material),
            route('siswa.videos.index'),
            route('siswa.worksheets.index'),
            route('siswa.worksheets.show', $assignment),
            route('siswa.quizzes.index'),
            route('siswa.quizzes.show', $quiz),
            route('siswa.portfolio.index'),
            route('siswa.grades.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_student_routes_use_the_documented_indonesian_paths(): void
    {
        $this->assertSame('/siswa/mapel', route('siswa.subjects.index', absolute: false));
        $this->assertSame('/siswa/perpustakaan', route('siswa.library.index', absolute: false));
        $this->assertSame('/siswa/katalog', route('siswa.catalog.index', absolute: false));
        $this->assertSame('/siswa/rak', route('siswa.shelf.index', absolute: false));
        $this->assertSame('/siswa/riwayat-bacaan', route('siswa.reading-history.index', absolute: false));
        $this->assertSame('/siswa/video', route('siswa.videos.index', absolute: false));
        $this->assertSame('/siswa/lembar-kerja', route('siswa.worksheets.index', absolute: false));
        $this->assertSame('/siswa/lembar-kerja/1/submit', route('siswa.worksheets.submit', 1, absolute: false));
        $this->assertSame('/siswa/kuis', route('siswa.quizzes.index', absolute: false));
        $this->assertSame('/siswa/kuis/1/finish', route('siswa.quizzes.finish', 1, absolute: false));
        $this->assertSame('/siswa/portofolio', route('siswa.portfolio.index', absolute: false));
        $this->assertSame('/siswa/nilai', route('siswa.grades.index', absolute: false));
    }

    public function test_dashboard_progress_counts_submissions_and_quiz_attempts_against_active_activities(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $activeAssignment = Assignment::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
        ]);
        Assignment::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'status' => AssignmentStatus::Closed,
        ]);
        $activeQuiz = Quiz::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
        ]);
        Quiz::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'status' => 'closed',
        ]);
        Submission::factory()->create([
            'assignment_id' => $activeAssignment->id,
            'student_id' => $student->id,
        ]);
        QuizAttempt::factory()->create([
            'quiz_id' => $activeQuiz->id,
            'student_id' => $student->id,
        ]);
        QuizAttempt::factory()->create([
            'quiz_id' => $activeQuiz->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($student)
            ->withSession(['success' => 'Progres diperbarui.'])
            ->get(route('siswa.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('progress.completed', 2)
                ->where('progress.total', 2)
                ->where('progress.percentage', 100)
                ->where('auth.user.classroom.name', $classroom->name)
                ->where('subjectsCount', 1)
                ->has('featuredMaterials')
                ->where('videoCount', 0)
                ->where('flash.success', 'Progres diperbarui.'));
    }

    public function test_library_search_matches_published_titles_and_descriptions(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();

        Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'type' => MaterialType::Module,
            'title' => 'Dasar Geometri',
            'description' => 'Mengenal bentuk dasar',
        ]);
        Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'type' => MaterialType::Module,
            'title' => 'Bacaan Sains',
            'description' => 'Geometri di sekitar kita',
        ]);

        $this->actingAs($student)
            ->get(route('siswa.library.index', ['q' => 'Geometri']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('search', 'Geometri')
                ->has('materials', 2));
    }

    public function test_catalog_search_matches_subject_names(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $subject = Subject::factory()->create(['name' => 'Bahasa Indonesia']);
        $teacher = User::factory()->teacher()->create();
        Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'type' => MaterialType::Module,
            'title' => 'Teks Eksplanasi',
            'description' => 'Memahami struktur teks',
        ]);

        $this->actingAs($student)
            ->get(route('siswa.catalog.index', ['q' => 'Bahasa Indonesia']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Library/Index')
                ->where('search', 'Bahasa Indonesia')
                ->has('materials', 1)
                ->where('materials.0.title', 'Teks Eksplanasi'));
    }

    public function test_students_can_save_and_remove_visible_materials_from_their_shelf(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $subject = Subject::factory()->create();
        $material = Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'type' => MaterialType::Module,
        ]);

        $this->actingAs($student)
            ->from(route('siswa.catalog.index'))
            ->post(route('siswa.shelf.toggle', $material))
            ->assertRedirect(route('siswa.catalog.index'));

        $this->assertDatabaseHas('shelf_items', [
            'user_id' => $student->id,
            'material_id' => $material->id,
        ]);

        $this->get(route('siswa.shelf.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Library/Shelf')
                ->has('materials', 1)
                ->where('materials.0.id', $material->id));

        $this->from(route('siswa.shelf.index'))
            ->post(route('siswa.shelf.toggle', $material))
            ->assertRedirect(route('siswa.shelf.index'));

        $this->assertDatabaseMissing('shelf_items', [
            'user_id' => $student->id,
            'material_id' => $material->id,
        ]);
        $this->get(route('siswa.shelf.index'))
            ->assertInertia(fn (Assert $page) => $page->has('materials', 0));
    }

    public function test_opening_a_material_updates_one_student_scoped_reading_history_entry(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $otherStudent = User::factory()->create(['classroom_id' => $classroom->id]);
        $subject = Subject::factory()->create();
        $material = Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'type' => MaterialType::Module,
        ]);

        $this->actingAs($student)
            ->get(route('siswa.library.show', $material))
            ->assertOk();
        $this->get(route('siswa.library.show', $material))
            ->assertOk();

        $this->assertDatabaseCount('reading_histories', 1);
        $this->assertDatabaseHas('reading_histories', [
            'user_id' => $student->id,
            'material_id' => $material->id,
        ]);

        $this->get(route('siswa.reading-history.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Library/History')
                ->has('materials', 1)
                ->where('materials.0.id', $material->id));

        $this->actingAs($otherStudent)
            ->get(route('siswa.reading-history.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Library/History')
                ->has('materials', 0));
    }

    public function test_shared_teacher_context_includes_the_assigned_subject_and_classroom(): void
    {
        $teacher = User::factory()->teacher()->create();
        $classroom = Classroom::factory()->create(['grade_level' => 'VIII']);
        $subject = Subject::factory()->create(['name' => 'Matematika']);
        Teaching::factory()->create([
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('profile.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user.teachings.0.subject.name', 'Matematika')
                ->where('auth.user.teachings.0.classroom.grade_level', 'VIII'));
    }

    public function test_student_pages_only_show_class_material_and_never_share_quiz_answer_keys(): void
    {
        $classroom = Classroom::factory()->create();
        $otherClassroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();

        Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'type' => MaterialType::Module,
            'title' => 'Materi untuk kelas siswa',
        ]);
        $draftMaterial = Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'type' => MaterialType::Module,
            'status' => PublicationStatus::Draft,
            'title' => 'Draf untuk guru',
        ]);
        Material::factory()->create([
            'classroom_id' => $otherClassroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'type' => MaterialType::Module,
            'title' => 'Materi kelas lain',
        ]);
        $quiz = Quiz::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
        ]);
        Question::factory()->create([
            'quiz_id' => $quiz->id,
            'body' => 'Pertanyaan siswa',
            'correct_index' => 2,
        ]);
        $draftQuiz = Quiz::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'status' => PublicationStatus::Draft,
        ]);

        $this->actingAs($student)
            ->get(route('siswa.library.index', ['subject' => $subject->slug]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Library/Index')
                ->where('selectedSubject', $subject->slug)
                ->has('materials', 1)
                ->where('materials.0.title', 'Materi untuk kelas siswa'));

        $this->get(route('siswa.quizzes.show', $quiz))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Quizzes/Show')
                ->where('quiz.questions.0.body', 'Pertanyaan siswa')
                ->missing('quiz.questions.0.correct_index'));

        $this->get(route('siswa.library.show', $draftMaterial))->assertNotFound();
        $this->get(route('siswa.quizzes.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student/Quizzes/Index')
                ->has('quizzes', 1));
        $this->get(route('siswa.quizzes.show', $draftQuiz))->assertNotFound();
    }

    public function test_student_cannot_open_material_assignment_or_quiz_for_another_class(): void
    {
        $student = User::factory()->create([
            'classroom_id' => Classroom::factory(),
        ]);
        $otherClassroom = Classroom::factory()->create();
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $material = Material::factory()->create([
            'classroom_id' => $otherClassroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
        ]);
        $assignment = Assignment::factory()->create([
            'classroom_id' => $otherClassroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
        ]);
        $quiz = Quiz::factory()->create([
            'classroom_id' => $otherClassroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
        ]);

        $this->actingAs($student);

        $this->get(route('siswa.library.show', $material))->assertNotFound();
        $this->get(route('siswa.worksheets.show', $assignment))->assertNotFound();
        $this->get(route('siswa.quizzes.show', $quiz))->assertNotFound();
    }
}

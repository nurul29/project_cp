<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    public function index(Request $request): Response
    {
        $classrooms = Classroom::with('students')->orderBy('name')->get();
        $selectedClassroomId = $request->input('classroom_id') ?? $classrooms->first()?->id;

        $classroom = $classrooms->firstWhere('id', $selectedClassroomId);
        $students = $classroom?->students ?? collect();

        $assignments = Assignment::where('classroom_id', $selectedClassroomId)->get();

        $gradesMatrix = $students->map(function ($student) use ($assignments) {
            $studentSubmissions = Submission::where('student_id', $student->id)->get()->keyBy('assignment_id');
            $scores = [];
            foreach ($assignments as $assignment) {
                $scores[$assignment->id] = $studentSubmissions->get($assignment->id)?->score;
            }

            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'username' => $student->username,
                'scores' => $scores,
            ];
        });

        return Inertia::render('Teacher/Grades/Index', [
            'classrooms' => $classrooms,
            'selectedClassroomId' => $selectedClassroomId,
            'assignments' => $assignments,
            'gradesMatrix' => $gradesMatrix,
        ]);
    }
}

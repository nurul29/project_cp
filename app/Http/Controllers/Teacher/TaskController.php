<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $assignments = Assignment::query()
            ->with(['subject', 'classroom', 'submissions'])
            ->latest()
            ->get();

        $quizzes = Quiz::query()
            ->with(['subject', 'classroom', 'attempts'])
            ->latest()
            ->get();

        $subjects = Subject::orderBy('name')->get(['id', 'name']);
        $classrooms = Classroom::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Teacher/Tasks/Index', [
            'assignments' => $assignments,
            'quizzes' => $quizzes,
            'subjects' => $subjects,
            'classrooms' => $classrooms,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'instructions' => 'required|string',
            'due_at' => 'nullable|date',
        ]);

        Assignment::create([
            'title' => $validated['title'],
            'subject_id' => $validated['subject_id'],
            'classroom_id' => $validated['classroom_id'],
            'instructions' => $validated['instructions'],
            'due_at' => $validated['due_at'] ?? now()->addDays(7),
            'teacher_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Aktivitas tugas berhasil dibuat.');
    }
}

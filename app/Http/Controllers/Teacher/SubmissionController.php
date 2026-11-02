<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function index(Request $request): Response
    {
        $submissions = Submission::query()
            ->with(['student.classroom', 'assignment.subject'])
            ->latest()
            ->get();

        return Inertia::render('Teacher/Submissions/Index', [
            'submissions' => $submissions,
        ]);
    }

    public function grade(Request $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_at' => now(),
            'status' => 'graded',
        ]);

        return back()->with('success', 'Nilai dan umpan balik berhasil disimpan.');
    }
}

<?php

namespace App\Http\Controllers\Student;

use App\Actions\Student\SubmitAssignment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorksheetController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Assignment::class);

        $assignments = Assignment::query()
            ->where('classroom_id', $request->user()->classroom_id)
            ->with(['subject:id,name,icon,tone', 'submissions' => fn ($query) => $query
                ->where('student_id', $request->user()->id)])
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->get();

        return Inertia::render('Student/Worksheets/Index', [
            'assignments' => AssignmentResource::collection($assignments)->resolve($request),
        ]);
    }

    public function show(Request $request, Assignment $assignment): Response
    {
        abort_unless($request->user()->can('view', $assignment), 404);

        $assignment->load([
            'subject:id,name,icon,tone',
            'submissions' => fn ($query) => $query->where('student_id', $request->user()->id),
        ]);

        return Inertia::render('Student/Worksheets/Show', [
            'assignment' => (new AssignmentResource($assignment))->resolve($request),
        ]);
    }

    public function submit(
        SubmitAssignmentRequest $request,
        Assignment $assignment,
        SubmitAssignment $submitAssignment,
    ): RedirectResponse {
        $submitAssignment->handle($assignment, $request->user(), $request->validated('answer'));

        return to_route('siswa.worksheets.show', $assignment)
            ->with('success', 'Tugas berhasil dikumpulkan.');
    }
}

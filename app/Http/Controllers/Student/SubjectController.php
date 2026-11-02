<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Student/Subjects/Index', [
            'subjects' => SubjectResource::collection(Subject::query()
                ->withCount(['materials' => fn (Builder $query) => $query->visibleToStudent($request->user())])
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'icon', 'description', 'tone']))->resolve($request),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Student;

use App\Enums\MaterialType;
use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Material::class);

        $videos = Material::query()
            ->visibleToStudent($request->user())
            ->where('type', MaterialType::Video->value)
            ->with('subject:id,name,slug,icon,tone')
            ->latest()
            ->get();

        return Inertia::render('Student/Videos/Index', [
            'videos' => MaterialResource::collection($videos)->resolve($request),
        ]);
    }
}

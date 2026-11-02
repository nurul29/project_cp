<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Student/Portfolio/Index', [
            'items' => PortfolioItem::query()
                ->where('student_id', $request->user()->id)
                ->with(['subject:id,name,icon,tone', 'submission:id,status,score,feedback'])
                ->latest()
                ->get()
                ->map(fn (PortfolioItem $item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'status' => $item->submission?->status->value ?? $item->status,
                    'score' => $item->submission?->score,
                    'subject' => $item->subject->name,
                    'icon' => $item->subject->icon,
                    'tone' => $item->subject->tone,
                    'createdAt' => $item->created_at->toIso8601String(),
                ]),
        ]);
    }
}

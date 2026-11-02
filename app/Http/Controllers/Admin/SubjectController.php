<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    public function index(Request $request): Response
    {
        $subjects = Subject::query()
            ->with('teachings.teacher')
            ->orderBy('code')
            ->get()
            ->map(function ($subject) {
                $teacher = $subject->teachings->first()?->teacher?->name ?? 'Belum ditentukan';
                return [
                    'id' => $subject->id,
                    'code' => $subject->code ?? ('MP' . str_pad($subject->id, 2, '0', STR_PAD_LEFT)),
                    'name' => $subject->name,
                    'slug' => $subject->slug,
                    'teacher' => $teacher,
                    'description' => $subject->description,
                ];
            });

        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:subjects,code',
            'description' => 'nullable|string',
        ]);

        Subject::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'code' => $validated['code'],
            'description' => $validated['description'] ?? '',
        ]);

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
        ]);

        $subject->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'code' => $validated['code'],
            'description' => $validated['description'] ?? '',
        ]);

        return back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();
        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}

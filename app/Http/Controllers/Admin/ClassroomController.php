<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClassroomController extends Controller
{
    public function index(Request $request): Response
    {
        $schoolYearFilter = $request->input('school_year', 'Semua');

        $query = Classroom::query()
            ->with(['students', 'homeroomTeacher'])
            ->ordered();

        if ($schoolYearFilter && $schoolYearFilter !== 'Semua') {
            $query->where('school_year', $schoolYearFilter);
        }

        $classrooms = $query->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'grade_level' => $class->grade_level,
                    'teacher' => $class->homeroomTeacher?->name ?? 'Belum ditentukan',
                    'homeroom_teacher_id' => $class->homeroom_teacher_id,
                    'student_count' => $class->students->count(),
                    'academic_year' => $class->school_year ?? '2026/2027',
                    'is_active' => (bool) ($class->is_active ?? true),
                    'delete_reasons' => $class->getDeletePreventionReasons(),
                ];
            });

        $teachers = User::where('role', 'teacher')->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $schoolYears = Classroom::query()->whereNotNull('school_year')->distinct()->pluck('school_year')->filter()->values()->all();
        if (empty($schoolYears)) {
            $schoolYears = ['2026/2027', '2025/2026'];
        }

        return Inertia::render('Admin/Classes/Index', [
            'classrooms' => $classrooms,
            'teachers' => $teachers,
            'schoolYears' => $schoolYears,
            'selectedSchoolYear' => $schoolYearFilter,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('classrooms', 'name'),
            ],
            'grade_level' => 'nullable|string|max:20',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'nullable|string|max:20',
        ], [
            'name.unique' => 'Nama kelas sudah terdaftar dan tidak boleh sama.',
        ]);

        $gradeLevel = $validated['grade_level'] ?? null;
        if (! $gradeLevel) {
            if (str_starts_with($validated['name'], 'VIII')) {
                $gradeLevel = 'VIII';
            } elseif (str_starts_with($validated['name'], 'IX')) {
                $gradeLevel = 'IX';
            } else {
                $gradeLevel = 'VII';
            }
        }

        Classroom::create([
            'name' => $validated['name'],
            'grade_level' => $gradeLevel,
            'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?? null,
            'school_year' => $validated['academic_year'] ?? '2026/2027',
            'is_active' => true,
        ]);

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('classrooms', 'name')->ignore($classroom->id),
            ],
            'grade_level' => 'nullable|string|max:20',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'nullable|string|max:20',
        ], [
            'name.unique' => 'Nama kelas sudah terdaftar dan tidak boleh sama.',
        ]);

        $gradeLevel = $validated['grade_level'] ?? $classroom->grade_level;
        if (! $gradeLevel) {
            if (str_starts_with($validated['name'], 'VIII')) {
                $gradeLevel = 'VIII';
            } elseif (str_starts_with($validated['name'], 'IX')) {
                $gradeLevel = 'IX';
            } else {
                $gradeLevel = 'VII';
            }
        }

        $classroom->update([
            'name' => $validated['name'],
            'grade_level' => $gradeLevel,
            'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?? null,
            'school_year' => $validated['academic_year'] ?? $classroom->school_year ?? '2026/2027',
        ]);

        return back()->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        $reasons = $classroom->getDeletePreventionReasons();
        if (! empty($reasons)) {
            return back()->with('error', 'Penghapusan kelas ditolak: ' . implode(' ', $reasons));
        }

        $classroom->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    public function toggleActive(Classroom $classroom): RedirectResponse
    {
        $classroom->update(['is_active' => ! $classroom->is_active]);
        $status = $classroom->is_active ? 'diaktifkan kembali' : 'dinonaktifkan / diarsipkan';
        return back()->with('success', "Kelas {$classroom->name} berhasil {$status}.");
    }
}

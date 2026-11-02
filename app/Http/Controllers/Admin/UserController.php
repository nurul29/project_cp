<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()->with(['classroom', 'teachings.subject']);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            if ($role !== 'Semua') {
                $query->where('role', $role);
            }
        }

        if ($status = $request->input('status')) {
            if ($status !== 'Semua') {
                $query->where('is_active', $status === 'true');
            }
        }

        $users = $query->orderBy('name')->get();
        $classrooms = Classroom::ordered()->get(['id', 'name']);
        $subjects = Subject::orderBy('code')->get(['id', 'name']);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'classrooms' => $classrooms,
            'subjects' => $subjects,
            'filters' => [
                'q' => $request->input('q', ''),
                'role' => $request->input('role', 'Semua'),
                'status' => $request->input('status', 'Semua'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'role' => 'required|in:student,teacher,admin',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->symbols()],
            'classroom_id' => 'nullable|exists:classrooms,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'classroom_id' => $validated['role'] === 'student' ? ($validated['classroom_id'] ?? null) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($validated['role'] === 'teacher' && ! empty($validated['subject_id'])) {
            $classroomIds = ! empty($validated['classroom_id'])
                ? [$validated['classroom_id']]
                : Classroom::pluck('id')->all();

            foreach ($classroomIds as $cid) {
                \App\Models\Teaching::firstOrCreate([
                    'teacher_id' => $user->id,
                    'subject_id' => $validated['subject_id'],
                    'classroom_id' => $cid,
                ]);
            }
        }

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:student,teacher,admin',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'classroom_id' => $validated['role'] === 'student' ? ($validated['classroom_id'] ?? null) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($validated['role'] === 'teacher' && ! empty($validated['subject_id'])) {
            $user->teachings()->delete();
            $classroomIds = ! empty($validated['classroom_id'])
                ? [$validated['classroom_id']]
                : Classroom::pluck('id')->all();

            foreach ($classroomIds as $cid) {
                \App\Models\Teaching::firstOrCreate([
                    'teacher_id' => $user->id,
                    'subject_id' => $validated['subject_id'],
                    'classroom_id' => $cid,
                ]);
            }
        } elseif ($validated['role'] !== 'teacher') {
            $user->teachings()->delete();
        }

        return back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)->symbols()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', "Kata sandi untuk {$user->name} berhasil diubah.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}

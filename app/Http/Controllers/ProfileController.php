<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\SchoolSetting;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Admin dan Guru diarahkan ke halaman profil resmi pada panel Filament masing-masing
        if ($user->isAdmin()) {
            return redirect('/admin/profile');
        }
        if ($user->isTeacher()) {
            return redirect('/guru/profile');
        }

        $user->loadMissing([
            'classroom.homeroomTeacher:id,name,username',
        ]);

        $school = SchoolSetting::current();
        $classroom = $user->classroom;
        $homeroomTeacher = $classroom?->homeroomTeacher;

        $stats = [
            'shelfCount' => $user->shelfItems()->count(),
            'readingCount' => $user->readingHistories()->count(),
            'submissionsCount' => $user->submissions()->count(),
            'quizAttemptsCount' => $user->quizAttempts()->count(),
        ];

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'profileContext' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => 'Siswa',
                'isActive' => $user->is_active,
                'school' => $school->school_name,
                'schoolYear' => $classroom?->school_year ?? $school->school_year,
                'classroom' => $classroom?->name ?? 'Belum ditentukan',
                'homeroomTeacher' => $homeroomTeacher ? $homeroomTeacher->name : 'Belum ditentukan',
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

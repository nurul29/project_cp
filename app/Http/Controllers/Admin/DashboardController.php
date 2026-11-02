<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use App\Models\ActivityLog;
use App\Models\Classroom;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $settings = SchoolSetting::current();

        $activeUsersCount = User::where('is_active', true)->count();
        $totalUsersCount = User::count();
        $activeClassesCount = Classroom::count();
        $subjectsCount = Subject::count();
        $pendingCount = AccountRequest::where('status', 'pending')->count();

        $requests = AccountRequest::query()
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = ActivityLog::query()
            ->with('causer')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'settings' => [
                'school_name' => $settings->school_name ?? 'SMP Negeri 16 Depok',
                'school_year' => $settings->school_year ?? '2026/2027',
                'semester' => $settings->semester ?? 'Ganjil',
            ],
            'stats' => [
                'activeUsers' => $activeUsersCount,
                'totalUsers' => $totalUsersCount,
                'activeClasses' => $activeClassesCount,
                'subjects' => $subjectsCount,
                'pendingRequests' => $pendingCount,
            ],
            'requests' => $requests,
            'recentActivities' => $recentActivities,
        ]);
    }
}

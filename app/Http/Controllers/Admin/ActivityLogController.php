<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = ActivityLog::query()
            ->with('causer')
            ->latest()
            ->paginate(20)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'text' => $log->description,
                    'actor' => $log->causer?->name ?? 'Sistem',
                    'date' => $log->created_at->format('d M Y, H.i'),
                ];
            });

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
        ]);
    }
}

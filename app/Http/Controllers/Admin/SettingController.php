<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(Request $request): Response
    {
        $settings = SchoolSetting::current();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'school_name' => $settings->school_name ?? 'SMP Negeri 16 Depok',
                'school_year' => $settings->school_year ?? '2026/2027',
                'semester' => $settings->semester ?? 'Ganjil',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'school_year' => 'required|string|max:20',
            'semester' => 'required|string|max:20',
        ]);

        $settings = SchoolSetting::firstOrNew();
        $settings->fill($validated);
        $settings->save();

        return back()->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }
}

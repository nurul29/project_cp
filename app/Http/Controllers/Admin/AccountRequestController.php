<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $requests = AccountRequest::query()
            ->latest()
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'name' => $req->name ?? 'Permohonan Bantuan',
                    'username' => $req->username,
                    'type' => $req->type ?? 'Lupa kata sandi',
                    'date' => $req->created_at->format('d M Y, H.i'),
                    'status' => $req->status ?? 'pending',
                    'notes' => $req->notes ?? '',
                ];
            });

        return Inertia::render('Admin/AccountRequests/Index', [
            'requests' => $requests,
        ]);
    }

    public function resolve(Request $request, AccountRequest $accountRequest): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'status' => 'required|in:resolved,rejected',
        ]);

        $accountRequest->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? 'Diverifikasi oleh admin sekolah',
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Permintaan bantuan berhasil ditindaklanjuti.');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountHelpController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
        ]);

        $user = User::query()
            ->where('username', $validated['username'])
            ->first();

        $roleText = $user ? ($user->isStudent() ? 'Siswa' : ($user->isTeacher() ? 'Guru' : 'Admin')) : 'Pengguna';

        AccountRequest::query()->create([
            'name' => $user?->name ?? $validated['username'],
            'username' => $user?->username ?? $validated['username'],
            'email' => $user?->email,
            'message' => "Permintaan bantuan pemulihan kata sandi akun {$roleText}.",
            'status' => AccountRequestStatus::Pending,
        ]);

        return back()->with(
            'success',
            'Permintaan bantuan dicatat. Admin sekolah akan memverifikasi identitas melalui prosedur sekolah.',
        );
    }
}

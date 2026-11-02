<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Redirect "/" to appropriate role dashboard or login.
     */
    public function index(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        return match ($user->role) {
            UserRole::Student => redirect()->route('siswa.dashboard'),
            UserRole::Teacher => redirect()->route('guru.dashboard'),
            UserRole::Admin => redirect()->route('admin.dashboard'),
            default => redirect()->route('login'),
        };
    }
}

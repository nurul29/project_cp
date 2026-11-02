<?php

namespace App\Filament\Admin\Pages\Auth;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\User;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Contracts\Support\Htmlable;

class AdminEditProfile extends BaseEditProfile
{
    public function getView(): string
    {
        return 'filament.admin.pages.auth.admin-edit-profile';
    }

    public function getTitle(): string | Htmlable
    {
        return 'Profil Administrator';
    }

    public static function getLabel(): string
    {
        return 'Profil Admin';
    }

    public function getAdminData(): array
    {
        $user = $this->getUser();
        $parts = preg_split('/\s+/', trim($user->name));
        $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

        $schoolSetting = SchoolSetting::current();

        $stats = [
            'total_teachers' => User::query()->where('role', UserRole::Teacher->value)->count(),
            'total_students' => User::query()->where('role', UserRole::Student->value)->count(),
            'total_classes' => Classroom::query()->count(),
            'total_subjects' => Subject::query()->count(),
            'total_materials' => Material::query()->count(),
            'total_quizzes' => Quiz::query()->count(),
            'total_assignments' => Assignment::query()->count(),
        ];

        return [
            'user' => $user,
            'initials' => $initials ?: 'AD',
            'schoolSetting' => $schoolSetting,
            'stats' => $stats,
        ];
    }
}

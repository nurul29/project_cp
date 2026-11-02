<?php

namespace App\Filament\Teacher\Pages\Auth;

use App\Models\Assignment;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\SchoolSetting;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Contracts\Support\Htmlable;

class TeacherEditProfile extends BaseEditProfile
{
    public function getView(): string
    {
        return 'filament.teacher.pages.auth.teacher-edit-profile';
    }

    public function getTitle(): string | Htmlable
    {
        return 'Profil & Penugasan Guru';
    }

    public static function getLabel(): string
    {
        return 'Profil Guru';
    }

    public function getTeacherData(): array
    {
        $user = $this->getUser();
        $user->loadMissing([
            'classroomsAsHomeroomTeacher',
            'teachings.subject',
            'teachings.classroom',
        ]);

        $parts = preg_split('/\s+/', trim($user->name));
        $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

        $teachings = $user->teachings;

        // Kelompokkan penugasan mengajar per mata pelajaran
        $teachingGroups = $teachings
            ->groupBy('subject_id')
            ->map(function ($items) {
                $first = $items->first();
                $subject = $first->subject;

                $classrooms = $items->map(fn ($item) => $item->classroom)
                    ->filter()
                    ->unique('id')
                    ->sortBy(function ($c) {
                        return $c->name;
                    })
                    ->values();

                return [
                    'subject' => $subject,
                    'classrooms' => $classrooms,
                ];
            })
            ->values();

        $allClassrooms = $teachings->map(fn ($t) => $t->classroom)->filter()->unique('id')->values();

        $homeroomClassrooms = $user->classroomsAsHomeroomTeacher;

        $stats = [
            'total_classes' => $allClassrooms->count(),
            'total_subjects' => $teachingGroups->count(),
            'materials_count' => Material::query()->where('created_by', $user->id)->count(),
            'quizzes_count' => Quiz::query()->where('created_by', $user->id)->count(),
            'assignments_count' => Assignment::query()->where('created_by', $user->id)->count(),
        ];

        return [
            'user' => $user,
            'initials' => $initials ?: 'GR',
            'schoolSetting' => SchoolSetting::current(),
            'homeroomClassrooms' => $homeroomClassrooms,
            'teachingGroups' => $teachingGroups,
            'allClassrooms' => $allClassrooms,
            'stats' => $stats,
        ];
    }
}

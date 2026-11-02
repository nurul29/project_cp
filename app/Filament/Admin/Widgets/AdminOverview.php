<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\AccountRequestStatus;
use App\Filament\Admin\Resources\AccountRequestResource;
use App\Filament\Admin\Resources\ActivityLogResource;
use App\Filament\Admin\Resources\ClassroomResource;
use App\Filament\Admin\Resources\SubjectResource;
use App\Filament\Admin\Resources\UserResource;
use App\Models\AccountRequest;
use App\Models\ActivityLog;
use App\Models\Classroom;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\User;
use Filament\Widgets\Widget;

class AdminOverview extends Widget
{
    protected static string $view = 'filament.admin.widgets.admin-overview';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'settings' => SchoolSetting::current(),
            'pendingCount' => AccountRequest::query()
                ->where('status', AccountRequestStatus::Pending->value)
                ->count(),
            'activeUsersCount' => User::query()->where('is_active', true)->count(),
            'totalUsersCount' => User::query()->count(),
            'activeClassesCount' => Classroom::query()->where('is_active', true)->count(),
            'subjectsCount' => Subject::query()->count(),
            'requests' => AccountRequest::query()
                ->where('status', AccountRequestStatus::Pending->value)
                ->latest()
                ->limit(3)
                ->get(),
            'activityLogs' => ActivityLog::query()
                ->with('actor:id,name')
                ->latest()
                ->limit(4)
                ->get(),
            'requestsUrl' => AccountRequestResource::getUrl(),
            'activityUrl' => ActivityLogResource::getUrl(),
            'usersUrl' => UserResource::getUrl(),
            'classroomsUrl' => ClassroomResource::getUrl(),
            'subjectsUrl' => SubjectResource::getUrl(),
        ];
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $sharedUser = $user?->loadMissing('classroom:id,name');

        if ($user?->isTeacher()) {
            $sharedUser->loadMissing([
                'teachings:id,teacher_id,subject_id,classroom_id',
                'teachings.subject:id,name',
                'teachings.classroom:id,name,grade_level',
            ]);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $sharedUser,
            ],
            'subjectsCount' => fn () => Subject::count(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}

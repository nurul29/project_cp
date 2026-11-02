<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\AccountRequest;
use App\Models\Classroom;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\Teaching;
use App\Models\User;
use App\Observers\AdminActivityObserver;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\Submission;
use App\Policies\AssignmentPolicy;
use App\Policies\MaterialPolicy;
use App\Policies\QuizPolicy;
use App\Policies\SubmissionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([User::class, Classroom::class, Subject::class, Teaching::class, AccountRequest::class, SchoolSetting::class] as $model) {
            $model::observe(AdminActivityObserver::class);
        }

        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(Material::class, MaterialPolicy::class);
        Gate::policy(Quiz::class, QuizPolicy::class);
        Gate::policy(Submission::class, SubmissionPolicy::class);
    }
}

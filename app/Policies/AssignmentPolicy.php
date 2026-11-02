<?php

namespace App\Policies;

use App\Enums\AssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher() || $user->isStudent();
    }

    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStudent()) {
            return $assignment->classroom_id === $user->classroom_id;
        }

        return $user->isTeacher() && $user->teachings()
            ->where('subject_id', $assignment->subject_id)
            ->where('classroom_id', $assignment->classroom_id)
            ->exists();
    }

    public function submit(User $user, Assignment $assignment): bool
    {
        if (! $user->isStudent() || $assignment->classroom_id !== $user->classroom_id) {
            return false;
        }

        if (
            $assignment->status !== AssignmentStatus::Active
            || ($assignment->due_at && $assignment->due_at->isPast())
        ) {
            return false;
        }

        $submission = $assignment->relationLoaded('submissions')
            ? $assignment->submissions->first()
            : $assignment->submissions()
                ->where('student_id', $user->id)
                ->first();

        return $submission?->status !== SubmissionStatus::Graded;
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && $user->teachings()
            ->where('subject_id', $assignment->subject_id)
            ->where('classroom_id', $assignment->classroom_id)
            ->exists());
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }
}

<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher() || $user->isStudent();
    }

    public function view(User $user, Submission $submission): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStudent()) {
            return $submission->student_id === $user->id;
        }

        return $this->canTeachSubmission($user, $submission);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher() || $user->isStudent();
    }

    public function update(User $user, Submission $submission): bool
    {
        return $this->grade($user, $submission);
    }

    public function grade(User $user, Submission $submission): bool
    {
        return $user->isAdmin() || $this->canTeachSubmission($user, $submission);
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $user->isAdmin() || ($user->isStudent() && $submission->student_id === $user->id);
    }

    private function canTeachSubmission(User $user, Submission $submission): bool
    {
        if (! $user->isTeacher()) {
            return false;
        }

        $assignment = $submission->assignment;

        return $user->teachings()
            ->where('subject_id', $assignment->subject_id)
            ->where('classroom_id', $assignment->classroom_id)
            ->exists();
    }
}

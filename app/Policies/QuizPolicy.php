<?php

namespace App\Policies;

use App\Enums\PublicationStatus;
use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher() || $user->isStudent();
    }

    public function view(User $user, Quiz $quiz): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStudent()) {
            return ($quiz->classroom_id === null || $quiz->classroom_id === $user->classroom_id)
                && $quiz->status === PublicationStatus::Published;
        }

        return $user->isTeacher() && ($quiz->created_by === $user->id || $this->canTeach($user, $quiz));
    }

    public function finish(User $user, Quiz $quiz): bool
    {
        return $user->isStudent()
            && ($quiz->classroom_id === null || $quiz->classroom_id === $user->classroom_id)
            && $quiz->status === PublicationStatus::Published;
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && ($quiz->created_by === $user->id || $this->canTeach($user, $quiz)));
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }

    private function canTeach(User $user, Quiz $quiz): bool
    {
        return $user->teachings()
            ->where('subject_id', $quiz->subject_id)
            ->where('classroom_id', $quiz->classroom_id)
            ->exists();
    }
}

<?php

namespace App\Actions\Student;

use App\Enums\PublicationStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CalculateProgress
{
    /**
     * @return array{completed: int, total: int, percentage: int}
     */
    public function handle(User $student): array
    {
        $activeQuizzes = Quiz::query()
            ->where(fn (Builder $query) => $query
                ->whereNull('classroom_id')
                ->orWhere('classroom_id', $student->classroom_id))
            ->where('status', PublicationStatus::Published->value);

        $activeQuizIds = (clone $activeQuizzes)->select('id');
        $total = (clone $activeQuizzes)->count();

        $completed = QuizAttempt::query()
            ->where('student_id', $student->id)
            ->whereIn('quiz_id', $activeQuizIds)
            ->distinct()
            ->count('quiz_id');

        $completed = min($total, $completed);

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $total > 0 ? (int) round($completed / $total * 100) : 0,
        ];
    }
}

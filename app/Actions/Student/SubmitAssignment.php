<?php

namespace App\Actions\Student;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\PortfolioItem;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\ValidationException;

class SubmitAssignment
{
    public function handle(Assignment $assignment, User $student, string $answer): Submission
    {
        return DB::transaction(function () use ($assignment, $student, $answer): Submission {
            $assignment = Assignment::query()
                ->lockForUpdate()
                ->findOrFail($assignment->id);

            if ($assignment->status->value !== 'active' || ($assignment->due_at && $assignment->due_at->isPast())) {
                throw ValidationException::withMessages([
                    'answer' => 'Batas pengumpulan tugas ini telah berakhir.',
                ]);
            }

            $submission = Submission::query()
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if ($submission?->status === SubmissionStatus::Graded) {
                throw ValidationException::withMessages([
                    'answer' => 'Tugas yang sudah dinilai tidak dapat dikirim ulang.',
                ]);
            }

            if (! $submission) {
                $submission = new Submission([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                ]);
            }

            $submission->fill([
                'answer' => $answer,
                'status' => SubmissionStatus::Pending,
                'score' => null,
                'feedback' => null,
                'submitted_at' => now(),
                'graded_at' => null,
            ]);
            $submission->save();

            PortfolioItem::query()->updateOrCreate(
                ['submission_id' => $submission->id],
                [
                    'student_id' => $student->id,
                    'subject_id' => $assignment->subject_id,
                    'title' => $assignment->title,
                    'type' => 'worksheet',
                    'file_path' => null,
                    'status' => SubmissionStatus::Pending->value,
                ],
            );

            return $submission;
        });
    }
}

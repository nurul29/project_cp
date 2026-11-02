<?php

namespace App\Http\Resources;

use App\Enums\PublicationStatus;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quiz */
class QuizResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attempt = $this->attempts->sortByDesc('id')->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status instanceof PublicationStatus
                ? $this->status->value
                : $this->status,
            'questionCount' => $this->questions_count ?? $this->questions->count(),
            'attempt' => $attempt ? [
                'score' => $attempt->score,
                'total' => $attempt->total,
                'finishedAt' => $attempt->finished_at?->toIso8601String(),
            ] : null,
            'subject' => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
                'icon' => $this->subject->icon,
                'tone' => $this->subject->tone,
            ] : null,
            'hasMaterial' => $this->material_id !== null,
            'material' => $this->whenLoaded('material', fn () => $this->material ? [
                'id' => $this->material->id,
                'title' => $this->material->title,
                'href' => route('siswa.library.show', $this->material, false),
            ] : null),
            'questions' => $this->whenLoaded('questions', fn () => $this->questions
                ->map(fn ($question) => [
                    'id' => $question->id,
                    'body' => $question->body,
                    'options' => $question->options,
                    'position' => $question->position,
                    ...($attempt ? [
                        'correctIndex' => $question->correct_index,
                        'explanation' => $question->explanation,
                    ] : []),
                ])),
            'href' => route('siswa.quizzes.show', $this->resource, false),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Assignment */
class AssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $submission = $this->submissions->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'instructions' => $this->instructions,
            'dueAt' => $this->due_at?->toIso8601String(),
            'status' => $submission?->status->value ?? $this->status->value,
            'score' => $submission?->score,
            'answer' => $submission?->answer,
            'feedback' => $submission?->feedback,
            'submittedAt' => $submission?->submitted_at?->toIso8601String(),
            'canSubmit' => $request->user()?->can('submit', $this->resource) ?? false,
            'subject' => $this->subject?->name ?? 'Tugas',
            'icon' => $this->subject?->icon ?? '📝',
            'tone' => $this->subject?->tone ?? 'teal',
            'href' => route('siswa.worksheets.show', $this->resource, false),
        ];
    }
}

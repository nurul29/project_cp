<?php

namespace App\Http\Resources;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Material */
class MaterialResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type->value,
            'url' => $this->url,
            'videoProvider' => $this->video_provider?->value,
            'fileUrl' => $this->file_path ? Storage::url($this->file_path) : null,
            'coverText' => $this->cover_text,
            'tone' => $this->tone ?? $this->subject?->tone ?? 'teal',
            'description' => $this->description,
            'quizzes' => $this->whenLoaded('quizzes', fn () => $this->quizzes->map(fn ($quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'href' => route('siswa.quizzes.show', $quiz, false),
            ])->values()),
            'subject' => $this->category_display,
            'genre' => $this->genre,
            'author' => $this->author,
            'collectionCategory' => $this->collection_category ?? ($this->type?->value === 'reading' ? 'general' : 'learning'),
            'subjectSlug' => $this->subject?->slug,
            'icon' => $this->subject?->icon ?? ($this->collection_category === 'general' ? '📖' : '📚'),
            'href' => route('siswa.library.show', $this->resource, false),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teaching extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'classroom_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (Teaching $teaching): void {
            $conflict = static::where('subject_id', $teaching->subject_id)
                ->where('classroom_id', $teaching->classroom_id)
                ->when($teaching->id, fn ($q) => $q->where('id', '!=', $teaching->id))
                ->where('teacher_id', '!=', $teaching->teacher_id)
                ->with('teacher')
                ->first();

            if ($conflict) {
                $teacherName = $conflict->teacher?->name ?? 'guru lain';
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'classroom_id' => "Kelas ini sudah diampu oleh {$teacherName} untuk mata pelajaran ini. Satu kelas hanya dapat diampu oleh satu guru.",
                ]);
            }
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}

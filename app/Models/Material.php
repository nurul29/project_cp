<?php

namespace App\Models;

use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Enums\VideoProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Material extends Model
{
    use HasFactory;

    public const GENRES = [
        'Fiksi & Cerita Pendek' => 'Fiksi & Cerita Pendek',
        'Novel & Sastra Remaja' => 'Novel & Sastra Remaja',
        'Cerita Rakyat & Dongeng' => 'Cerita Rakyat & Dongeng',
        'Sains & Teknologi Populer' => 'Sains & Teknologi Populer',
        'Sejarah, Budaya & Biografi' => 'Sejarah, Budaya & Biografi',
        'Ensiklopedia & Pengetahuan Umum' => 'Ensiklopedia & Pengetahuan Umum',
        'Pengembangan Diri & Motivasi' => 'Pengembangan Diri & Motivasi',
        'Puisi, Pantun & Drama' => 'Puisi, Pantun & Drama',
        'Komik Edukasi & Cerita Bergambar' => 'Komik Edukasi & Cerita Bergambar',
        'Artikel & Literasi Umum' => 'Artikel & Literasi Umum',
    ];

    protected static function booted(): void
    {
        static::saving(function (Material $material): void {
            // Sinkronisasi kategori dan format jika belum diset
            if (blank($material->collection_category)) {
                $typeVal = $material->type instanceof MaterialType ? $material->type->value : (string) $material->type;
                if ($typeVal === 'reading' || filled($material->genre)) {
                    $material->collection_category = 'general';
                } else {
                    $material->collection_category = 'learning';
                }
            }

            if (blank($material->content_format)) {
                $typeVal = $material->type instanceof MaterialType ? $material->type->value : (string) $material->type;
                $material->content_format = ($typeVal === 'video') ? 'video' : 'pdf';
            }

            if (blank($material->status)) {
                $material->status = PublicationStatus::Published;
            }

            if ($material->collection_category === 'general') {
                // Bacaan Umum: bersihkan kolom yang tidak relevan
                $material->subject_id = null;
                $material->classroom_id = null;
                $material->target_grade = null;
                $material->target_classroom_ids = null;
                $material->url = null;
                $material->video_provider = null;
                $material->content_format = 'pdf'; // Bacaan umum selalu berkas PDF
                $material->type = MaterialType::Reading;

                if (filled($material->genre) && blank($material->cover_text)) {
                    $material->cover_text = $material->genre;
                }
            } else {
                // Materi Pembelajaran: bersihkan data bacaan umum
                $material->genre = null;
                $material->author = null;

                if ($material->content_format === 'video') {
                    $material->type = MaterialType::Video;
                    $material->file_path = null;
                } else {
                    $material->content_format = 'pdf';
                    $material->type = MaterialType::Module;
                    $material->url = null;
                    $material->video_provider = null;
                }
            }
        });

        static::deleting(function (Material $material): void {
            if ($material->quizzes()->where('status', PublicationStatus::Published->value)->exists()) {
                throw ValidationException::withMessages([
                    'record' => 'Materi tidak dapat dihapus selama masih terhubung dengan kuis yang aktif.',
                ]);
            }
        });
    }

    protected $fillable = [
        'subject_id',
        'classroom_id',
        'target_classroom_ids',
        'target_grade',
        'title',
        'collection_category',
        'content_format',
        'author',
        'type',
        'genre',
        'file_path',
        'url',
        'video_provider',
        'size_bytes',
        'status',
        'cover_text',
        'tone',
        'description',
        'created_by',
    ];

    protected $casts = [
        'type' => MaterialType::class,
        'status' => PublicationStatus::class,
        'video_provider' => VideoProvider::class,
        'target_classroom_ids' => 'array',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function getCategoryDisplayAttribute(): string
    {
        if ($this->collection_category === 'general' || filled($this->genre)) {
            return $this->genre ?: 'Bacaan Umum';
        }

        return $this->subject?->name ?? 'Materi Umum';
    }

    public function getTargetClassDisplayAttribute(): string
    {
        if ($this->collection_category === 'general') {
            return 'Semua Siswa';
        }

        if (filled($this->target_grade)) {
            return 'Kelas ' . $this->target_grade;
        }

        if (! empty($this->target_classroom_ids)) {
            $names = Classroom::whereIn('id', (array) $this->target_classroom_ids)->pluck('name')->all();
            if (! empty($names)) {
                return implode(', ', $names);
            }
        }

        if ($this->classroom) {
            return $this->classroom->name;
        }

        return 'Semua Kelas';
    }

    public function scopeVisibleToStudent(Builder $query, User $student): Builder
    {
        $studentGrade = $student->classroom?->grade_level;

        return $query
            ->where('status', PublicationStatus::Published->value)
            ->where(function (Builder $q) use ($student, $studentGrade) {
                // Bacaan umum dapat diakses seluruh siswa
                $q->where('collection_category', 'general')
                    ->orWhere('type', MaterialType::Reading->value)
                    ->orWhere(function (Builder $sub) {
                        $sub->whereNull('classroom_id')
                            ->whereNull('target_grade')
                            ->where(function ($sub2) {
                                $sub2->whereNull('target_classroom_ids')
                                    ->orWhere('target_classroom_ids', '[]')
                                    ->orWhere('target_classroom_ids', '');
                            });
                    });

                if ($student->classroom_id) {
                    $q->orWhere('classroom_id', $student->classroom_id)
                        ->orWhereJsonContains('target_classroom_ids', (string) $student->classroom_id)
                        ->orWhereJsonContains('target_classroom_ids', (int) $student->classroom_id);
                }

                if ($studentGrade) {
                    $q->orWhere('target_grade', $studentGrade);
                }
            });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade_level',
        'school_year',
        'homeroom_teacher_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByRaw("
                CASE 
                    WHEN grade_level = 'VII' OR name LIKE 'VII %' OR name = 'VII' THEN 7
                    WHEN grade_level = 'VIII' OR name LIKE 'VIII %' OR name = 'VIII' THEN 8
                    WHEN grade_level = 'IX' OR name LIKE 'IX %' OR name = 'IX' THEN 9
                    ELSE 99
                END ASC
            ")
            ->orderBy('name', 'asc');
    }

    public function getDeletePreventionReasons(): array
    {
        $reasons = [];

        $studentsCount = $this->students()->count();
        if ($studentsCount > 0) {
            $reasons[] = "Masih terdapat {$studentsCount} siswa terdaftar di kelas ini. Harap pindahkan siswa ke kelas lain terlebih dahulu.";
        }

        if ($this->homeroom_teacher_id && $this->homeroomTeacher) {
            $reasons[] = "Masih memiliki wali kelas ({$this->homeroomTeacher->name}). Harap lepaskan status wali kelas terlebih dahulu.";
        }

        $teachingsCount = $this->teachings()->count();
        if ($teachingsCount > 0) {
            $reasons[] = "Masih terdapat {$teachingsCount} penugasan guru di kelas ini. Harap lepaskan penugasan guru terlebih dahulu.";
        }

        $historyCount = $this->materials()->count() + $this->assignments()->count() + $this->quizzes()->count();
        if ($historyCount > 0) {
            $reasons[] = "Kelas ini memiliki riwayat pembelajaran ({$historyCount} materi/tugas/kuis) yang harus dipertahankan. Gunakan opsi 'Nonaktifkan / Arsipkan' agar riwayat nilai tetap aman.";
        }

        return $reasons;
    }

    protected static function booted(): void
    {
        static::saving(function (Classroom $classroom): void {
            if (filled($classroom->name)) {
                $classroom->name = static::formatFullClassroomName(
                    $classroom->grade_level,
                    $classroom->name
                );

                $existing = Classroom::query()
                    ->whereRaw('LOWER(name) = ?', [strtolower($classroom->name)])
                    ->when($classroom->exists, fn ($q) => $q->where('id', '!=', $classroom->id))
                    ->exists();

                if ($existing) {
                    throw ValidationException::withMessages([
                        'data.name' => "Kelas '{$classroom->name}' sudah terdaftar dan tidak boleh sama.",
                        'name' => "Kelas '{$classroom->name}' sudah terdaftar dan tidak boleh sama.",
                    ]);
                }
            }
        });

        static::deleting(function (Classroom $classroom): void {
            $reasons = $classroom->getDeletePreventionReasons();
            if (! empty($reasons)) {
                throw ValidationException::withMessages([
                    'record' => implode(' ', $reasons),
                ]);
            }
        });
    }

    /**
     * Ekstrak hanya kode rombel tanpa prefix tingkat kelas (VII, VIII, IX).
     * Contoh: ("VII A", "VII") -> "A"
     */
    public static function extractRombelSuffix(?string $name, ?string $gradeLevel = null): string
    {
        $clean = trim((string) preg_replace('/\s+/', ' ', (string) $name));
        if ($clean === '') {
            return '';
        }

        // Hilangkan prefix tingkat kelas di awal (VII, VIII, IX) jika ada
        $pattern = '/^(?:kelas\s+)?(?:viii|vii|ix)\b[\s\-]*/i';
        if (filled($gradeLevel)) {
            $escaped = preg_quote(trim($gradeLevel), '/');
            $pattern = '/^(?:kelas\s+)?(?:' . $escaped . '|viii|vii|ix)\b[\s\-]*/i';
        }

        $suffix = preg_replace($pattern, '', $clean);

        return strtoupper(trim((string) $suffix));
    }

    /**
     * Gabungkan tingkat kelas dan kode rombel menjadi nama kelas baku.
     * Contoh: ("VII", "a") -> "VII A", ("VII", "VII A") -> "VII A"
     */
    public static function formatFullClassroomName(?string $gradeLevel, string $rawInput): string
    {
        $grade = strtoupper(trim((string) $gradeLevel));
        $suffix = static::extractRombelSuffix($rawInput, $grade);

        if ($grade === '') {
            return strtoupper(trim((string) preg_replace('/\s+/', ' ', $rawInput)));
        }

        if ($suffix === '') {
            return $grade;
        }

        return "{$grade} {$suffix}";
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function teachings(): HasMany
    {
        return $this->hasMany(Teaching::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}

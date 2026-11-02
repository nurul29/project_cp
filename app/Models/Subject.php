<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'icon',
        'description',
        'tone',
        'sort_order',
    ];

    protected $attributes = [
        'sort_order' => 0,
    ];

    public static function determineTone(string $nameOrCode): string
    {
        $tones = ['green', 'blue', 'orange', 'pink', 'lilac', 'yellow'];
        $idx = abs(crc32(strtolower(trim($nameOrCode)))) % count($tones);
        return $tones[$idx];
    }

    public static function determineIcon(string $name): string
    {
        $lower = strtolower($name);
        return match (true) {
            str_contains($lower, 'matematika') || str_contains($lower, 'hitung') => '📐',
            str_contains($lower, 'ipa') || str_contains($lower, 'sains') || str_contains($lower, 'alam') || str_contains($lower, 'fisika') || str_contains($lower, 'biologi') || str_contains($lower, 'kimia') => '🔬',
            str_contains($lower, 'ips') || str_contains($lower, 'geografi') || str_contains($lower, 'sejarah') || str_contains($lower, 'sosial') => '🌍',
            str_contains($lower, 'indonesia') || str_contains($lower, 'bahasa') || str_contains($lower, 'sastra') => '📖',
            str_contains($lower, 'inggris') || str_contains($lower, 'english') => '🌐',
            str_contains($lower, 'agama') || str_contains($lower, 'islam') || str_contains($lower, 'kristen') || str_contains($lower, 'katolik') || str_contains($lower, 'hindu') || str_contains($lower, 'buddha') => '🕌',
            str_contains($lower, 'seni') || str_contains($lower, 'budaya') || str_contains($lower, 'musik') || str_contains($lower, 'rupa') => '🎨',
            str_contains($lower, 'pjok') || str_contains($lower, 'olahraga') || str_contains($lower, 'penjas') => '⚽',
            str_contains($lower, 'informatika') || str_contains($lower, 'tik') || str_contains($lower, 'komputer') => '💻',
            str_contains($lower, 'pkn') || str_contains($lower, 'pancasila') || str_contains($lower, 'kewarganegaraan') => '🇮🇩',
            default => '📚',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (Subject $subject): void {
            if (blank($subject->icon) || $subject->icon === '📚') {
                $subject->icon = self::determineIcon($subject->name ?? '');
            }
            if (blank($subject->tone) || ! in_array($subject->tone, ['green', 'blue', 'orange', 'pink', 'lilac', 'yellow'])) {
                $subject->tone = self::determineTone($subject->name ?? $subject->code ?? 'subject');
            }
            if (blank($subject->slug) && filled($subject->name)) {
                $subject->slug = \Illuminate\Support\Str::slug($subject->name);
            }
        });

        static::deleting(function (Subject $subject): void {
            if (
                $subject->teachings()->exists()
                || $subject->materials()->exists()
                || $subject->assignments()->exists()
                || $subject->quizzes()->exists()
                || $subject->portfolioItems()->exists()
            ) {
                throw ValidationException::withMessages([
                    'record' => 'Mata pelajaran tidak dapat dihapus selama masih digunakan oleh kelas, materi, tugas, atau kuis.',
                ]);
            }
        });
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

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class);
    }
}

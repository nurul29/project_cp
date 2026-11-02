<?php

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if (auth()->id() === $user->id) {
                throw ValidationException::withMessages([
                    'record' => 'Anda tidak dapat menghapus akun yang sedang Anda gunakan saat ini.',
                ]);
            }

            if ($user->isAdmin() && $user->is_active && User::query()
                ->where('role', UserRole::Admin->value)
                ->where('is_active', true)
                ->whereKeyNot($user->id)
                ->doesntExist()) {
                throw ValidationException::withMessages([
                    'record' => 'Akun admin aktif terakhir tidak dapat dihapus.',
                ]);
            }

            // Lepaskan peran wali kelas jika akun ini adalah wali kelas
            if ($user->classroomsAsHomeroomTeacher()->exists()) {
                $user->classroomsAsHomeroomTeacher()->update(['homeroom_teacher_id' => null]);
            }

            // Hapus penugasan mengajar guru
            if ($user->teachings()->exists()) {
                $user->teachings()->delete();
            }

            // Reassign kepemilikan materi, tugas, dan kuis ke admin aktif agar aset pembelajaran tidak hilang
            $fallbackAdminId = auth()->id() ?? User::query()
                ->where('role', UserRole::Admin->value)
                ->where('is_active', true)
                ->whereKeyNot($user->id)
                ->value('id');

            if ($fallbackAdminId) {
                Material::query()->where('created_by', $user->id)->update(['created_by' => $fallbackAdminId]);
                Quiz::query()->where('created_by', $user->id)->update(['created_by' => $fallbackAdminId]);
                Assignment::query()->where('created_by', $user->id)->update(['created_by' => $fallbackAdminId]);
            } else {
                Material::query()->where('created_by', $user->id)->delete();
                Quiz::query()->where('created_by', $user->id)->delete();
                Assignment::query()->where('created_by', $user->id)->delete();
            }

            // Bersihkan data aktivitas siswa jika akun siswa yang dihapus
            $user->submissions()->delete();
            $user->quizAttempts()->delete();
            $user->shelfItems()->delete();
            $user->readingHistories()->delete();
            $user->portfolioItems()->delete();
        });
    }

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'classroom_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'is_active' => 'boolean',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teachings(): HasMany
    {
        return $this->hasMany(Teaching::class, 'teacher_id');
    }

    public function classroomsAsHomeroomTeacher(): HasMany
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'student_id');
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class, 'student_id');
    }

    public function shelfItems(): HasMany
    {
        return $this->hasMany(ShelfItem::class);
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($panel->getId() === 'guru') {
            return $this->isTeacher() || $this->isAdmin();
        }

        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        return false;
    }
}

<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'answer',
        'file_path',
        'status',
        'score',
        'feedback',
        'submitted_at',
        'graded_at',
    ];

    protected $casts = [
        'status' => SubmissionStatus::class,
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'integer',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function portfolioItem(): HasOne
    {
        return $this->hasOne(PortfolioItem::class);
    }
}

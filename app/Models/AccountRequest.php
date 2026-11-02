<?php

namespace App\Models;

use App\Enums\AccountRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountRequest extends Model
{
    protected $fillable = [
        'name',
        'username',
        'email',
        'message',
        'status',
        'handled_by',
        'handled_at',
        'admin_notes',
    ];

    protected $casts = [
        'status' => AccountRequestStatus::class,
        'handled_at' => 'datetime',
    ];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}

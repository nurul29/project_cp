<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'school_name',
        'school_year',
        'semester',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'school_name' => 'SMP Negeri 16 Depok',
                'school_year' => '2026/2027',
                'semester' => 'Ganjil',
            ],
        );
    }
}

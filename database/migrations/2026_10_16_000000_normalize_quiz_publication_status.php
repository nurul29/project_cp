<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('quizzes')
            ->where('status', 'active')
            ->update(['status' => 'published']);
    }

    public function down(): void
    {
        DB::table('quizzes')
            ->where('status', 'published')
            ->update(['status' => 'active']);
    }
};

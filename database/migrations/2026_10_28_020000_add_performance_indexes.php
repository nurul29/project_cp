<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'is_active']);
            $table->index('classroom_id');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->index(['is_active', 'grade_level']);
            $table->index('school_year');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->index(['status', 'collection_category']);
            $table->index('created_by');
            $table->index('subject_id');
            $table->index('classroom_id');
            $table->index('type');
        });

        Schema::table('teachings', function (Blueprint $table) {
            $table->index('teacher_id');
            $table->index('subject_id');
            $table->index('classroom_id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->index(['status', 'classroom_id']);
            $table->index('material_id');
            $table->index('teacher_id');
            $table->index('subject_id');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'quiz_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'quiz_id']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['subject_id']);
            $table->dropIndex(['teacher_id']);
            $table->dropIndex(['material_id']);
            $table->dropIndex(['status', 'classroom_id']);
        });

        Schema::table('teachings', function (Blueprint $table) {
            $table->dropIndex(['classroom_id']);
            $table->dropIndex(['subject_id']);
            $table->dropIndex(['teacher_id']);
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['classroom_id']);
            $table->dropIndex(['subject_id']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status', 'collection_category']);
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropIndex(['school_year']);
            $table->dropIndex(['is_active', 'grade_level']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['classroom_id']);
            $table->dropIndex(['role', 'is_active']);
        });
    }
};

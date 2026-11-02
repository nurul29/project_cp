<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('video_provider')->nullable()->after('url');
            $table->unsignedBigInteger('size_bytes')->nullable()->after('video_provider');
            $table->string('status')->default('published')->after('size_bytes');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('material_id')->nullable()->after('classroom_id')->constrained('materials')->nullOnDelete();
            $table->text('instructions')->nullable()->after('title');
            $table->unsignedInteger('revision')->default(1)->after('status');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->text('explanation')->nullable()->after('correct_index');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->unsignedInteger('revision')->default(1)->after('student_id');
            $table->json('answers')->nullable()->after('revision');
        });

        Schema::create('shelf_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'material_id']);
        });

        Schema::create('reading_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->timestamp('last_read_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_histories');
        Schema::dropIfExists('shelf_items');

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['revision', 'answers']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropColumn(['material_id', 'instructions', 'revision']);
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['video_provider', 'size_bytes', 'status']);
        });
    }
};

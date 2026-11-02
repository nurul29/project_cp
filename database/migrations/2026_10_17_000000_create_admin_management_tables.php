<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('homeroom_teacher_id')->nullable()->after('school_year')
                ->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('homeroom_teacher_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('name');
        });

        Schema::create('account_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['event', 'created_at']);
        });

        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('SMP Negeri 16 Depok');
            $table->string('school_year')->default('2026/2027');
            $table->string('semester')->default('Ganjil');
            $table->timestamps();
        });

        DB::table('school_settings')->insert([
            'id' => 1,
            'school_name' => 'SMP Negeri 16 Depok',
            'school_year' => '2026/2027',
            'semester' => 'Ganjil',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('account_requests');

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('homeroom_teacher_id');
            $table->dropColumn('is_active');
        });
    }
};

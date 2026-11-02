<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (! Schema::hasColumn('materials', 'collection_category')) {
                $table->string('collection_category')->default('learning')->after('title');
            }
            if (! Schema::hasColumn('materials', 'content_format')) {
                $table->string('content_format')->default('pdf')->after('collection_category');
            }
            if (! Schema::hasColumn('materials', 'author')) {
                $table->string('author')->nullable()->after('genre');
            }
            if (! Schema::hasColumn('materials', 'target_classroom_ids')) {
                $table->json('target_classroom_ids')->nullable()->after('classroom_id');
            }
            $table->foreignId('subject_id')->nullable()->change();
        });

        // Backfill existing data
        DB::table('materials')->where('type', 'video')->update([
            'collection_category' => 'learning',
            'content_format' => 'video',
        ]);

        DB::table('materials')->where('type', 'reading')->update([
            'collection_category' => 'general',
            'content_format' => 'pdf',
        ]);

        DB::table('materials')->where('type', 'book')->whereNotNull('genre')->update([
            'collection_category' => 'general',
            'content_format' => 'pdf',
        ]);

        DB::table('materials')->where('type', 'book')->whereNull('genre')->update([
            'collection_category' => 'learning',
            'content_format' => 'pdf',
        ]);

        DB::table('materials')->where('type', 'module')->update([
            'collection_category' => 'learning',
            'content_format' => 'pdf',
        ]);
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'target_classroom_ids')) {
                $table->dropColumn('target_classroom_ids');
            }
            if (Schema::hasColumn('materials', 'author')) {
                $table->dropColumn('author');
            }
            if (Schema::hasColumn('materials', 'content_format')) {
                $table->dropColumn('content_format');
            }
            if (Schema::hasColumn('materials', 'collection_category')) {
                $table->dropColumn('collection_category');
            }
        });
    }
};

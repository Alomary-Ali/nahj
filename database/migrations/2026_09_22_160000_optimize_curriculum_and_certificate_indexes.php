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
        Schema::table('lessons', function (Blueprint $table) {
            $table->index('slug');
            $table->index(['module_id', 'slug']);
        });

        Schema::table('course_reviews', function (Blueprint $table) {
            $table->index(['course_id', 'is_published']);
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->unique(['user_id', 'course_id'], 'certificates_user_id_course_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropUnique('certificates_user_id_course_id_unique');
        });

        Schema::table('course_reviews', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'is_published']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex(['module_id', 'slug']);
            $table->dropIndex(['slug']);
        });
    }
};

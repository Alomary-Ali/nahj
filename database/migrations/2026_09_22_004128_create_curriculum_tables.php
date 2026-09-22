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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('headline')->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('level')->default('all_levels'); // beginner, intermediate, advanced, all_levels
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_published']);
            $table->index(['instructor_id', 'is_published']);
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('headline')->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('preview_video_url')->nullable();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('specialization_id')->nullable()->constrained('specializations')->nullOnDelete();
            $table->string('level')->default('beginner'); // beginner, intermediate, advanced, all_levels
            $table->string('language')->default('ar');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('duration_hours')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_published']);
            $table->index(['instructor_id', 'is_published']);
            $table->index(['specialization_id', 'is_published']);
        });

        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'sort_order']);
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('type')->default('video'); // video, article, quiz
            $table->integer('duration_seconds')->default(0);
            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['module_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('specializations');
        Schema::dropIfExists('categories');
    }
};

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
        Schema::create('discussion_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->integer('upvotes_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'is_pinned']);
            $table->index(['course_id', 'lesson_id']);
            $table->index(['course_id', 'created_at']);
        });

        Schema::create('discussion_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('discussion_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->integer('upvotes_count')->default(0);
            $table->boolean('is_instructor_endorsed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('thread_id');
            $table->index(['thread_id', 'created_at']);
        });

        Schema::create('discussion_upvotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('upvotable_type'); // thread, reply
            $table->unsignedBigInteger('upvotable_id');
            $table->timestamps();

            $table->unique(['user_id', 'upvotable_type', 'upvotable_id']);
            $table->index(['upvotable_type', 'upvotable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_upvotes');
        Schema::dropIfExists('discussion_replies');
        Schema::dropIfExists('discussion_threads');
    }
};

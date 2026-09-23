<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_subtitles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->string('language', 10);
            $table->string('label', 100);
            $table->string('file_url');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['lesson_id', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_subtitles');
    }
};

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
        Schema::create('curriculum_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('school_section_id')->nullable()->constrained('school_sections')->onDelete('cascade');
            $table->foreignId('curriculum_id')->constrained('curricula') ->onDelete('cascade');
            $table->foreignId('grade_id')->constrained('grades') ->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects') ->onDelete('cascade');
            $table->tinyInteger('week_number')->nullable();
            $table->tinyInteger('semester_number')->nullable();
            $table->json('calendar_ids')->nullable();
            $table->json('data')->nullable();
           $table->text('notes')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_maps');
    }
};

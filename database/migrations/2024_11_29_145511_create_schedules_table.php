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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            // Foreign keys with better constraints
            $table->foreignId('copy_id')
                ->constrained('schedule_copies')
                ->onDelete('cascade');

            $table->foreignId('school_id')
                ->constrained('schools')
                ->onDelete('cascade');

            $table->foreignId('grade_id')
                ->constrained('grades')
                ->onDelete('cascade');

            $table->foreignId('classroom_id')
                ->constrained('classrooms')
                ->onDelete('cascade');

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->onDelete('cascade');

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->onDelete('cascade');

            // Schedule timing information
            $table->unsignedTinyInteger('day')
                ->nullable()
                ->comment('Day of the week (1-7)')
                ->check('day >= 1 AND day <= 7');

            $table->unsignedTinyInteger('period')
                ->nullable()
                ->comment('Period number in the day')
                ->check('period > 0');

            $table->unsignedTinyInteger('num')
                ->nullable()
                ->comment('Sequence number')
                ->check('num > 0');

            // Location and display information
            $table->string('name', 120)
                ->nullable()
                ->comment('Schedule name or identifier');

            $table->string('place', 120)
                ->nullable()
                ->comment('Physical location or classroom');

            $table->string('color_custom', 7)
                ->nullable()
                ->comment('Custom color for UI display (hex format: #RRGGBB)')
                ->check("color_custom REGEXP '^#[0-9A-Fa-f]{6}$'");

            // Status and metadata
            $table->boolean('active')
                ->default(true)
                ->comment('Whether this schedule is currently active');

            $table->text('notes')
                ->nullable()
                ->comment('Additional notes or comments');

            // Audit timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['school_id', 'grade_id', 'classroom_id']);
            $table->index(['school_id', 'day', 'period']);
            $table->index(['copy_id', 'active']);
            $table->index(['teacher_id', 'day']);

            // Unique constraints
            $table->unique(['school_id', 'classroom_id', 'day', 'period', 'copy_id'], 'unique_schedule_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};



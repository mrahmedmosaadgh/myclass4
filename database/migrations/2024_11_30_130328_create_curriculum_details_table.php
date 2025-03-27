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
        Schema::create('curriculum_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('curriculum_id')->constrained('curricula') ;
            // $table->foreignId('calendar_id')->nullable()->constrained('calendars')->onDelete('cascade');
            // $table->tinyInteger('week_number')->nullable();
            $table->tinyInteger('selected')->default(1);
            $table->string('topic_number');
            $table->string('topic_title');
            $table->string('lesson_number');
            $table->string('lesson_title');
            $table->integer('page_number')->nullable();
            $table->string('description')->nullable();




            $table->string('standard')->nullable();
            $table->string('strand')->nullable();
            $table->string('content')->nullable();
            $table->string('skill')->nullable();
            $table->text('activities')->nullable();
            $table->string('assignment')->nullable();
            $table->string('assessment')->nullable();
            $table->string('notes_admin')->nullable();
            $table->string('notes_teacher')->nullable();



           $table->json('data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_details');
    }
};

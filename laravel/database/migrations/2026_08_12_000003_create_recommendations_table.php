<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Persisted recommendation-engine output, refreshed by StudentObserver/InternshipObserver rather than recomputed per request. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->increments('recommendation_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('internship_id');
            $table->decimal('score', 6, 2)->default(0);
            $table->json('matched_reasons')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'internship_id']);
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('internship_id')->references('internship_id')->on('internships')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};

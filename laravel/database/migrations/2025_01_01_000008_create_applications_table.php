<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->increments('application_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('internship_id');
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['Pending', 'Accepted', 'Rejected', 'Withdrawn'])->default('Pending');
            $table->text('employer_feedback')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'internship_id']);
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('internship_id')->references('internship_id')->on('internships')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};

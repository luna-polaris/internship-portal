<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->increments('bookmark_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('internship_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['student_id', 'internship_id']);
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('internship_id')->references('internship_id')->on('internships')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};

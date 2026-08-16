<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->increments('student_id');
            $table->unsignedInteger('user_id')->unique();
            $table->string('matric_no', 20)->unique();
            $table->string('university', 100)->nullable();
            $table->string('faculty', 100)->nullable();
            $table->string('programme', 100)->nullable();
            $table->decimal('cgpa', 3, 2)->nullable();
            $table->year('graduation_year')->nullable();
            $table->string('resume')->nullable();

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

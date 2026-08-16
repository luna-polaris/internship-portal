<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->increments('interview_id');
            $table->unsignedInteger('application_id');
            $table->dateTime('scheduled_at');
            $table->enum('mode', ['Onsite', 'Online'])->default('Onsite');
            $table->string('location', 255)->nullable();
            $table->string('meeting_link', 255)->nullable();
            $table->enum('status', ['Scheduled', 'Rescheduled', 'Completed', 'Cancelled'])->default('Scheduled');
            $table->text('remarks')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('application_id')->references('application_id')->on('applications')->onDelete('cascade');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};

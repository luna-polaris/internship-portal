<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only audit trail for the Command pattern used in Module 4 — every
 * Schedule/Reschedule/Cancel/Complete action logs itself here, independent
 * of the interview row's current state.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->unsignedInteger('interview_id');
            $table->string('action', 30);
            $table->unsignedInteger('performed_by');
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('interview_id')->references('interview_id')->on('interviews')->onDelete('cascade');
            $table->foreign('performed_by')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_logs');
    }
};

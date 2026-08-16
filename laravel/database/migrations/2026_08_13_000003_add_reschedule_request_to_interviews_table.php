<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A student-proposed new time sits here until the employer/admin approves
 * or declines it — the interview's real `scheduled_at` never changes until
 * approval, so a pending request can't silently become the actual schedule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dateTime('requested_scheduled_at')->nullable()->after('remarks');
            $table->text('requested_reason')->nullable()->after('requested_scheduled_at');
            $table->unsignedInteger('requested_by')->nullable()->after('requested_reason');

            $table->foreign('requested_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropColumn(['requested_scheduled_at', 'requested_reason', 'requested_by']);
        });
    }
};

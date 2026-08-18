<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Named `user_notifications` so it never collides with Laravel's built-in DatabaseNotification table.
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->increments('notification_id');
            $table->unsignedInteger('user_id');
            $table->string('type', 50);              // e.g. evaluation.submitted
            $table->string('title', 150);
            $table->text('message');
            $table->string('link', 255)->nullable(); // where clicking the alert should go
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at'], 'user_notifications_user_read_index');
            $table->foreign('user_id', 'user_notifications_user_id_foreign')
                  ->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
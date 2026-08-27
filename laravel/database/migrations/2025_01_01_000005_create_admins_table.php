<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('admin_id');
            $table->unsignedInteger('user_id')->unique();
            // Dropped again by 2026_08_25_000001; kept here because this migration has already
            // run on existing databases and must not be rewritten after the fact.
            $table->enum('admin_level', ['Super Admin', 'Moderator'])->default('Moderator');

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};

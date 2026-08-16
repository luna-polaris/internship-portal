<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->json('skills')->nullable()->after('resume');
            $table->json('interests')->nullable()->after('skills');
            $table->json('preferred_locations')->nullable()->after('interests');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['skills', 'interests', 'preferred_locations']);
        });
    }
};

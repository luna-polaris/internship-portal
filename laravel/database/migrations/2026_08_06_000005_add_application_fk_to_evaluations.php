<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            // application_id was left unconstrained when evaluations was created; wiring the FK now.
            $table->foreign('application_id', 'evaluations_application_id_foreign')
                  ->references('application_id')->on('applications')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign('evaluations_application_id_foreign');
        });
    }
};
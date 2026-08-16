<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            // Left unconstrained when the evaluations table was created, because
            // the applications table belonged to another module. Wiring it now
            // ties each evaluation to the specific placement it describes.
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
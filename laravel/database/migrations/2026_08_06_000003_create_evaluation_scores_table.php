<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->increments('score_id');
            $table->unsignedInteger('evaluation_id');
            $table->unsignedInteger('criterion_id');
            $table->decimal('score', 5, 2);
            $table->string('remark', 255)->nullable();
            $table->timestamps();

            $table->unique(['evaluation_id', 'criterion_id'], 'evaluation_scores_evaluation_criterion_unique');

            $table->foreign('evaluation_id', 'evaluation_scores_evaluation_id_foreign')
                  ->references('evaluation_id')->on('evaluations')->cascadeOnDelete();
            // A scored criterion must be deactivated, not deleted.
            $table->foreign('criterion_id', 'evaluation_scores_criterion_id_foreign')
                  ->references('criterion_id')->on('evaluation_criteria')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_scores');
    }
};
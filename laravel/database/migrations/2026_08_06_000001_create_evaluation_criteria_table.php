<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->increments('criterion_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('category', ['Technical', 'Soft Skills', 'Professionalism', 'Other'])
                  ->default('Other');
            // Highest raw score an evaluator may give for this criterion (e.g. 5 => 1-5 scale)
            $table->unsignedTinyInteger('max_score')->default(5);
            // Contribution to the weighted total, in percent. Active criteria should sum to 100.
            $table->decimal('weight', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('created_by')->nullable();   // users.user_id of the Admin
            $table->timestamps();

            $table->unique('name', 'evaluation_criteria_name_unique');
            $table->index('is_active', 'evaluation_criteria_is_active_index');
            $table->foreign('created_by', 'evaluation_criteria_created_by_foreign')
                  ->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria');
    }
};
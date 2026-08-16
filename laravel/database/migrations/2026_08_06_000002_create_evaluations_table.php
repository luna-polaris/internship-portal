<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->increments('evaluation_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('employer_id');

            // Hook for the internship/application module owned by another team member.
            // Left nullable + unconstrained on purpose so this module migrates and runs
            // standalone. Once `applications` exists, add the FK in a follow-up migration.
            $table->unsignedInteger('application_id')->nullable();

            $table->enum('period', ['Midterm', 'Final'])->default('Final');
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('overall_comment')->nullable();

            // Weighted percentage (0-100), recalculated whenever scores change.
            $table->decimal('total_score', 5, 2)->nullable();

            $table->enum('status', ['Draft', 'Submitted', 'Approved', 'Rejected'])
                  ->default('Draft');
            $table->timestamp('submitted_at')->nullable();

            $table->unsignedInteger('reviewed_by')->nullable();  // users.user_id of the Admin
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_remark')->nullable();

            $table->timestamps();

            // One evaluation per student, per employer, per period.
            $table->unique(['student_id', 'employer_id', 'period'], 'evaluations_student_employer_period_unique');
            $table->index('status', 'evaluations_status_index');

            $table->foreign('student_id', 'evaluations_student_id_foreign')
                  ->references('student_id')->on('students')->cascadeOnDelete();
            $table->foreign('employer_id', 'evaluations_employer_id_foreign')
                  ->references('employer_id')->on('employers')->cascadeOnDelete();
            $table->foreign('reviewed_by', 'evaluations_reviewed_by_foreign')
                  ->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
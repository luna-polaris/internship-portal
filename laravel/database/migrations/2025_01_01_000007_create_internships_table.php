<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->increments('internship_id');
            $table->unsignedInteger('company_id');
            $table->string('title', 150);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('category', 100)->nullable();
            $table->enum('work_mode', ['Onsite', 'Remote', 'Hybrid'])->default('Onsite');
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->decimal('allowance', 8, 2)->nullable();
            $table->unsignedTinyInteger('duration_months')->nullable();
            $table->unsignedInteger('vacancies')->default(1);
            $table->date('application_deadline')->nullable();
            $table->enum('status', ['Draft', 'Published', 'Closed'])->default('Draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')
                ->references('company_id')->on('companies')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};

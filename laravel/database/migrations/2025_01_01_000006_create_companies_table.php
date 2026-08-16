<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('company_id');
            $table->unsignedInteger('employer_id')->unique();
            $table->string('company_name', 150);
            $table->string('registration_no', 50)->nullable();
            $table->string('industry', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('website', 150)->nullable();
            $table->string('company_email', 100)->nullable();
            $table->string('company_phone', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();

            $table->foreign('employer_id')
                ->references('employer_id')->on('employers')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('language', 3);
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('result');
            $table->json('answers');
            $table->timestamp('completed_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('language');
            $table->index('result');
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};

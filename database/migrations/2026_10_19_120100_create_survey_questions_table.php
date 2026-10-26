<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();

            $table->string('type', 20);                    // single|multi|rating|nps|text
            $table->string('prompt');
            $table->json('options')->nullable();           // choice types: ["Yes","No"]
            $table->json('config')->nullable();            // e.g. {"max":5} for rating
            $table->boolean('required')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['survey_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};

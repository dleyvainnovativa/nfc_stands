<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('survey_response_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained('survey_questions')->cascadeOnDelete();

            $table->text('value_text')->nullable();        // text answers
            $table->integer('value_number')->nullable();   // rating / nps
            $table->json('value_options')->nullable();     // choice selections (array)

            $table->timestamps();

            // Hot query: all answers for a question (stats).
            $table->index(['survey_question_id']);
            $table->index(['survey_response_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};

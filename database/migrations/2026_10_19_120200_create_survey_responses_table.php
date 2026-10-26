<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            // Which stand sent them here, if any (attribution).
            $table->foreignId('stand_id')->nullable()->constrained('stands')->nullOnDelete();

            $table->timestamp('submitted_at')->index();
            $table->string('device', 12)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['survey_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};

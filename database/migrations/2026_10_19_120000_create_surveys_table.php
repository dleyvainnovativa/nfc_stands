<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('code', 16)->unique();          // public /s/{code}
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft|active|closed
            $table->string('thank_you_message')->nullable();
            $table->unsignedBigInteger('responses_count')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};

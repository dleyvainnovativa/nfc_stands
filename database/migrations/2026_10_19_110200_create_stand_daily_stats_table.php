<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stand_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('stand_id')->constrained('stands')->cascadeOnDelete();

            $table->date('stat_date');
            $table->unsignedInteger('scan_count')->default(0);
            $table->unsignedInteger('unique_devices')->default(0); // approx (distinct ip_hash)

            $table->timestamps();

            // One row per stand per day — the upsert key.
            $table->unique(['stand_id', 'stat_date']);
            $table->index(['tenant_id', 'stat_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stand_daily_stats');
    }
};

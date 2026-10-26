<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_place_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('google_place_id')->constrained('google_places')->cascadeOnDelete();

            $table->date('stat_date');
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedInteger('user_rating_count')->nullable();

            $table->timestamps();

            // One snapshot per place per day — the trend series. We store OUR
            // recorded numbers over time (allowed), not Google's review content.
            $table->unique(['google_place_id', 'stat_date']);
            $table->index(['tenant_id', 'stat_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_place_daily_stats');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('stand_id')->constrained('stands')->cascadeOnDelete();

            $table->timestamp('occurred_at')->index();
            $table->string('device', 12)->nullable();   // mobile|tablet|desktop|bot
            $table->string('ip_hash', 64)->nullable();   // hashed, never raw IP (LFPDPPP)
            $table->string('referer')->nullable();
            $table->string('user_agent', 512)->nullable();

            // No updated_at needed; events are immutable.
            $table->timestamp('created_at')->nullable();

            // Hot query: "events for this stand in a date range".
            $table->index(['stand_id', 'occurred_at']);
            $table->index(['tenant_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_events');
    }
};

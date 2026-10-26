<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hub_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('stand_id')->constrained('stands')->cascadeOnDelete();

            // Snapshot of the link at click time, so analytics stay accurate even
            // if the hub's links are later edited or reordered.
            $table->unsignedInteger('link_index')->nullable();
            $table->string('label')->nullable();
            $table->string('url', 2048);

            $table->timestamp('occurred_at')->index();
            $table->string('device', 12)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['stand_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_link_clicks');
    }
};

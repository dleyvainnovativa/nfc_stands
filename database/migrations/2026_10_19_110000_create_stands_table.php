<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stands', function (Blueprint $table) {
            $table->id();

            // Nullable: a pre-generated blank tag has no tenant until claimed.
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();

            $table->string('code', 16)->unique();          // public /t/{code}
            $table->string('label')->nullable();           // "Table 4", "Entrance"
            $table->string('status', 20)->default('unassigned'); // unassigned|active|disabled

            // Routing
            $table->string('destination_type', 20)->default('direct'); // direct|hub
            $table->json('destination_config')->nullable();

            // Google (Tier 3) — carried now so the column is ready
            $table->string('place_id')->nullable();

            // Cheap denormalized counters, maintained by the nightly rollup
            $table->unsignedBigInteger('scans_total')->default(0);
            $table->timestamp('last_scan_at')->nullable();

            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stands');
    }
};

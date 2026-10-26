<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            $table->string('place_id');                    // Google place ID (stored indefinitely — allowed)
            $table->string('label')->nullable();           // our display name for it
            $table->string('name')->nullable();            // Google displayName (cached)

            $table->decimal('rating', 2, 1)->nullable();   // latest average
            $table->unsignedInteger('user_rating_count')->nullable();
            $table->string('maps_uri')->nullable();
            $table->json('reviews')->nullable();           // up to 5 latest (refreshed each sync)

            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_error')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'place_id']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_places');
    }
};

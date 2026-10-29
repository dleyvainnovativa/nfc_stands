<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();

            $table->unsignedInteger('link_index');
            $table->string('label')->nullable();
            $table->string('url', 2048)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->string('device', 12)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['page_id', 'link_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_link_clicks');
    }
};

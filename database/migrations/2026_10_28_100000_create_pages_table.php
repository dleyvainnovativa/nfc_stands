<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('code', 16)->unique();           // public URL /p/{code}

            $table->string('name');                         // internal + page title
            $table->string('title')->nullable();            // shown heading (falls back to name)
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();

            $table->string('primary_color', 9)->nullable();
            $table->string('secondary_color', 9)->nullable();
            $table->string('button_shape', 12)->default('rounded');  // rounded | pill | square
            $table->string('button_fill', 12)->default('solid');     // solid | outline
            $table->string('bg_style', 12)->default('gradient');     // gradient | plain

            $table->json('links')->nullable();              // [{label,url,icon}]
            $table->unsignedInteger('views')->default(0);

            // Admin-controlled visibility. Clients edit content but can't publish.
            $table->boolean('is_active')->default(false);

            $table->timestamps();
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};

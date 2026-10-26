<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Client brand applied to that tenant's public surveys / thank-you pages.
            $table->string('primary_color', 9)->nullable()->after('is_active');
            $table->string('secondary_color', 9)->nullable()->after('primary_color');
            $table->string('logo_path')->nullable()->after('secondary_color');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'secondary_color', 'logo_path']);
        });
    }
};

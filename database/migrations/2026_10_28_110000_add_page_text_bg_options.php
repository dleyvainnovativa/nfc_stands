<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Background colour used when bg_style = 'plain'.
            $table->string('bg_color', 9)->nullable()->after('bg_style');
            // Text colour mode: auto (contrast from bg) | light | dark.
            $table->string('text_mode', 8)->default('auto')->after('bg_color');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['bg_color', 'text_mode']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional per-page / per-survey Google Font. Stores the family name only
 * (e.g. "Poppins"); the font is loaded from Google's CSS API at render time,
 * never uploaded. Null falls back to Inter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('font_family', 64)->nullable()->after('text_mode');
        });
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('font_family', 64)->nullable()->after('return_label');
        });
    }

    public function down(): void
    {
        Schema::table('pages', fn (Blueprint $table) => $table->dropColumn('font_family'));
        Schema::table('surveys', fn (Blueprint $table) => $table->dropColumn('font_family'));
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            // Show one question per page (stepper) on the public survey.
            $table->boolean('paginated')->default(false)->after('thank_you_message');
            // Ask for contact details (name required, phone/email optional) up front.
            $table->boolean('collect_contact')->default(false)->after('paginated');
            // "Return" button on the thank-you page (social link / tenant site).
            $table->string('return_url', 2048)->nullable()->after('collect_contact');
            $table->string('return_label', 60)->nullable()->after('return_url');
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('stand_id');
            $table->string('contact_phone', 40)->nullable()->after('contact_name');
            $table->string('contact_email')->nullable()->after('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(['paginated', 'collect_contact', 'return_url', 'return_label']);
        });
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'contact_phone', 'contact_email']);
        });
    }
};

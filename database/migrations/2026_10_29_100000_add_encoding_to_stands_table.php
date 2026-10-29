<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Physical-encoding state for stands. The redirect/destination model already
 * lives in `stands`; this adds whether (and when) a physical NFC tag has been
 * written for a code — the inventory-control backbone the NFC writer app syncs
 * against. A code with encoded_at IS NULL is "pending" (needs writing).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stands', function (Blueprint $table) {
            $table->timestamp('encoded_at')->nullable()->after('claimed_at');
            $table->foreignId('encoded_by')->nullable()->after('encoded_at')
                ->constrained('users')->nullOnDelete();
            $table->string('encoded_kind', 8)->nullable()->after('encoded_by'); // url | wifi
            $table->index(['tenant_id', 'encoded_at']);
        });
    }

    public function down(): void
    {
        Schema::table('stands', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'encoded_at']);
            $table->dropConstrainedForeignId('encoded_by');
            $table->dropColumn(['encoded_at', 'encoded_kind']);
        });
    }
};

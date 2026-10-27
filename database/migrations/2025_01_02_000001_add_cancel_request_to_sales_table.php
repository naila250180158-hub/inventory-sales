<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Alur "customer mengajukan pembatalan, owner memutuskan"
            // cancel_status: null (tidak ada) / requested / rejected / approved
            $table->string('cancel_status', 20)->nullable()->index()->after('status');
            $table->string('cancel_reason', 255)->nullable()->after('cancel_status');
            $table->timestamp('cancel_requested_at')->nullable()->after('cancel_reason');
            $table->string('cancel_response', 255)->nullable()->after('cancel_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['cancel_status']);
            $table->dropColumn(['cancel_status', 'cancel_reason', 'cancel_requested_at', 'cancel_response']);
        });
    }
};

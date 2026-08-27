<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->timestamp('certificate_sent_at')->nullable()->after('scope_notes');
            $table->boolean('certificate_accessible')->default(false)->after('certificate_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['certificate_sent_at', 'certificate_accessible']);
        });
    }
};

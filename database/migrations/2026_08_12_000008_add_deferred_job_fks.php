<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Resolves the two FKs that were deferred until the work_orders table existed:
//   assets.created_from_job_id  → work_orders.id
//   inspection_records.job_id   → work_orders.id

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('created_from_job_id')
                  ->references('id')->on('work_orders')
                  ->nullOnDelete();
        });

        Schema::table('inspection_records', function (Blueprint $table) {
            $table->foreign('job_id')
                  ->references('id')->on('work_orders')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_records', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['created_from_job_id']);
        });
    }
};

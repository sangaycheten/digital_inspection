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
        Schema::table('job_technician_buildings', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->after('building_id');
        });
        // Backfill existing rows with the job's created_at
        \Illuminate\Support\Facades\DB::statement('
            UPDATE job_technician_buildings jtb
            JOIN work_orders wo ON wo.id = jtb.job_id
            SET jtb.created_at = wo.created_at
        ');
    }

    public function down(): void
    {
        Schema::table('job_technician_buildings', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });
    }
};

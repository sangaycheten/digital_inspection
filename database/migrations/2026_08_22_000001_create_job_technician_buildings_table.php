<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_technician_buildings', function (Blueprint $table) {
            $table->foreignUuid('job_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('building_id')->constrained()->cascadeOnDelete();
            $table->primary(['job_id', 'user_id', 'building_id']);
            $table->index(['job_id', 'user_id']);
        });

        // Seed existing jobs: cross-join each job's technicians × buildings
        $rows = DB::table('job_technicians')
            ->join('job_buildings', 'job_technicians.job_id', '=', 'job_buildings.job_id')
            ->select(
                'job_technicians.job_id',
                'job_technicians.technician_id as user_id',
                'job_buildings.building_id'
            )
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        if (!empty($rows)) {
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('job_technician_buildings')->insertOrIgnore($chunk);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_technician_buildings');
    }
};

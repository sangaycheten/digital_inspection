<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_buildings', function (Blueprint $table) {
            $table->foreignUuid('job_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignUuid('building_id')->constrained()->cascadeOnDelete();
            $table->primary(['job_id', 'building_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_buildings');
    }
};

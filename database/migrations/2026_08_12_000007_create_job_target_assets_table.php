<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_target_assets', function (Blueprint $table) {
            $table->foreignUuid('job_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignUuid('asset_id')->constrained()->cascadeOnDelete();
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->primary(['job_id', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_target_assets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installation_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignUuid('asset_id')->constrained()->restrictOnDelete();
            // installed | repaired | adjusted | connected
            $table->string('action');
            $table->text('material_notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('job_id');
            $table->index('asset_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_assets');
    }
};

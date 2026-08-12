<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('building_id')->nullable()->constrained()->restrictOnDelete();
            $table->text('zone')->nullable();

            $table->string('asset_code');
            // anchor_point | static_line | ladder | guardrail | walkway | other
            $table->string('asset_type');
            $table->uuid('group_id')->nullable();

            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_or_batch')->nullable();
            $table->string('rating')->nullable();
            $table->string('fixing_type')->nullable();

            // pass | fail | under_review | restricted_use | not_inspected | not_located | removed | replaced
            $table->string('current_status')->default('not_inspected');
            // FK to inspection_records added after that table is created (see next migration)
            $table->uuid('current_inspection_id')->nullable();

            $table->date('install_date')->nullable();
            // FK to jobs added when the jobs module migration runs
            $table->uuid('created_from_job_id')->nullable();
            $table->date('next_inspection_due_date')->nullable();

            $table->uuid('replaces_asset_id')->nullable();
            $table->uuid('replaced_by_asset_id')->nullable();

            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'asset_code']);
            $table->index('current_status');
            $table->index('group_id');
            $table->index('next_inspection_due_date');

            $table->foreign('replaces_asset_id')->references('id')->on('assets')->nullOnDelete();
            $table->foreign('replaced_by_asset_id')->references('id')->on('assets')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

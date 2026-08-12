<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained()->restrictOnDelete();
            // FK to jobs added when the jobs module migration runs
            $table->uuid('job_id');

            $table->date('inspection_date');
            $table->foreignUuid('technician_id')->constrained('users')->restrictOnDelete();

            // pass | fail | under_review | restricted_use | not_inspected | not_located
            $table->string('result');
            $table->text('condition')->nullable();
            $table->text('defect_description')->nullable();
            $table->text('reason_for_result')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('required_action')->nullable();

            // draft | approved
            $table->string('document_status')->default('draft');
            $table->boolean('is_current')->default(false);

            $table->uuid('previous_inspection_id')->nullable();
            $table->foreign('previous_inspection_id')->references('id')->on('inspection_records')->nullOnDelete();

            // Append-only: no updated_at
            $table->timestamp('created_at')->useCurrent();

            $table->index(['asset_id', 'inspection_date']);
            $table->index('job_id');
        });

        // Resolve the circular FK: assets.current_inspection_id → inspection_records.id
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('current_inspection_id')->references('id')->on('inspection_records')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Drop the circular FK on assets first
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['current_inspection_id']);
        });

        Schema::dropIfExists('inspection_records');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('client_id')->constrained()->restrictOnDelete();

            // first_inspection | re_inspection | installation | rectification | combined
            $table->string('work_type');
            // new | scheduled | in_progress | submitted_for_review | under_review
            // | approved | issued | rectification_required | closed
            $table->string('status')->default('new');

            $table->date('scheduled_date')->nullable();
            $table->text('scope_notes')->nullable();

            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_technicians', function (Blueprint $table) {
            $table->foreignUuid('job_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignUuid('technician_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['job_id', 'technician_id']);
            $table->index('technician_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_technicians');
    }
};

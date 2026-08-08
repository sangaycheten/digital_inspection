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
        Schema::create('master_lookups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('category', ['asset_type', 'defect_reason', 'recommendation']);
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['category', 'label']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_lookups');
    }
};

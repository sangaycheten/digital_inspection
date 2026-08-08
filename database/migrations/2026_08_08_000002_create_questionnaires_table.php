<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('key')->unique();
            $table->string('type'); // DataType enum value
            $table->foreignUuid('field_type_id')->nullable()->constrained('field_types')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};

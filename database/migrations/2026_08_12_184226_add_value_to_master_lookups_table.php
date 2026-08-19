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
        Schema::table('master_lookups', function (Blueprint $table) {
            // Slug stored in related records (e.g. assets.asset_type). Nullable for display-only categories.
            $table->string('value')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('master_lookups', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }
};

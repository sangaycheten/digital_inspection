<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_records', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('required_action');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_records', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};

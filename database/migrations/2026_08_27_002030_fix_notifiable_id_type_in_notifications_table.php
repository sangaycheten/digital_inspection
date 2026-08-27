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
        // notifiable_id must be a string to hold UUIDs
        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE notifications MODIFY notifiable_id VARCHAR(36) NOT NULL'
        );
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE notifications MODIFY notifiable_id BIGINT UNSIGNED NOT NULL'
        );
    }
};

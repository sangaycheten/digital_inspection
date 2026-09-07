<?php

use App\Models\MenuSequence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('role', 100);
            $table->string('key', 100);
            $table->unsignedSmallInteger('sequence');
            $table->unique(['role', 'key']);
        });

        // Seed default sequences for system-administrator
        $defaults = [
            ['role' => 'system-administrator', 'key' => 'dashboard',      'sequence' => 1],
            ['role' => 'system-administrator', 'key' => 'user-role',      'sequence' => 2],
            ['role' => 'system-administrator', 'key' => 'audit-log',      'sequence' => 3],
            ['role' => 'system-administrator', 'key' => 'master-data',    'sequence' => 4],
            ['role' => 'system-administrator', 'key' => 'questionnaires', 'sequence' => 5],
            ['role' => 'system-administrator', 'key' => 'jobs',           'sequence' => 6],
            ['role' => 'system-administrator', 'key' => 'assets',         'sequence' => 7],
            ['role' => 'system-administrator', 'key' => 'inspections',    'sequence' => 8],
        ];

        foreach ($defaults as $row) {
            MenuSequence::create($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_sequences');
    }
};

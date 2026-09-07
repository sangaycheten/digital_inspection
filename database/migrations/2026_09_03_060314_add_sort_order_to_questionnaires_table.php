<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('status');
        });

        // Seed sort_order for existing rows, scoped per asset_type
        $rows = DB::table('questionnaires')
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->orderBy('asset_type')
            ->orderBy('created_at')
            ->get(['id', 'asset_type']);

        $counters = [];
        foreach ($rows as $row) {
            $key = $row->asset_type ?? '__null__';
            $counters[$key] = ($counters[$key] ?? 0) + 1;
            DB::table('questionnaires')->where('id', $row->id)->update(['sort_order' => $counters[$key]]);
        }
    }

    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $map = [
        'anchor_point' => 'AP',
        'static_line'  => 'SL',
        'ladder'       => 'LA',
        'guardrail'    => 'GD',
        'walkway'      => 'WW',
        'other'        => 'OTR',
    ];

    public function up(): void
    {
        foreach ($this->map as $old => $new) {
            DB::table('assets')->where('asset_type', $old)->update(['asset_type' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->map as $old => $new) {
            DB::table('assets')->where('asset_type', $new)->update(['asset_type' => $old]);
        }
    }
};

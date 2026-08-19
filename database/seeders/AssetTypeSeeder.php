<?php

namespace Database\Seeders;

use App\Models\MasterLookup;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['value' => 'anchor_point', 'label' => 'Anchor Point',  'sort_order' => 1],
            ['value' => 'static_line',  'label' => 'Static Line',   'sort_order' => 2],
            ['value' => 'ladder',       'label' => 'Ladder',        'sort_order' => 3],
            ['value' => 'guardrail',    'label' => 'Guardrail',     'sort_order' => 4],
            ['value' => 'walkway',      'label' => 'Walkway',       'sort_order' => 5],
            ['value' => 'other',        'label' => 'Other',         'sort_order' => 99],
        ];

        foreach ($types as $type) {
            MasterLookup::updateOrCreate(
                ['category' => 'asset_type', 'value' => $type['value']],
                ['label' => $type['label'], 'sort_order' => $type['sort_order']]
            );
        }
    }
}

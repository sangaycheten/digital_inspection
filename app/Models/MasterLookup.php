<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MasterLookup extends Model
{
    use HasUuids;

    protected $fillable = ['category', 'value', 'label', 'sort_order'];

    public const CATEGORIES = [
        'asset_type'     => 'Asset Type',
        'defect_reason'  => 'Defect Reason',
        'recommendation' => 'Recommendation',
    ];

    /** Returns [value => label] map for asset types, ordered by sort_order. */
    public static function assetTypeMap(): array
    {
        return static::where('category', 'asset_type')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->pluck('label', 'value')
            ->toArray();
    }
}

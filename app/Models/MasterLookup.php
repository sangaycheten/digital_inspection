<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MasterLookup extends Model
{
    use HasUuids;

    protected $fillable = ['category', 'label', 'sort_order'];

    public const CATEGORIES = [
        'asset_type'     => 'Asset Type',
        'defect_reason'  => 'Defect Reason',
        'recommendation' => 'Recommendation',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InstallationAsset extends Model
{
    use HasUuids;

    public $timestamps = false;

    const ACTIONS = ['installed', 'repaired', 'adjusted', 'connected'];

    protected $fillable = ['job_id', 'asset_id', 'action', 'material_notes'];

    protected $casts = ['created_at' => 'datetime'];

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function asset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}

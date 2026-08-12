<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobTargetAsset extends Model
{
    public $timestamps       = false;
    public $incrementing     = false;
    protected $primaryKey    = null;

    protected $fillable = ['job_id', 'asset_id', 'completed', 'completed_at'];

    protected $casts = [
        'completed'    => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function asset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}

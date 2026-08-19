<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Asset extends Model
{
    use HasUuids, LogsActivity;

    // Asset types are managed in master_lookups (category: asset_type).
    // Use MasterLookup::assetTypeMap() for dropdowns and Rule::exists for validation.

    const STATUSES = [
        'pass',
        'fail',
        'under_review',
        'restricted_use',
        'not_inspected',
        'not_located',
        'removed',
        'replaced',
    ];

    protected $fillable = [
        'site_id',
        'building_id',
        'zone',
        'asset_code',
        'asset_type',
        'group_id',
        'make',
        'model',
        'serial_or_batch',
        'rating',
        'fixing_type',
        'current_status',
        'current_inspection_id',
        'install_date',
        'created_from_job_id',
        'next_inspection_due_date',
        'replaces_asset_id',
        'replaced_by_asset_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'install_date' => 'date',
        'next_inspection_due_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            if (Auth::check()) {
                $asset->created_by ??= Auth::id();
                $asset->updated_by ??= Auth::id();
            }
        });

        static::updating(function (Asset $asset) {
            if (Auth::check()) {
                $asset->updated_by = Auth::id();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'site_id', 'building_id', 'zone',
                'asset_code', 'asset_type', 'group_id',
                'make', 'model', 'serial_or_batch', 'rating', 'fixing_type',
                'current_status',
                'install_date', 'next_inspection_due_date',
                'replaces_asset_id', 'replaced_by_asset_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('asset');
    }

    public function site(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function building(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function currentInspection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InspectionRecord::class, 'current_inspection_id');
    }

    public function inspectionRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InspectionRecord::class)->orderByDesc('inspection_date');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function replacesAsset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asset::class, 'replaces_asset_id');
    }

    public function replacedByAsset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asset::class, 'replaced_by_asset_id');
    }
}

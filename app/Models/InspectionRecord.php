<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InspectionRecord extends Model
{
    use HasUuids, LogsActivity;

    // Append-only: no updated_at
    public $timestamps = false;

    const RESULTS = [
        'pass',
        'fail',
        'under_review',
        'restricted_use',
        'not_inspected',
        'not_located',
    ];

    const DOCUMENT_STATUSES = [
        'draft',
        'approved',
    ];

    protected $fillable = [
        'asset_id',
        'job_id',
        'inspection_date',
        'technician_id',
        'result',
        'condition',
        'defect_description',
        'reason_for_result',
        'recommendation',
        'required_action',
        'document_status',
        'is_current',
        'previous_inspection_id',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'is_current' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->useLogName('inspection_record');
    }

    public function asset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function technician(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function previousInspection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InspectionRecord::class, 'previous_inspection_id');
    }

    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InspectionAnswer::class);
    }
}

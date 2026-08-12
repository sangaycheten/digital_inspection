<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Job extends Model
{
    use HasUuids, LogsActivity;

    protected $table = 'work_orders';

    const WORK_TYPES = [
        'first_inspection' => 'First Inspection',
        're_inspection'    => 'Re-Inspection',
        'installation'     => 'Installation',
        'rectification'    => 'Rectification',
        'combined'         => 'Combined',
    ];

    const STATUSES = [
        'new'                   => 'New',
        'scheduled'             => 'Scheduled',
        'in_progress'           => 'In Progress',
        'submitted_for_review'  => 'Submitted for Review',
        'under_review'          => 'Under Review',
        'approved'              => 'Approved',
        'issued'                => 'Issued',
        'rectification_required'=> 'Rectification Required',
        'closed'                => 'Closed',
    ];

    // Valid forward transitions from each status
    const TRANSITIONS = [
        'new'                    => ['scheduled'],
        'scheduled'              => ['in_progress'],
        'in_progress'            => ['submitted_for_review'],
        'submitted_for_review'   => ['under_review'],
        'under_review'           => ['approved', 'rectification_required'],
        'approved'               => ['issued'],
        'issued'                 => ['closed'],
        'rectification_required' => ['in_progress', 'closed'],
        'closed'                 => [],
    ];

    protected $fillable = [
        'site_id',
        'client_id',
        'work_type',
        'status',
        'scheduled_date',
        'scope_notes',
        'created_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Job $job) {
            if (Auth::check()) {
                $job->created_by ??= Auth::id();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['site_id', 'client_id', 'work_type', 'status', 'scheduled_date', 'scope_notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('job');
    }

    public function site(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function technicians(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_technicians', 'job_id', 'technician_id');
    }

    public function buildings(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Building::class, 'job_buildings', 'job_id', 'building_id');
    }

    public function targetAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JobTargetAsset::class, 'job_id');
    }

    public function installationAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InstallationAsset::class, 'job_id');
    }

    public function inspectionRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InspectionRecord::class, 'job_id');
    }

    public function nextStatuses(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}

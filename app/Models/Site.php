<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = ['client_id', 'name', 'address', 'latitude', 'longitude', 'site_notes'];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function buildings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_site');
    }
}

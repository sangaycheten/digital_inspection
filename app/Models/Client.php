<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = ['name', 'custom_client_code', 'billing_contact_info', 'status'];

    public function sites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }
}

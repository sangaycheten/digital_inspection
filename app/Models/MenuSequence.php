<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MenuSequence extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'role', 'section', 'key', 'parent_key', 'sequence',
        'label', 'icon', 'route', 'route_params', 'pattern',
        'permission', 'canany', 'active_params', 'no_params',
        'collapse_id', 'enabled',
    ];

    protected $casts = [
        'route_params'  => 'array',
        'canany'        => 'array',
        'active_params' => 'array',
        'no_params'     => 'boolean',
        'enabled'       => 'boolean',
    ];

    public function getLabelAttribute(): string
    {
        $raw = $this->attributes['label'] ?? null;
        if ($raw !== null && $raw !== '') {
            return $raw;
        }
        if (!empty($this->attributes['permission'])) {
            return Str::title($this->attributes['permission']);
        }
        if (!empty($this->attributes['canany'])) {
            $perms = is_string($this->attributes['canany'])
                ? json_decode($this->attributes['canany'], true)
                : (array) $this->attributes['canany'];
            return Str::title($perms[0] ?? $this->key);
        }
        return $this->key;
    }

    public function getIconAttribute(): string
    {
        return $this->attributes['icon'] ?? 'ri-circle-line';
    }

    /**
     * Build a nav-item array compatible with the sidebar template.
     */
    public function toNavItem(array $children = []): array
    {
        $item = array_filter([
            'menu_key'   => $this->key,
            'label'      => $this->attributes['label'] ?? null,
            'icon'       => $this->attributes['icon'] ?? 'ri-circle-line',
            'route'      => $this->attributes['route'] ?? null,
            'params'     => $this->route_params,
            'pattern'    => $this->attributes['pattern'] ?? null,
            'permission' => $this->attributes['permission'] ?? null,
            'canany'     => $this->canany,
            'active_params' => $this->active_params,
            'no_params'  => $this->no_params ?: null,
            'id'         => $this->attributes['collapse_id'] ?? null,
        ], fn ($v) => !is_null($v));

        if (!empty($children)) {
            $item['children'] = $children;
        }

        return $item;
    }
}

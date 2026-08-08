<?php

namespace App\Models;

use App\Enums\DataType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldType extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = ['name', 'type', 'options', 'status'];

    protected $casts = [
        'options' => 'array',
    ];

    public function typeEnum(): DataType
    {
        return DataType::from($this->type);
    }

    public function typeLabel(): string
    {
        return DataType::from($this->type)->label();
    }

    public function typeBadgeClass(): string
    {
        return match($this->type) {
            'text'        => 'bg-primary-subtle text-primary',
            'numeric'     => 'bg-info-subtle text-info',
            'switch'      => 'bg-success-subtle text-success',
            'option_list' => 'bg-warning-subtle text-warning',
            'long_text'   => 'bg-secondary-subtle text-secondary',
            default       => 'bg-light text-dark',
        };
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, ['switch', 'option_list']);
    }
}

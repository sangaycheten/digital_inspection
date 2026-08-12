<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Questionnaire extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = ['name', 'key', 'type', 'field_type_id', 'section_id', 'parent_id', 'condition', 'enabled', 'required', 'status'];

    protected $casts = [
        'enabled'  => 'boolean',
        'required' => 'boolean',
    ];

    public function fieldType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FieldType::class);
    }

    public function section(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Questionnaire::class, 'parent_id');
    }

    public function subQuestionnaires(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Questionnaire::class, 'parent_id');
    }
}

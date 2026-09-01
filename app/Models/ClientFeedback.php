<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientFeedback extends Model
{
    use HasUuids;

    protected $table = 'client_feedbacks';

    protected $fillable = [
        'user_id', 'client_id', 'job_id',
        'overall_rating', 'service_quality', 'communication',
        'timeliness', 'professionalism', 'message',
    ];

    protected $casts = [
        'overall_rating'  => 'integer',
        'service_quality' => 'integer',
        'communication'   => 'integer',
        'timeliness'      => 'integer',
        'professionalism' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function starsHtml(int $rating): string
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $html .= $i <= $rating
                ? '<i class="ri-star-fill text-warning"></i>'
                : '<i class="ri-star-line text-muted"></i>';
        }
        return $html;
    }
}

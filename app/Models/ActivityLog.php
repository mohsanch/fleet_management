<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;
    
    protected $fillable = ['user_id', 'action', 'model_type', 'model_id', 'reason', 'details', 'description'];

    protected $casts = [
        'details' => 'array',
    ];

    public function getDescriptionAttribute()
    {
        return $this->reason;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['reason'] = $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

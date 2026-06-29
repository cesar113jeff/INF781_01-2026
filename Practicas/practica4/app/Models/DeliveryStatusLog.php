<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryStatusLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['delivery_id', 'old_status', 'new_status', 'changed_by', 'notes', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

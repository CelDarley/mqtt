<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceGroupAssignment extends Model
{
    protected $fillable = [
        'device_id',
        'group_id',
        'is_active',
        'assigned_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'assigned_at' => 'datetime'
    ];

    /**
     * Relacionamento com o dispositivo (tópico)
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'device_id');
    }

    /**
     * Relacionamento com o grupo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(DeviceGroup::class, 'group_id');
    }

    /**
     * Scope para associações ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

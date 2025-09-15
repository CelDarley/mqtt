<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Relacionamento com as associações de dispositivos
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(DeviceGroupAssignment::class, 'group_id');
    }

    /**
     * Relacionamento com os dispositivos através das associações
     */
    public function devices()
    {
        return $this->belongsToMany(Topic::class, 'device_group_assignments', 'group_id', 'device_id')
                    ->where('device_group_assignments.is_active', true);
    }

    /**
     * Scope para grupos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por ordem de exibição
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Retorna a cor do grupo com fallback
     */
    public function getColorAttribute($value)
    {
        return $value ?: '#3B82F6';
    }
}

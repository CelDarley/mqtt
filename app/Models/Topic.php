<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Topic extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relacionamento com a associação de grupo
     */
    public function groupAssignment(): HasOne
    {
        return $this->hasOne(DeviceGroupAssignment::class, 'device_id')->where('is_active', true);
    }

    /**
     * Relacionamento direto com o grupo
     */
    public function group()
    {
        return $this->belongsTo(DeviceGroup::class, 'group_id', 'id')
                    ->whereHas('assignments', function($query) {
                        $query->where('device_id', $this->id)->where('is_active', true);
                    });
    }

    /**
     * Verifica se o dispositivo tem um grupo
     */
    public function hasGroup(): bool
    {
        return $this->groupAssignment()->exists();
    }

    /**
     * Retorna o nome do grupo ou "Sem Grupo"
     */
    public function getGroupNameAttribute(): string
    {
        if ($this->hasGroup()) {
            return $this->groupAssignment->group->name;
        }
        return 'Sem Grupo';
    }

    /**
     * Retorna a cor do grupo ou cor padrão
     */
    public function getGroupColorAttribute(): string
    {
        if ($this->hasGroup()) {
            return $this->groupAssignment->group->color;
        }
        return '#6B7280'; // Cor cinza para "sem grupo"
    }
}

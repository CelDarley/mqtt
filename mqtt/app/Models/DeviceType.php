<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'specifications',
        'is_active'
    ];

    protected $casts = [
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Buscar tópicos relacionados a este tipo de dispositivo
     * (baseado no nome do tipo no tópico)
     */
    public function getRelatedTopics()
    {
        // Como o nome do tipo de dispositivo faz parte do nome do tópico,
        // vamos buscar tópicos que contenham o nome do tipo
        return Topic::where('name', 'LIKE', '%/' . strtolower($this->name) . '/%')
                   ->orWhere('name', 'LIKE', '%/' . $this->name . '/%')
                   ->get();
    }

    /**
     * Scope para tipos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para busca por nome
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    /**
     * Obtém estatísticas do tipo de dispositivo
     */
    public function getStats()
    {
        $relatedTopics = $this->getRelatedTopics();
        
        return [
            'total_topics' => $relatedTopics->count(),
            'active_topics' => $relatedTopics->where('is_active', true)->count(),
            'last_activity' => $relatedTopics->sortByDesc('updated_at')->first()?->updated_at,
        ];
    }
}

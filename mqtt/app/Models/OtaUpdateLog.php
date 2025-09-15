<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtaUpdateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_type_id',
        'firmware_version',
        'previous_version',
        'devices_count',
        'status',
        'device_results',
        'error_message',
        'started_at',
        'completed_at',
        'firmware_url',
        'checksum_md5',
        'firmware_size_bytes',
        'metadata'
    ];

    protected $casts = [
        'device_results' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'firmware_size_bytes' => 'integer',
        'devices_count' => 'integer'
    ];

    /**
     * Relacionamento com DeviceType
     */
    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class);
    }

    /**
     * Scopes para facilitar consultas
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDeviceType($query, $deviceTypeId)
    {
        return $query->where('device_type_id', $deviceTypeId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Métodos utilitários
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage
        ]);
    }

    public function addDeviceResult($deviceId, $result)
    {
        $results = $this->device_results ?? [];
        $results[$deviceId] = [
            'status' => $result['status'],
            'message' => $result['message'] ?? null,
            'timestamp' => now()->toISOString(),
            'firmware_version' => $result['firmware_version'] ?? null
        ];
        
        $this->update(['device_results' => $results]);
    }

    /**
     * Calcular estatísticas do update
     */
    public function getSuccessfulDevicesCount()
    {
        if (!$this->device_results) {
            return 0;
        }
        
        return collect($this->device_results)
            ->where('status', 'success')
            ->count();
    }

    public function getFailedDevicesCount()
    {
        if (!$this->device_results) {
            return 0;
        }
        
        return collect($this->device_results)
            ->where('status', 'failed')
            ->count();
    }

    public function getSuccessRate()
    {
        if ($this->devices_count === 0) {
            return 0;
        }
        
        return round(($this->getSuccessfulDevicesCount() / $this->devices_count) * 100, 2);
    }

    public function getDurationMinutes()
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }
        
        return $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Verificar se o update está ativo
     */
    public function isActive()
    {
        return in_array($this->status, ['initiated', 'in_progress']);
    }

    /**
     * Verificar se o update foi concluído
     */
    public function isCompleted()
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled']);
    }
}

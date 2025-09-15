<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PendingDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'mac_address',
        'device_name',
        'ip_address',
        'wifi_ssid',
        'status',
        'registered_at',
        'device_info',
        'activated_at',
        'activated_by'
    ];

    protected $casts = [
        'device_info' => 'array',
        'registered_at' => 'integer',
        'activated_at' => 'datetime'
    ];

    // ===== SCOPES =====
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActivated($query)
    {
        return $query->where('status', 'activated');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRecent($query, $hours = 24)
    {
        $timestampLimit = Carbon::now()->subHours($hours)->timestamp * 1000; // Converter para milissegundos
        return $query->where('registered_at', '>=', $timestampLimit);
    }

    // ===== RELATIONSHIPS =====
    public function activatedBy()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function topic()
    {
        return $this->hasOne(Topic::class, 'device_mac', 'mac_address');
    }

    // ===== ACCESSORS =====
    public function getFormattedMacAttribute()
    {
        return strtoupper($this->mac_address);
    }

    public function getTimeSinceRegistrationAttribute()
    {
        $registeredTimestamp = $this->registered_at / 1000; // Converter de milissegundos para segundos
        return Carbon::createFromTimestamp($registeredTimestamp)->diffForHumans();
    }

    public function getFormattedStatusAttribute()
    {
        $statuses = [
            'pending' => '⏳ Pendente',
            'activated' => '✅ Ativado',
            'rejected' => '❌ Rejeitado'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getFirmwareVersionAttribute()
    {
        return $this->device_info['firmware_version'] ?? 'N/A';
    }

    public function getEsp32ModelAttribute()
    {
        return $this->device_info['esp32_model'] ?? 'N/A';
    }

    public function getFreeHeapAttribute()
    {
        $heap = $this->device_info['free_heap'] ?? 0;
        return number_format($heap / 1024, 2) . ' KB';
    }

    // ===== MÉTODOS DE NEGÓCIO =====
    public function activate($userId, $deviceType, $department)
    {
        $this->update([
            'status' => 'activated',
            'activated_at' => now(),
            'activated_by' => $userId
        ]);

        return $this;
    }

    public function reject($userId)
    {
        $this->update([
            'status' => 'rejected',
            'activated_by' => $userId
        ]);

        return $this;
    }

    public function canBeActivated()
    {
        return $this->status === 'pending';
    }

    public function getActivationData()
    {
        return [
            'device_name' => $this->device_name,
            'mac_address' => $this->mac_address,
            'ip_address' => $this->ip_address,
            'wifi_ssid' => $this->wifi_ssid,
            'firmware_version' => $this->firmware_version,
            'esp32_model' => $this->esp32_model,
            'free_heap' => $this->free_heap,
            'registered_at' => Carbon::createFromTimestamp($this->registered_at / 1000)->format('d/m/Y H:i:s'),
            'time_since_registration' => $this->time_since_registration
        ];
    }

    // ===== MÉTODOS ESTÁTICOS =====
    public static function createFromESP32($data)
    {
        return self::create([
            'mac_address' => $data['mac_address'],
            'device_name' => $data['device_name'],
            'ip_address' => $data['ip_address'] ?? null,
            'wifi_ssid' => $data['wifi_ssid'] ?? null,
            'status' => 'pending',
            'registered_at' => $data['registered_at'] ?? time() * 1000, // Usar timestamp do ESP32 ou timestamp atual em milissegundos
            'device_info' => $data['device_info'] ?? null
        ]);
    }

    public static function findByMac($macAddress)
    {
        return self::where('mac_address', $macAddress)->first();
    }

    public static function countByStatus($status = null)
    {
        $query = self::query();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->count();
    }

    public static function getStats()
    {
        $todayStart = Carbon::today()->timestamp * 1000; // Início do dia em milissegundos
        $todayEnd = Carbon::tomorrow()->timestamp * 1000; // Início do próximo dia em milissegundos
        
        return [
            'total' => self::count(),
            'pending' => self::countByStatus('pending'),
            'activated' => self::countByStatus('activated'),
            'rejected' => self::countByStatus('rejected'),
            'recent' => self::recent(24)->count(),
            'today' => self::whereBetween('registered_at', [$todayStart, $todayEnd])->count()
        ];
    }
}

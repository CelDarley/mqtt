<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'id_comp',
        'tipo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'id_comp' => 'integer',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'tipo' => $this->tipo,
            'id_comp' => $this->id_comp,
        ];
    }

    /**
     * Relacionamento com a companhia
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_comp');
    }

    /**
     * Verifica se o usuário é administrador
     */
    public function isAdmin(): bool
    {
        return $this->tipo === 'admin';
    }

    /**
     * Verifica se o usuário é comum
     */
    public function isCommon(): bool
    {
        return $this->tipo === 'comum';
    }

    /**
     * Escopo para usuários administradores
     */
    public function scopeAdmins($query)
    {
        return $query->where('tipo', 'admin');
    }

    /**
     * Escopo para usuários comuns
     */
    public function scopeCommon($query)
    {
        return $query->where('tipo', 'comum');
    }

    /**
     * Escopo para usuários de uma companhia específica
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('id_comp', $companyId);
    }
}

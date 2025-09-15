<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Relacionamento com departamentos
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'id_comp', 'id');
    }

    /**
     * Relacionamento com usuários
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_comp', 'id');
    }

    /**
     * Obter departamentos raiz (nível 1)
     */
    public function rootDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'id_comp', 'id')->where('nivel_hierarquico', 1);
    }

    /**
     * Obter estrutura organizacional completa
     */
    public function getOrganizationalStructure()
    {
        return $this->departments()
            ->orderBy('nivel_hierarquico')
            ->orderBy('id_unid_up')
            ->orderBy('name')
            ->get()
            ->groupBy('nivel_hierarquico');
    }

    /**
     * Obter estatísticas da companhia
     */
    public function getStats()
    {
        return [
            'total_departments' => $this->departments()->count(),
            'total_users' => $this->users()->count(),
            'admin_users' => $this->users()->where('tipo', 'admin')->count(),
            'common_users' => $this->users()->where('tipo', 'comum')->count(),
        ];
    }
}

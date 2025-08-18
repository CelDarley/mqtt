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
        return $this->hasMany(Department::class);
    }

    /**
     * Obter departamentos raiz (nível 1)
     */
    public function rootDepartments(): HasMany
    {
        return $this->hasMany(Department::class)->where('nivel_hierarquico', 1);
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
}

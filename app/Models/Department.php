<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nivel_hierarquico',
        'id_unid_up',
        'id_comp'
    ];

    protected $casts = [
        'nivel_hierarquico' => 'integer',
        'id_unid_up' => 'integer',
        'id_comp' => 'integer',
    ];

    /**
     * Relacionamento com a companhia
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_comp');
    }

    /**
     * Relacionamento com a unidade superior (parent)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'id_unid_up');
    }

    /**
     * Relacionamento com as unidades subordinadas (children)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'id_unid_up');
    }

    /**
     * Relacionamento recursivo com todas as unidades subordinadas
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Relacionamento recursivo com todas as unidades superiores
     */
    public function allParents(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'id_unid_up')->with('allParents');
    }

    /**
     * Verifica se é uma unidade raiz (nível 1)
     */
    public function isRoot(): bool
    {
        return $this->nivel_hierarquico === 1;
    }

    /**
     * Verifica se é uma unidade folha (sem unidades subordinadas)
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Obtém o caminho completo da hierarquia
     */
    public function getHierarchyPath(): string
    {
        $path = collect([$this]);
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            $path->prepend($current);
        }

        return $path->pluck('name')->implode(' > ');
    }

    /**
     * Obtém todas as unidades no mesmo nível hierárquico
     */
    public function getSiblings()
    {
        if ($this->isRoot()) {
            return Department::where('id_comp', $this->id_comp)
                           ->where('nivel_hierarquico', 1)
                           ->where('id', '!=', $this->id);
        }

        return Department::where('id_comp', $this->id_comp)
                        ->where('id_unid_up', $this->id_unid_up)
                        ->where('id', '!=', $this->id);
    }

    /**
     * Obtém a árvore organizacional da companhia
     */
    public static function getOrganizationalTree(int $companyId)
    {
        return Department::where('id_comp', $companyId)
                        ->where('nivel_hierarquico', 1)
                        ->with('allChildren')
                        ->get();
    }

    /**
     * Obtém estatísticas da estrutura organizacional da companhia
     */
    public static function getOrganizationalStats(int $companyId)
    {
        $departments = Department::where('id_comp', $companyId);
        $total = $departments->count();
        $roots = $departments->where('nivel_hierarquico', 1)->count();
        $leaves = $departments->whereNotExists(function ($query) use ($companyId) {
            $query->select(\DB::raw(1))
                  ->from('departments as d2')
                  ->whereColumn('d2.id_unid_up', 'departments.id')
                  ->where('d2.id_comp', $companyId);
        })->count();

        return [
            'total_departments' => $total,
            'root_departments' => $roots,
            'leaf_departments' => $leaves,
            'middle_departments' => $total - $roots - $leaves,
            'max_hierarchy_level' => $departments->max('nivel_hierarquico') ?? 0
        ];
    }
}

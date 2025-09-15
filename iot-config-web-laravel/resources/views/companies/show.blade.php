@extends('layouts.app')

@section('title', 'Detalhes da Empresa')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">
                🏢 {{ $company['name'] }}
            </h1>
            <p class="page-description">
                Detalhes completos da empresa e sua estrutura organizacional
            </p>
        </div>
        <div class="page-actions">
            <a href="{{ route('companies.index') }}" class="btn btn-secondary">
                ← Voltar à Lista
            </a>
            <a href="{{ route('companies.edit', $company['id']) }}" class="btn btn-primary">
                ✏️ Editar
            </a>
        </div>
    </div>

    <div class="content-grid">
        <!-- Informações Principais -->
        <div class="dashboard-card">
            <h2>📋 Informações Básicas</h2>
            <div class="info-grid">
                <div class="detail-item">
                    <label>🆔 ID da Empresa:</label>
                    <div class="detail-value">
                        <span class="entity-id">#{{ $company['id'] }}</span>
                    </div>
                </div>
                
                <div class="detail-item">
                    <label>🏢 Nome:</label>
                    <div class="detail-value">{{ $company['name'] }}</div>
                </div>
                
                <div class="detail-item">
                    <label>📅 Criada em:</label>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($company['created_at'])->format('d/m/Y H:i') }}
                        <small>({{ \Carbon\Carbon::parse($company['created_at'])->diffForHumans() }})</small>
                    </div>
                </div>
                
                <div class="detail-item">
                    <label>🔄 Última atualização:</label>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($company['updated_at'])->format('d/m/Y H:i') }}
                        <small>({{ \Carbon\Carbon::parse($company['updated_at'])->diffForHumans() }})</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="dashboard-card">
            <h2>📊 Estatísticas</h2>
            <div class="stats-grid">
                <div class="stat-card departments">
                    <div class="stat-icon">🏛️</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ isset($company['departments']) ? count($company['departments']) : 0 }}</div>
                        <div class="stat-label">Departamentos</div>
                    </div>
                </div>
                
                <div class="stat-card levels">
                    <div class="stat-icon">📈</div>
                    <div class="stat-content">
                        <div class="stat-number">
                            @if(isset($company['departments']) && count($company['departments']) > 0)
                                {{ max(array_column($company['departments'], 'nivel_hierarquico')) }}
                            @else
                                0
                            @endif
                        </div>
                        <div class="stat-label">Níveis Hierárquicos</div>
                    </div>
                </div>
                
                <div class="stat-card root-depts">
                    <div class="stat-icon">🌳</div>
                    <div class="stat-content">
                        <div class="stat-number">
                            @if(isset($company['departments']))
                                {{ count(array_filter($company['departments'], function($dept) { return $dept['parent_id'] === null; })) }}
                            @else
                                0
                            @endif
                        </div>
                        <div class="stat-label">Departamentos Raiz</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departamentos -->
        @if(isset($company['departments']) && count($company['departments']) > 0)
            <div class="dashboard-card full-width">
                <h2>🏛️ Estrutura de Departamentos</h2>
                <div class="departments-container">
                    <div class="departments-hierarchy">
                        @php
                            // Organizar departamentos por hierarquia
                            $departmentsByLevel = [];
                            foreach($company['departments'] as $dept) {
                                $level = $dept['nivel_hierarquico'];
                                if (!isset($departmentsByLevel[$level])) {
                                    $departmentsByLevel[$level] = [];
                                }
                                $departmentsByLevel[$level][] = $dept;
                            }
                            ksort($departmentsByLevel);
                        @endphp
                        
                        @foreach($departmentsByLevel as $level => $departments)
                            <div class="hierarchy-level">
                                <div class="level-header">
                                    <span class="level-badge">Nível {{ $level }}</span>
                                    <span class="level-count">{{ count($departments) }} departamento(s)</span>
                                </div>
                                <div class="departments-list">
                                    @foreach($departments as $department)
                                        <div class="department-card level-{{ $level }}">
                                            <div class="department-header">
                                                <h4 class="department-name">📁 {{ $department['name'] }}</h4>
                                                <span class="department-id">#{{ $department['id'] }}</span>
                                            </div>
                                            
                                            @if($department['description'])
                                                <div class="department-description">
                                                    {{ $department['description'] }}
                                                </div>
                                            @endif
                                            
                                            <div class="department-meta">
                                                @if($department['parent_id'])
                                                    <div class="parent-info">
                                                        <small>
                                                            👆 Subordinado a: 
                                                            @php
                                                                $parent = collect($company['departments'])->firstWhere('id', $department['parent_id']);
                                                            @endphp
                                                            @if($parent)
                                                                <strong>{{ $parent['name'] }}</strong>
                                                            @else
                                                                Departamento não encontrado
                                                            @endif
                                                        </small>
                                                    </div>
                                                @else
                                                    <div class="root-info">
                                                        <small>🌳 <strong>Departamento Raiz</strong></small>
                                                    </div>
                                                @endif
                                                
                                                <div class="department-status">
                                                    @if($department['is_active'])
                                                        <span class="status-badge active">✅ Ativo</span>
                                                    @else
                                                        <span class="status-badge inactive">❌ Inativo</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="department-actions">
                                                <a href="{{ route('departments.show', $department['id']) }}" 
                                                   class="btn-action btn-view" 
                                                   title="Ver Departamento">
                                                    👁️ Detalhes
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="dashboard-card full-width">
                <h2>🏛️ Departamentos</h2>
                <div class="empty-state">
                    <div class="empty-icon">📁</div>
                    <h3>Nenhum departamento cadastrado</h3>
                    <p>Esta empresa ainda não possui departamentos organizados.</p>
                    <a href="{{ route('departments.create') }}" class="btn btn-primary">
                        ➕ Criar Primeiro Departamento
                    </a>
                </div>
            </div>
        @endif

        <!-- Ações -->
        <div class="dashboard-card full-width">
            <h2>⚡ Ações Rápidas</h2>
            <div class="quick-actions">
                <a href="{{ route('companies.edit', $company['id']) }}" class="action-card edit">
                    <div class="action-icon">✏️</div>
                    <div class="action-content">
                        <h3>Editar Empresa</h3>
                        <p>Modificar informações básicas</p>
                    </div>
                </a>
                
                <a href="{{ route('departments.create') }}?company_id={{ $company['id'] }}" class="action-card create">
                    <div class="action-icon">➕</div>
                    <div class="action-content">
                        <h3>Novo Departamento</h3>
                        <p>Adicionar departamento à empresa</p>
                    </div>
                </a>
                
                @if(isset($company['departments']) && count($company['departments']) > 0)
                    <a href="{{ route('companies.organizational-structure', $company['id']) }}" class="action-card structure">
                        <div class="action-icon">🌳</div>
                        <div class="action-content">
                            <h3>Estrutura Organizacional</h3>
                            <p>Visualizar hierarquia completa</p>
                        </div>
                    </a>
                @endif
                
                <button onclick="confirmDelete()" class="action-card delete">
                    <div class="action-icon">🗑️</div>
                    <div class="action-content">
                        <h3>Deletar Empresa</h3>
                        <p>Remover permanentemente</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
}

.page-title {
    font-size: 2rem;
    font-weight: bold;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
}

.page-description {
    color: #6b7280;
    margin: 0;
    font-size: 1rem;
}

.page-actions {
    display: flex;
    gap: 1rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
}

.dashboard-card.full-width {
    grid-column: 1 / -1;
}

.dashboard-card h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.info-grid {
    display: grid;
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-item label {
    font-weight: 500;
    color: #6b7280;
    font-size: 0.875rem;
}

.detail-value {
    color: #1f2937;
    font-weight: 500;
}

.detail-value small {
    color: #6b7280;
    font-weight: normal;
    display: block;
}

.entity-id {
    font-family: 'Courier New', monospace;
    background-color: #e5e7eb;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

/* Estatísticas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

.stat-card.departments {
    background-color: #dbeafe;
    border-color: #93c5fd;
}

.stat-card.levels {
    background-color: #d1fae5;
    border-color: #a7f3d0;
}

.stat-card.root-depts {
    background-color: #fef3c7;
    border-color: #fcd34d;
}

.stat-icon {
    font-size: 1.5rem;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1f2937;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Departamentos */
.departments-hierarchy {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.hierarchy-level {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.level-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.level-badge {
    background-color: #3b82f6;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.level-count {
    color: #6b7280;
    font-size: 0.875rem;
}

.departments-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.department-card {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    background-color: #fafafa;
}

.department-card.level-1 {
    border-left: 4px solid #3b82f6;
}

.department-card.level-2 {
    border-left: 4px solid #10b981;
}

.department-card.level-3 {
    border-left: 4px solid #f59e0b;
}

.department-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.department-name {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.department-id {
    font-family: 'Courier New', monospace;
    background-color: #e5e7eb;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    color: #374151;
}

.department-description {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
}

.department-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background-color: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background-color: #fee2e2;
    color: #991b1b;
}

.department-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.5rem 0.75rem;
    background-color: #e0f2fe;
    color: #0277bd;
    text-decoration: none;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    transition: background-color 0.2s ease;
}

.btn-action:hover {
    background-color: #b3e5fc;
}

/* Ações rápidas */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.2s ease;
    background: white;
    cursor: pointer;
}

.action-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.action-card.edit {
    border-left: 4px solid #f59e0b;
}

.action-card.create {
    border-left: 4px solid #10b981;
}

.action-card.structure {
    border-left: 4px solid #8b5cf6;
}

.action-card.delete {
    border-left: 4px solid #ef4444;
}

.action-icon {
    font-size: 1.5rem;
}

.action-content h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.action-content p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

/* Estado vazio */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.25rem;
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .page-actions {
        flex-direction: column;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .departments-list {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function confirmDelete() {
    const companyName = '{{ $company["name"] }}';
    const departmentsCount = {{ isset($company['departments']) ? count($company['departments']) : 0 }};
    
    let message = `🗑️ Tem certeza que deseja deletar a empresa "${companyName}"?\n\n⚠️ Esta ação não pode ser desfeita.`;
    
    if (departmentsCount > 0) {
        message += `\n\n❌ ATENÇÃO: Esta empresa possui ${departmentsCount} departamento(s).\nNão será possível deletar enquanto houver departamentos associados.`;
        alert(message);
        return;
    }
    
    if (confirm(message)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/companies/{{ $company["id"] }}';
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection 
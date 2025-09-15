@extends('layouts.app')

@section('title', 'Departamentos')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <div class="page-header-content">
            <h1 style="color: #eeeeee;">🏢 Departamentos</h1>
            <p>Gerencie a estrutura organizacional da empresa</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('departments.create') }}" class="btn btn-primary">
                ➕ Novo Departamento
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filtros -->
    <div class="dashboard-card">
        <h2>🔍 Filtros</h2>
        <form method="GET" action="{{ route('departments.index') }}" class="filter-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="company_id">Empresa:</label>
                    <select name="company_id" id="company_id" class="form-control">
                        <option value="">Todas as empresas</option>
                        @foreach($companies ?? [] as $company)
                            <option value="{{ $company['id'] }}" {{ request('company_id') == $company['id'] ? 'selected' : '' }}>
                                {{ $company['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="nivel_hierarquico">Nível Hierárquico:</label>
                    <select name="nivel_hierarquico" id="nivel_hierarquico" class="form-control">
                        <option value="">Todos os níveis</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ request('nivel_hierarquico') == $i ? 'selected' : '' }}>
                                Nível {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary">Filtrar</button>
                    <a href="{{ route('departments.index') }}" class="btn btn-outline">Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Lista de Departamentos -->
    <div class="dashboard-card">
        <h2>📋 Lista de Departamentos</h2>
        
        <!-- Legenda da Hierarquia -->
        <div class="hierarchy-legend">
            <h3>🔍 Legenda da Hierarquia:</h3>
            <div class="legend-items">
                <span class="legend-item"><span class="hierarchy-icon">🏢</span> Nível 1 - Departamentos Principais</span>
                <span class="legend-item"><span class="hierarchy-icon">📋</span> Nível 2 - Subdepartamentos</span>
                <span class="legend-item"><span class="hierarchy-icon">📁</span> Nível 3 - Setores</span>
                <span class="legend-item"><span class="hierarchy-icon">📄</span> Nível 4+ - Subsetores</span>
            </div>
        </div>
        
        @if(count($departments ?? []) > 0)
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Empresa</th>
                            <th>Nível</th>
                            <th>Unidade Superior</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $department)
                            <tr data-level="{{ $department['nivel_hierarquico'] }}" class="hierarchy-row level-{{ $department['nivel_hierarquico'] }}">
                                <td>
                                    <div class="department-hierarchy" style="padding-left: {{ ($department['nivel_hierarquico'] - 1) * 25 }}px;">
                                        @if($department['nivel_hierarquico'] > 1)
                                            <span class="hierarchy-indent">
                                                @for($i = 1; $i < $department['nivel_hierarquico']; $i++)
                                                    <span class="hierarchy-line">│&nbsp;&nbsp;&nbsp;</span>
                                                @endfor
                                                <span class="hierarchy-connector">├─&nbsp;</span>
                                            </span>
                                        @endif
                                        
                                        @if($department['nivel_hierarquico'] == 1)
                                            <span class="hierarchy-icon">🏢</span>
                                        @elseif($department['nivel_hierarquico'] == 2)
                                            <span class="hierarchy-icon">📋</span>
                                        @elseif($department['nivel_hierarquico'] == 3)
                                            <span class="hierarchy-icon">📁</span>
                                        @else
                                            <span class="hierarchy-icon">📄</span>
                                        @endif
                                        
                                        <strong>{{ $department['name'] }}</strong>
                                    </div>
                                </td>
                                <td>
                                    {{ $department['company']['name'] ?? 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge badge-level-{{ $department['nivel_hierarquico'] }}">
                                        Nível {{ $department['nivel_hierarquico'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($department['parent'])
                                        <span class="parent-path">
                                            <small class="text-muted">{{ $department['parent']['name'] }}</small>
                                        </span>
                                    @else
                                        <span class="root-indicator">
                                            <small class="text-muted">🏛️ Raiz</small>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($department['created_at'])->format('d/m/Y H:i') }}
                                </td>
                                <td class="actions">
                                    <a href="{{ route('departments.show', $department['id']) }}" class="btn-action btn-view" title="Visualizar">
                                        👁️
                                    </a>
                                    <a href="{{ route('departments.edit', $department['id']) }}" class="btn-action btn-edit" title="Editar">
                                        ✏️
                                    </a>
                                    <form method="POST" action="{{ route('departments.destroy', $department['id']) }}" 
                                          style="display: inline-block;"
                                          onsubmit="return confirm('Tem certeza que deseja deletar este departamento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Deletar">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <p>📭 Nenhum departamento encontrado.</p>
                <a href="{{ route('departments.create') }}" class="btn btn-primary">
                    ➕ Criar Primeiro Departamento
                </a>
            </div>
        @endif
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-header-content h1 {
    margin: 0;
    color: #2563eb;
}

.page-header-content p {
    margin: 0.5rem 0 0 0;
    color: #6b7280;
}

.filter-form {
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.table-container {
    overflow-x: auto;
    background-color: #ffffff;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background-color: #ffffff;
}

/* Força a cor do texto em todas as células */
.data-table * {
    color: #374151 !important;
}

.data-table strong,
.data-table td strong {
    color: #111827 !important;
    font-weight: 600;
}

/* Exceções para badges e botões que devem manter suas cores */
.data-table .badge,
.data-table .btn-action {
    color: inherit !important;
}

.data-table th,
.data-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
    vertical-align: middle;
    line-height: 1.5;
}

.data-table th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.data-table td {
    background-color: #ffffff;
    color: #374151;
}

.data-table td strong {
    color: #111827;
}

.data-table td span {
    color: #374151;
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.25rem 0.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.btn-view { background-color: #3b82f6; color: white; }
.btn-edit { background-color: #f59e0b; color: white; }
.btn-delete { background-color: #ef4444; color: white; }

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-level-1 { background-color: #dbeafe; color: #1e40af; }
.badge-level-2 { background-color: #dcfce7; color: #166534; }
.badge-level-3 { background-color: #fef3c7; color: #92400e; }
.badge-level-4 { background-color: #fde2e8; color: #991b1b; }
.badge-level-5 { background-color: #f3e8ff; color: #7c3aed; }

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background-color: #fde2e8;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

/* Estilos para hierarquia de departamentos */
.department-hierarchy {
    display: flex;
    align-items: center;
    line-height: 1.5;
}

.hierarchy-indent {
    color: #6b7280;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    white-space: nowrap;
}

.hierarchy-line {
    color: #cbd5e1;
    font-weight: normal;
}

.hierarchy-connector {
    color: #94a3b8;
    margin-right: 0.25rem;
    font-weight: normal;
}

.hierarchy-icon {
    margin-right: 0.5rem;
    font-size: 1rem;
}

/* Estilos para diferentes níveis hierárquicos */
.data-table tr[data-level="1"] {
    background-color: #f0f9ff;
    border-left: 4px solid #0ea5e9;
}

.data-table tr[data-level="1"] td {
    font-weight: 600;
}

.data-table tr[data-level="2"] {
    background-color: #f0fdf4;
    border-left: 3px solid #22c55e;
}

.data-table tr[data-level="3"] {
    background-color: #fffbeb;
    border-left: 2px solid #f59e0b;
}

.data-table tr[data-level="4"] {
    background-color: #fef2f2;
    border-left: 1px solid #ef4444;
}

.data-table tr[data-level="5"] {
    background-color: #faf5ff;
    border-left: 1px solid #a855f7;
}

/* Hover effect para linhas da hierarquia */
.hierarchy-row:hover {
    background-color: #f1f5f9 !important;
    transition: background-color 0.15s ease;
}

.hierarchy-row {
    transition: background-color 0.15s ease;
    min-height: 3rem;
}

.hierarchy-row td {
    min-height: 3rem;
}

/* Estilos para unidade superior */
.parent-path {
    color: #6b7280;
    font-style: italic;
}

.root-indicator {
    color: #059669;
    font-weight: 500;
}

.text-muted {
    color: #6b7280 !important;
}

/* Estilos para a legenda da hierarquia */
.hierarchy-legend {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.hierarchy-legend h3 {
    margin: 0 0 0.75rem 0;
    font-size: 1rem;
    color: #374151;
}

.legend-items {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: #6b7280;
}

.legend-item .hierarchy-icon {
    margin-right: 0.25rem;
}

@media (max-width: 768px) {
    .legend-items {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
@endsection 
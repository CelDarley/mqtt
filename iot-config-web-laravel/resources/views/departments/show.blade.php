@extends('layouts.app')

@section('title', 'Detalhes do Departamento')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <div class="page-header-content">
            <h1 style="color: #eeeeee;">🏢 {{ $department['name'] }}</h1>
            <p>Detalhes da unidade organizacional</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('departments.edit', $department['id']) }}" class="btn btn-primary">
                ✏️ Editar
            </a>
            <a href="{{ route('departments.index') }}" class="btn btn-outline">
                ← Voltar
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

    <div class="details-grid">
        <!-- Informações Básicas -->
        <div class="dashboard-card">
            <h2>📋 Informações Básicas</h2>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Nome:</label>
                    <div class="detail-value">
                        <strong>{{ $department['name'] }}</strong>
                    </div>
                </div>

                <div class="detail-item">
                    <label>Empresa:</label>
                    <div class="detail-value">
                        {{ $department['company']['name'] ?? 'N/A' }}
                    </div>
                </div>

                <div class="detail-item">
                    <label>Nível Hierárquico:</label>
                    <div class="detail-value">
                        <span class="badge badge-level-{{ $department['nivel_hierarquico'] }}">
                            Nível {{ $department['nivel_hierarquico'] }}
                            @if($department['nivel_hierarquico'] == 1)
                                (Raiz)
                            @endif
                        </span>
                    </div>
                </div>

                <div class="detail-item">
                    <label>Unidade Superior:</label>
                    <div class="detail-value">
                        @if($department['parent'])
                            <span class="parent-link">
                                🏢 {{ $department['parent']['name'] }}
                                <small>(Nível {{ $department['parent']['nivel_hierarquico'] }})</small>
                            </span>
                        @else
                            <span class="root-indicator">
                                🏛️ Departamento Raiz
                            </span>
                        @endif
                    </div>
                </div>

                <div class="detail-item">
                    <label>Criado em:</label>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($department['created_at'])->format('d/m/Y H:i') }}
                    </div>
                </div>

                <div class="detail-item">
                    <label>Última atualização:</label>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($department['updated_at'])->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Estrutura Hierárquica -->
        <div class="dashboard-card">
            <h2>🌳 Estrutura Hierárquica</h2>
            
            @if($department['children'] && count($department['children']) > 0)
                <div class="hierarchy-section">
                    <h3>📋 Unidades Subordinadas:</h3>
                    <div class="children-list">
                        @foreach($department['children'] as $child)
                            <div class="child-item">
                                <div class="child-info">
                                    @if($child['nivel_hierarquico'] == 2)
                                        📋
                                    @elseif($child['nivel_hierarquico'] == 3)
                                        📁
                                    @else
                                        📄
                                    @endif
                                    <span class="child-name">{{ $child['name'] }}</span>
                                    <span class="child-level">Nível {{ $child['nivel_hierarquico'] }}</span>
                                </div>
                                <div class="child-actions">
                                    <a href="{{ route('departments.show', $child['id']) }}" class="btn-small btn-view">
                                        👁️ Ver
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="no-children">
                    <p>📭 Este departamento não possui unidades subordinadas.</p>
                </div>
            @endif

            @if($department['parent'])
                <div class="hierarchy-section">
                    <h3>⬆️ Caminho Hierárquico:</h3>
                    <div class="breadcrumb-path">
                        <span class="path-item">
                            🏛️ {{ $department['company']['name'] ?? 'Empresa' }}
                        </span>
                        @if($department['parent'])
                            <span class="path-separator">→</span>
                            <span class="path-item">
                                🏢 {{ $department['parent']['name'] }}
                            </span>
                        @endif
                        <span class="path-separator">→</span>
                        <span class="path-item current">
                            📋 {{ $department['name'] }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Ações -->
    <div class="dashboard-card">
        <h2>⚙️ Ações</h2>
        <div class="actions-grid">
            <a href="{{ route('departments.edit', $department['id']) }}" class="action-button edit">
                <div class="action-icon">✏️</div>
                <div class="action-text">
                    <strong>Editar Departamento</strong>
                    <small>Alterar informações do departamento</small>
                </div>
            </a>

            @if(!$department['children'] || count($department['children']) == 0)
                <form method="POST" action="{{ route('departments.destroy', $department['id']) }}" 
                      onsubmit="return confirm('Tem certeza que deseja deletar este departamento?')" 
                      style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-button delete">
                        <div class="action-icon">🗑️</div>
                        <div class="action-text">
                            <strong>Deletar Departamento</strong>
                            <small>Remover permanentemente</small>
                        </div>
                    </button>
                </form>
            @else
                <div class="action-button disabled">
                    <div class="action-icon">🚫</div>
                    <div class="action-text">
                        <strong>Não é possível deletar</strong>
                        <small>Departamento possui unidades subordinadas</small>
                    </div>
                </div>
            @endif
        </div>
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

.page-header-actions {
    display: flex;
    gap: 1rem;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.detail-grid {
    display: grid;
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item label {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.detail-value {
    color: #6b7280;
    font-size: 1rem;
}

.detail-value strong {
    color: #111827;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-block;
}

.badge-level-1 { background-color: #dbeafe; color: #1e40af; }
.badge-level-2 { background-color: #dcfce7; color: #166534; }
.badge-level-3 { background-color: #fef3c7; color: #92400e; }
.badge-level-4 { background-color: #fde2e8; color: #991b1b; }

.parent-link {
    color: #2563eb;
    text-decoration: none;
}

.root-indicator {
    color: #059669;
    font-weight: 500;
}

.hierarchy-section {
    margin-bottom: 1.5rem;
}

.hierarchy-section h3 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1rem;
}

.children-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.child-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background-color: #f8fafc;
    border-radius: 0.375rem;
    border: 1px solid #e2e8f0;
}

.child-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.child-name {
    font-weight: 500;
    color: #374151;
}

.child-level {
    font-size: 0.75rem;
    color: #6b7280;
    background-color: #e5e7eb;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}

.no-children {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.breadcrumb-path {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.path-item {
    color: #6b7280;
    font-size: 0.875rem;
}

.path-item.current {
    color: #2563eb;
    font-weight: 600;
}

.path-separator {
    color: #d1d5db;
}

.actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.action-button {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    width: 100%;
}

.action-button.edit {
    background-color: #f0f9ff;
    border: 1px solid #0ea5e9;
    color: #0c4a6e;
}

.action-button.edit:hover {
    background-color: #e0f2fe;
}

.action-button.delete {
    background-color: #fef2f2;
    border: 1px solid #ef4444;
    color: #991b1b;
}

.action-button.delete:hover {
    background-color: #fee2e2;
}

.action-button.disabled {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    cursor: not-allowed;
}

.action-icon {
    font-size: 1.5rem;
}

.action-text strong {
    display: block;
    margin-bottom: 0.25rem;
}

.action-text small {
    color: inherit;
    opacity: 0.7;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
    border: none;
    font-size: 1rem;
}

.btn-primary {
    background-color: #2563eb;
    color: white;
}

.btn-outline {
    background-color: white;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-small {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 0.25rem;
}

.btn-view {
    background-color: #3b82f6;
    color: white;
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

@media (max-width: 768px) {
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .breadcrumb-path {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endsection 
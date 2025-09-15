@extends('layouts.app')

@section('title', 'Detalhes do Tipo de Dispositivo')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <div class="page-header-content">
            <h1 style="color: #eeeeee;">{{ $deviceType['icon'] ?? '📱' }} {{ $deviceType['name'] }}</h1>
            <p>Detalhes do tipo de dispositivo IoT</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('device-types.edit', $deviceType['id']) }}" class="btn btn-primary">
                ✏️ Editar
            </a>
            <a href="{{ route('device-types.index') }}" class="btn btn-outline">
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
                        <strong>{{ $deviceType['name'] }}</strong>
                    </div>
                </div>

                <div class="detail-item">
                    <label>Ícone:</label>
                    <div class="detail-value">
                        <span class="device-icon-large">{{ $deviceType['icon'] ?? '📱' }}</span>
                        <small>{{ $deviceType['icon'] ?? 'Nenhum ícone' }}</small>
                    </div>
                </div>

                <div class="detail-item">
                    <label>Status:</label>
                    <div class="detail-value">
                        <span class="badge {{ $deviceType['is_active'] ? 'badge-active' : 'badge-inactive' }}">
                            {{ $deviceType['is_active'] ? '✅ Ativo' : '❌ Inativo' }}
                        </span>
                    </div>
                </div>

                <div class="detail-item">
                    <label>Descrição:</label>
                    <div class="detail-value">
                        {{ $deviceType['description'] ?? 'Nenhuma descrição disponível' }}
                    </div>
                </div>

                <div class="detail-item">
                    <label>Criado em:</label>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($deviceType['created_at'])->format('d/m/Y H:i') }}
                    </div>
                </div>

                <div class="detail-item">
                    <label>Última atualização:</label>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($deviceType['updated_at'])->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="dashboard-card">
            <h2>📊 Estatísticas de Uso</h2>
            @if(isset($deviceType['stats']))
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📡</div>
                        <div class="stat-info">
                            <div class="stat-value">{{ $deviceType['stats']['total_topics'] ?? 0 }}</div>
                            <div class="stat-label">Tópicos Relacionados</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <div class="stat-value">{{ $deviceType['stats']['active_topics'] ?? 0 }}</div>
                            <div class="stat-label">Tópicos Ativos</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-info">
                            <div class="stat-value">
                                @if($deviceType['stats']['last_activity'])
                                    {{ \Carbon\Carbon::parse($deviceType['stats']['last_activity'])->format('d/m/Y') }}
                                @else
                                    Nenhuma
                                @endif
                            </div>
                            <div class="stat-label">Última Atividade</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="no-stats">
                    <p>📊 Estatísticas não disponíveis</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Especificações Técnicas -->
    @if($deviceType['specifications'])
        <div class="dashboard-card">
            <h2>⚙️ Especificações Técnicas</h2>
            <div class="specifications-container">
                @if(is_array($deviceType['specifications']))
                    <div class="specs-grid">
                        @foreach($deviceType['specifications'] as $key => $value)
                            <div class="spec-item">
                                <label>{{ ucfirst(str_replace('_', ' ', $key)) }}:</label>
                                <div class="spec-value">{{ $value }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="specs-raw">
                        <pre><code>{{ json_encode($deviceType['specifications'], JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Ações -->
    <div class="dashboard-card">
        <h2>⚙️ Ações</h2>
        <div class="actions-grid">
            <a href="{{ route('device-types.edit', $deviceType['id']) }}" class="action-button edit">
                <div class="action-icon">✏️</div>
                <div class="action-text">
                    <strong>Editar Tipo</strong>
                    <small>Alterar informações do tipo</small>
                </div>
            </a>

            <form method="POST" action="{{ route('device-types.toggle-status', $deviceType['id']) }}" 
                  style="margin: 0;">
                @csrf
                @method('PATCH')
                <button type="submit" class="action-button toggle" 
                        onclick="return confirm('Tem certeza que deseja {{ $deviceType['is_active'] ? 'desativar' : 'ativar' }} este tipo?')">
                    <div class="action-icon">{{ $deviceType['is_active'] ? '⏸️' : '▶️' }}</div>
                    <div class="action-text">
                        <strong>{{ $deviceType['is_active'] ? 'Desativar' : 'Ativar' }} Tipo</strong>
                        <small>{{ $deviceType['is_active'] ? 'Tornar indisponível' : 'Tornar disponível' }}</small>
                    </div>
                </button>
            </form>

            @if((!isset($deviceType['stats']['total_topics']) || $deviceType['stats']['total_topics'] == 0))
                <form method="POST" action="{{ route('device-types.destroy', $deviceType['id']) }}" 
                      onsubmit="return confirm('Tem certeza que deseja deletar este tipo de dispositivo?')" 
                      style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-button delete">
                        <div class="action-icon">🗑️</div>
                        <div class="action-text">
                            <strong>Deletar Tipo</strong>
                            <small>Remover permanentemente</small>
                        </div>
                    </button>
                </form>
            @else
                <div class="action-button disabled">
                    <div class="action-icon">🚫</div>
                    <div class="action-text">
                        <strong>Não é possível deletar</strong>
                        <small>Tipo possui tópicos associados</small>
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
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

.device-icon-large {
    font-size: 2rem;
    display: block;
    margin-bottom: 0.25rem;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-block;
}

.badge-active { 
    background-color: #dcfce7; 
    color: #166534; 
}

.badge-inactive { 
    background-color: #fde2e8; 
    color: #991b1b; 
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background-color: #f8fafc;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
}

.stat-icon {
    font-size: 2rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2563eb;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.no-stats {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.specifications-container {
    margin-top: 1rem;
}

.specs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.spec-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0.75rem;
    background-color: #f8fafc;
    border-radius: 0.375rem;
    border: 1px solid #e2e8f0;
}

.spec-item label {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.spec-value {
    color: #6b7280;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    background-color: #ffffff;
    padding: 0.5rem;
    border-radius: 0.25rem;
    border: 1px solid #e5e7eb;
}

.specs-raw {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
}

.specs-raw pre {
    margin: 0;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

.action-button.toggle {
    background-color: #f0fdf4;
    border: 1px solid #22c55e;
    color: #166534;
}

.action-button.toggle:hover {
    background-color: #dcfce7;
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
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .specs-grid {
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
}
</style>
@endsection 
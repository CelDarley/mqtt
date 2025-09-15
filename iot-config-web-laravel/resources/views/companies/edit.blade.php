@extends('layouts.app')

@section('title', 'Editar Empresa')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">
                ✏️ Editar Empresa
            </h1>
            <p class="page-description">
                Edite as informações da empresa: {{ $company['name'] }}
            </p>
        </div>
        <div class="page-actions">
            <a href="{{ route('companies.index') }}" class="btn btn-secondary">
                ← Voltar à Lista
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-error">
            ❌ {{ session('error') }}
        </div>
    @endif

    <div class="form-container">
        <form method="POST" action="{{ route('companies.update', $company['id']) }}" class="company-form">
            @csrf
            @method('PUT')
            
            <div class="form-section">
                <h2 class="section-title">📝 Informações da Empresa</h2>
                
                <div class="form-group">
                    <label for="name" class="form-label">
                        Nome da Empresa <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-input"
                           placeholder="Digite o nome da empresa"
                           value="{{ old('name', $company['name']) }}"
                           required>
                    <small class="form-help">
                        💡 O nome deve ser único no sistema
                    </small>
                </div>
                
                <div class="info-section">
                    <h3 class="info-title">ℹ️ Informações Adicionais</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>🆔 ID:</label>
                            <span>#{{ $company['id'] }}</span>
                        </div>
                        <div class="info-item">
                            <label>📅 Criada em:</label>
                            <span>{{ \Carbon\Carbon::parse($company['created_at'])->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="info-item">
                            <label>🔄 Atualizada em:</label>
                            <span>{{ \Carbon\Carbon::parse($company['updated_at'])->format('d/m/Y H:i') }}</span>
                        </div>
                        @if(isset($company['departments']) && count($company['departments']) > 0)
                            <div class="info-item departments-info">
                                <label>🏛️ Departamentos ({{ count($company['departments']) }}):</label>
                                <div class="departments-list">
                                    @foreach($company['departments'] as $department)
                                        <div class="department-tag">
                                            📁 {{ $department['name'] }}
                                            <small>(Nível {{ $department['nivel_hierarquico'] }})</small>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    💾 Salvar Alterações
                </button>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
                <a href="{{ route('companies.show', $company['id']) }}" class="btn btn-info">
                    👁️ Ver Detalhes
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.container {
    max-width: 800px;
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

.btn-secondary:hover {
    background-color: #4b5563;
}

.btn-info {
    background-color: #0ea5e9;
    color: white;
}

.btn-info:hover {
    background-color: #0284c7;
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.form-container {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.company-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

.required {
    color: #ef4444;
}

.form-input {
    padding: 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-help {
    color: #6b7280;
    font-size: 0.875rem;
}

/* Informações adicionais */
.info-section {
    background-color: #f9fafb;
    border-radius: 0.5rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
}

.info-title {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 1rem 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item label {
    font-weight: 500;
    color: #6b7280;
    font-size: 0.875rem;
}

.info-item span {
    color: #1f2937;
    font-weight: 500;
}

.departments-info {
    grid-column: 1 / -1;
}

.departments-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.department-tag {
    background-color: #dbeafe;
    color: #1e40af;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-left: 3px solid #3b82f6;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Validação do formulário
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.company-form');
    const nameInput = document.getElementById('name');
    
    form.addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        
        if (!name) {
            e.preventDefault();
            alert('❌ Por favor, digite o nome da empresa');
            nameInput.focus();
            return false;
        }
        
        if (name.length < 2) {
            e.preventDefault();
            alert('❌ O nome da empresa deve ter pelo menos 2 caracteres');
            nameInput.focus();
            return false;
        }
        
        if (name.length > 255) {
            e.preventDefault();
            alert('❌ O nome da empresa não pode ter mais de 255 caracteres');
            nameInput.focus();
            return false;
        }
        
        // Mostrar indicador de carregamento
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Salvando...';
    });
    
    // Remover espaços extras ao digitar
    nameInput.addEventListener('blur', function() {
        this.value = this.value.trim();
    });
});
</script>
@endsection 
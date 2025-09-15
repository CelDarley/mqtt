@extends('layouts.app')

@section('title', 'Novo Departamento')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <div class="page-header-content">
            <h1 style="color: #eeeeee;">🏢 Novo Departamento</h1>
            <p>Criar nova unidade organizacional</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('departments.index') }}" class="btn btn-outline">
                ← Voltar
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="dashboard-card">
        <h2>📝 Informações do Departamento</h2>
        
        <form method="POST" action="{{ route('departments.store') }}" class="form">
            @csrf
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nome do Departamento *</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="form-control" 
                           value="{{ old('name') }}" 
                           required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="id_comp">Empresa *</label>
                    <select name="id_comp" id="id_comp" class="form-control" required>
                        <option value="">Selecione uma empresa</option>
                        @foreach($companies ?? [] as $company)
                            <option value="{{ $company['id'] }}" {{ old('id_comp') == $company['id'] ? 'selected' : '' }}>
                                {{ $company['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_comp')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nivel_hierarquico">Nível Hierárquico *</label>
                    <select name="nivel_hierarquico" id="nivel_hierarquico" class="form-control" required>
                        <option value="">Selecione o nível</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ old('nivel_hierarquico') == $i ? 'selected' : '' }}>
                                Nível {{ $i }} {{ $i == 1 ? '(Raiz)' : '' }}
                            </option>
                        @endfor
                    </select>
                    @error('nivel_hierarquico')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <small class="help-text">Nível 1 = Departamento raiz, sem superior</small>
                </div>

                <div class="form-group">
                    <label for="id_unid_up">Unidade Superior</label>
                    <select name="id_unid_up" id="id_unid_up" class="form-control">
                        <option value="">Nenhuma (Departamento raiz)</option>
                        @foreach($departments ?? [] as $department)
                            <option value="{{ $department['id'] }}" {{ old('id_unid_up') == $department['id'] ? 'selected' : '' }}>
                                {{ $department['name'] }} (Nível {{ $department['nivel_hierarquico'] }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_unid_up')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <small class="help-text">Apenas para departamentos de nível superior a 1</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    💾 Salvar Departamento
                </button>
                <a href="{{ route('departments.index') }}" class="btn btn-outline">
                    Cancelar
                </a>
            </div>
        </form>
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

.form {
    max-width: 800px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-control {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.help-text {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.error-message {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #ef4444;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
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

.btn-primary:hover {
    background-color: #1d4ed8;
}

.btn-outline {
    background-color: white;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background-color: #f9fafb;
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.alert-error {
    background-color: #fde2e8;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nivelSelect = document.getElementById('nivel_hierarquico');
    const unidadeSupSelect = document.getElementById('id_unid_up');
    
    nivelSelect.addEventListener('change', function() {
        const nivel = parseInt(this.value);
        
        if (nivel === 1) {
            unidadeSupSelect.value = '';
            unidadeSupSelect.disabled = true;
        } else {
            unidadeSupSelect.disabled = false;
        }
    });
    
    // Aplicar a lógica no carregamento da página
    if (nivelSelect.value === '1') {
        unidadeSupSelect.disabled = true;
    }
});
</script>
@endsection 
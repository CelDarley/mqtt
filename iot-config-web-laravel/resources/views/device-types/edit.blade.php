@extends('layouts.app')

@section('title', 'Editar Tipo de Dispositivo')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <div class="page-header-content">
            <h1 style="color: #eeeeee;">📱 Editar Tipo de Dispositivo</h1>
            <p>Atualizar categoria de dispositivo IoT</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('device-types.index') }}" class="btn btn-outline">
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
        <h2>📝 Informações do Tipo</h2>
        
        <form method="POST" action="{{ route('device-types.update', $deviceType['id']) }}" class="form">
            @csrf
            @method('PUT')
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nome do Tipo *</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="form-control" 
                           value="{{ old('name', $deviceType['name']) }}" 
                           required
                           placeholder="Ex: Sensor de Temperatura">
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="icon">Ícone</label>
                    <select name="icon" id="icon" class="form-control">
                        <option value="">Selecione um ícone</option>
                        <option value="📱" {{ old('icon', $deviceType['icon']) == '📱' ? 'selected' : '' }}>📱 Dispositivo Genérico</option>
                        <option value="🌡️" {{ old('icon', $deviceType['icon']) == '🌡️' ? 'selected' : '' }}>🌡️ Sensor de Temperatura</option>
                        <option value="💧" {{ old('icon', $deviceType['icon']) == '💧' ? 'selected' : '' }}>💧 Sensor de Umidade</option>
                        <option value="💡" {{ old('icon', $deviceType['icon']) == '💡' ? 'selected' : '' }}>💡 Lâmpada/LED</option>
                        <option value="🔌" {{ old('icon', $deviceType['icon']) == '🔌' ? 'selected' : '' }}>🔌 Relé/Tomada</option>
                        <option value="🚪" {{ old('icon', $deviceType['icon']) == '🚪' ? 'selected' : '' }}>🚪 Sensor de Porta</option>
                        <option value="🔊" {{ old('icon', $deviceType['icon']) == '🔊' ? 'selected' : '' }}>🔊 Buzzer/Alarme</option>
                        <option value="📹" {{ old('icon', $deviceType['icon']) == '📹' ? 'selected' : '' }}>📹 Câmera</option>
                        <option value="⚡" {{ old('icon', $deviceType['icon']) == '⚡' ? 'selected' : '' }}>⚡ Sensor de Energia</option>
                        <option value="🌪️" {{ old('icon', $deviceType['icon']) == '🌪️' ? 'selected' : '' }}>🌪️ Sensor de Movimento</option>
                    </select>
                    @error('icon')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="description">Descrição</label>
                    <textarea name="description" 
                              id="description" 
                              class="form-control" 
                              rows="3"
                              placeholder="Descreva o tipo de dispositivo...">{{ old('description', $deviceType['description']) }}</textarea>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="specifications">Especificações Técnicas (JSON)</label>
                    <textarea name="specifications" 
                              id="specifications" 
                              class="form-control" 
                              rows="5"
                              placeholder='{"voltagem": "3.3V", "protocolo": "WiFi", "range": "10m"}'>{{ old('specifications', $deviceType['specifications'] ? json_encode($deviceType['specifications'], JSON_PRETTY_PRINT) : '') }}</textarea>
                    @error('specifications')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <small class="help-text">Digite as especificações em formato JSON válido (opcional)</small>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $deviceType['is_active']) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        Tipo ativo
                    </label>
                    <small class="help-text">Tipos ativos podem ser usados em novos dispositivos</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    💾 Atualizar Tipo
                </button>
                <a href="{{ route('device-types.index') }}" class="btn btn-outline">
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

.form-group.full-width {
    grid-column: 1 / -1;
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

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 600 !important;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 0.5rem;
    width: 1.25rem;
    height: 1.25rem;
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
    const specificationsTextarea = document.getElementById('specifications');
    
    specificationsTextarea.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value && value !== '') {
            try {
                JSON.parse(value);
                this.style.borderColor = '#10b981';
                this.style.backgroundColor = '#f0fdf4';
            } catch (e) {
                this.style.borderColor = '#ef4444';
                this.style.backgroundColor = '#fef2f2';
            }
        } else {
            this.style.borderColor = '#d1d5db';
            this.style.backgroundColor = '#ffffff';
        }
    });
});
</script>
@endsection 
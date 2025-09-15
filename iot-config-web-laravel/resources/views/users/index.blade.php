@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <div class="page-header-content">
            <h1 style="color: #eeeeee;">👥 Usuários</h1>
            <p>Gerenciar usuários do sistema</p>
        </div>
        <div class="page-header-actions">
            <a href="#" class="btn btn-primary" onclick="alert('🚧 Funcionalidade em desenvolvimento')">
                ➕ Novo Usuário
            </a>
        </div>
    </div>

    <div class="dashboard-card">
        <h2>📋 Lista de Usuários</h2>
        
        <div class="filters-section">
            <div class="search-box">
                <input type="text" 
                       placeholder="🔍 Buscar usuários..." 
                       class="search-input"
                       onkeyup="filterUsers(this.value)">
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="usersTable">
                <thead>
                    <tr>
                        <th class="id-column">ID</th>
                        <th class="name-column">Nome</th>
                        <th class="email-column">Email</th>
                        <th class="role-column">Perfil</th>
                        <th class="status-column">Status</th>
                        <th class="date-column">Criado em</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dados de exemplo - substituir por dados reais quando implementar backend -->
                    <tr class="user-row">
                        <td class="id-column">
                            <span class="entity-id">#1</span>
                        </td>
                        <td class="name-column">
                            <div class="entity-name">
                                <span class="name">Administrador</span>
                            </div>
                        </td>
                        <td class="email-column">
                            <span class="email">admin@sistema.com</span>
                        </td>
                        <td class="role-column">
                            <span class="badge badge-admin">🔧 Admin</span>
                        </td>
                        <td class="status-column">
                            <span class="status-badge active">✅ Ativo</span>
                        </td>
                        <td class="date-column">
                            <span class="date">2025-01-01</span>
                        </td>
                        <td class="actions-column">
                            <div class="actions-menu">
                                <button class="btn-action view" onclick="viewUser(1)" title="Visualizar usuário">
                                    👁️
                                </button>
                                <button class="btn-action edit" onclick="editUser(1)" title="Editar usuário">
                                    ✏️
                                </button>
                                <button class="btn-action delete" onclick="deleteUser(1, 'Administrador')" title="Deletar usuário">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="user-row">
                        <td class="id-column">
                            <span class="entity-id">#2</span>
                        </td>
                        <td class="name-column">
                            <div class="entity-name">
                                <span class="name">João Silva</span>
                            </div>
                        </td>
                        <td class="email-column">
                            <span class="email">joao.silva@empresa.com</span>
                        </td>
                        <td class="role-column">
                            <span class="badge badge-operator">👨‍💻 Operador</span>
                        </td>
                        <td class="status-column">
                            <span class="status-badge active">✅ Ativo</span>
                        </td>
                        <td class="date-column">
                            <span class="date">2025-01-15</span>
                        </td>
                        <td class="actions-column">
                            <div class="actions-menu">
                                <button class="btn-action view" onclick="viewUser(2)" title="Visualizar usuário">
                                    👁️
                                </button>
                                <button class="btn-action edit" onclick="editUser(2)" title="Editar usuário">
                                    ✏️
                                </button>
                                <button class="btn-action delete" onclick="deleteUser(2, 'João Silva')" title="Deletar usuário">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="user-row">
                        <td class="id-column">
                            <span class="entity-id">#3</span>
                        </td>
                        <td class="name-column">
                            <div class="entity-name">
                                <span class="name">Maria Santos</span>
                            </div>
                        </td>
                        <td class="email-column">
                            <span class="email">maria.santos@empresa.com</span>
                        </td>
                        <td class="role-column">
                            <span class="badge badge-viewer">👀 Visualizador</span>
                        </td>
                        <td class="status-column">
                            <span class="status-badge inactive">❌ Inativo</span>
                        </td>
                        <td class="date-column">
                            <span class="date">2025-02-01</span>
                        </td>
                        <td class="actions-column">
                            <div class="actions-menu">
                                <button class="btn-action view" onclick="viewUser(3)" title="Visualizar usuário">
                                    👁️
                                </button>
                                <button class="btn-action edit" onclick="editUser(3)" title="Editar usuário">
                                    ✏️
                                </button>
                                <button class="btn-action delete" onclick="deleteUser(3, 'Maria Santos')" title="Deletar usuário">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="table-info">
                <span>📊 Mostrando <strong>3</strong> usuários</span>
            </div>
            <div class="pagination">
                <span class="pagination-info">Página 1 de 1</span>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualização de detalhes -->
<div id="userModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Detalhes do Usuário</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalContent">
            <!-- Conteúdo será preenchido via JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Fechar</button>
        </div>
    </div>
</div>

<script>
function filterUsers(searchTerm) {
    const table = document.getElementById('usersTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const name = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const role = row.cells[3].textContent.toLowerCase();
        
        if (name.includes(searchTerm.toLowerCase()) || 
            email.includes(searchTerm.toLowerCase()) ||
            role.includes(searchTerm.toLowerCase())) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function viewUser(userId) {
    const modal = document.getElementById('userModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    
    modalTitle.textContent = `👥 Detalhes do Usuário #${userId}`;
    modalContent.innerHTML = `
        <div class="user-details">
            <p><strong>🚧 Funcionalidade em desenvolvimento</strong></p>
            <p>Esta tela mostrará os detalhes completos do usuário quando o backend for implementado.</p>
            <div class="development-note">
                <h4>📋 Informações que serão exibidas:</h4>
                <ul>
                    <li>👤 Dados pessoais completos</li>
                    <li>🏢 Empresa/Departamento</li>
                    <li>🔐 Permissões e roles</li>
                    <li>📊 Histórico de atividades</li>
                    <li>📅 Logs de acesso</li>
                </ul>
            </div>
        </div>
    `;
    modal.style.display = 'block';
}

function editUser(userId) {
    alert(`🚧 Edição de usuário #${userId} em desenvolvimento.\n\nEsta funcionalidade será implementada quando o CRUD de usuários estiver completo.`);
}

function deleteUser(userId, userName) {
    if (confirm(`🗑️ Tem certeza que deseja deletar o usuário "${userName}"?\n\n⚠️ Esta ação não pode ser desfeita.`)) {
        alert(`🚧 Exclusão de usuário em desenvolvimento.\n\nUsuário "${userName}" seria deletado quando o backend estiver implementado.`);
    }
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Fechar modal clicando fora dele
window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

<style>
.admin-dashboard {
    padding: 20px;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    min-height: 100vh;
    color: #ffffff;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px 0;
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.page-header-content h1 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: bold;
}

.page-header-content p {
    margin: 5px 0 0 0;
    opacity: 0.8;
    font-size: 1.1rem;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.dashboard-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    color: #333;
}

.dashboard-card h2 {
    margin: 0 0 25px 0;
    color: #2c3e50;
    font-size: 1.8rem;
}

.filters-section {
    margin-bottom: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.search-input {
    width: 100%;
    max-width: 400px;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.table-container {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.data-table th {
    background: linear-gradient(135deg, #495057, #6c757d);
    color: white;
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    border: none;
    position: sticky;
    top: 0;
    z-index: 1;
}

.data-table td {
    padding: 15px 12px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.data-table tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.entity-id {
    font-weight: bold;
    color: #6c757d;
    font-family: 'Courier New', monospace;
}

.entity-name .name {
    font-weight: 600;
    color: #2c3e50;
}

.email {
    color: #007bff;
    font-family: 'Courier New', monospace;
}

.badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-admin {
    background: #dc3545;
    color: white;
}

.badge-operator {
    background: #007bff;
    color: white;
}

.badge-viewer {
    background: #28a745;
    color: white;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.date {
    color: #6c757d;
    font-family: 'Courier New', monospace;
}

.actions-menu {
    display: flex;
    gap: 8px;
}

.btn-action {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s ease;
}

.btn-action.view {
    background: #e3f2fd;
    color: #1976d2;
}

.btn-action.view:hover {
    background: #1976d2;
    color: white;
}

.btn-action.edit {
    background: #fff3e0;
    color: #f57c00;
}

.btn-action.edit:hover {
    background: #f57c00;
    color: white;
}

.btn-action.delete {
    background: #ffebee;
    color: #d32f2f;
}

.btn-action.delete:hover {
    background: #d32f2f;
    color: white;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 0 0 10px 10px;
    margin-top: -1px;
}

.table-info {
    color: #6c757d;
    font-weight: 500;
}

.pagination-info {
    color: #6c757d;
    font-weight: 500;
}

/* Modal */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 10px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.3s ease;
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #495057, #6c757d);
    color: white;
    border-radius: 10px 10px 0 0;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.4rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: white;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid #e9ecef;
    text-align: right;
    background: #f8f9fa;
    border-radius: 0 0 10px 10px;
}

.user-details {
    color: #333;
}

.development-note {
    background: #e3f2fd;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    border-left: 4px solid #2196f3;
}

.development-note h4 {
    margin: 0 0 15px 0;
    color: #1976d2;
}

.development-note ul {
    margin: 0;
    padding-left: 20px;
}

.development-note li {
    margin-bottom: 5px;
    color: #555;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Responsividade */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .filters-section {
        padding: 15px;
    }
    
    .search-input {
        max-width: 100%;
    }
    
    .data-table {
        font-size: 0.875rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 10px 8px;
    }
    
    .actions-menu {
        flex-direction: column;
        gap: 4px;
    }
    
    .table-footer {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}
</style>
@endsection 
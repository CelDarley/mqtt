<!DOCTYPE html>
<html>
<head>
    <title>Teste Companies</title>
</head>
<body>
    <h1>Debug - Lista de Empresas</h1>
    
    <p>Total de empresas: {{ count($companies) }}</p>
    
    @if(count($companies) > 0)
        <h2>Primeira empresa:</h2>
        <pre>{{ print_r($companies[0], true) }}</pre>
        
        <h2>Tipo da primeira empresa:</h2>
        <p>{{ gettype($companies[0]) }}</p>
        
        <h2>Tentativa de acesso aos dados:</h2>
        @php $company = $companies[0]; @endphp
        
        @if(is_array($company))
            <p>✅ É array:</p>
            <ul>
                <li>ID: {{ $company['id'] ?? 'N/A' }}</li>
                <li>Nome: {{ $company['name'] ?? 'N/A' }}</li>
                <li>Departamentos: {{ $company['departments_count'] ?? 'N/A' }}</li>
            </ul>
        @else
            <p>❌ Não é array (é {{ gettype($company) }})</p>
            @if(is_object($company))
                <ul>
                    <li>ID: {{ $company->id ?? 'N/A' }}</li>
                    <li>Nome: {{ $company->name ?? 'N/A' }}</li>
                    <li>Departamentos: {{ $company->departments_count ?? 'N/A' }}</li>
                </ul>
            @endif
        @endif
        
        <h2>Lista de todas as empresas:</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Departamentos</th>
                <th>Tipo</th>
            </tr>
            @foreach($companies as $index => $company)
                <tr>
                    <td>{{ is_array($company) ? ($company['id'] ?? 'N/A') : (is_object($company) ? ($company->id ?? 'N/A') : 'ERRO') }}</td>
                    <td>{{ is_array($company) ? ($company['name'] ?? 'N/A') : (is_object($company) ? ($company->name ?? 'N/A') : 'ERRO') }}</td>
                    <td>{{ is_array($company) ? ($company['departments_count'] ?? 'N/A') : (is_object($company) ? ($company->departments_count ?? 'N/A') : 'ERRO') }}</td>
                    <td>{{ gettype($company) }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <p>Nenhuma empresa encontrada</p>
    @endif
</body>
</html> 
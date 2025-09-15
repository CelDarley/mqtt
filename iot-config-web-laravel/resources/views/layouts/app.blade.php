<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IoT Config Web') }} - @yield('title', 'Sistema IoT')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css'])
    
    <!-- Additional styles -->
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div id="app">
        @if(auth()->check())
            <!-- Navigation -->
            <nav class="navbar">
                <div class="navbar-container">
                    <div class="navbar-content">
                        <div class="navbar-brand">
                            <h1 class="navbar-title">🔌 IoT Config</h1>
                        </div>
                        <div class="navbar-menu">
                            <a href="{{ route('dashboard') }}" class="navbar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('companies.index') }}" class="navbar-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                                🏢 Empresas
                            </a>
                            <a href="{{ route('departments.index') }}" class="navbar-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                                Departamentos
                            </a>
                            <a href="{{ route('device-types.index') }}" class="navbar-link {{ request()->routeIs('device-types.*') ? 'active' : '' }}">
                                Tipos de Dispositivo
                            </a>
                            <a href="{{ route('users.index') }}" class="navbar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                Usuários
                            </a>
                            <a href="{{ route('topics.index') }}" class="navbar-link {{ request()->routeIs('topics.*') ? 'active' : '' }}">
                                Tópicos MQTT
                            </a>
                            <a href="{{ route('ota-updates.index') }}" class="navbar-link {{ request()->routeIs('ota-updates.*') ? 'active' : '' }}">
                                📊 Logs OTA
                            </a>
                        </div>
                        <div class="navbar-user">
                            <span class="navbar-user-name">Olá, {{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="navbar-logout">
                                @csrf
                                <button type="submit" class="navbar-logout-btn">
                                    Sair
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>


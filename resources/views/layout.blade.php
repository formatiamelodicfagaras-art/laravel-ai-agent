<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Style AI Agent')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .navbar-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .navbar-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .navbar-links a:hover {
            background: rgba(255,255,255,0.1);
        }

        .navbar-links a.active {
            background: rgba(255,255,255,0.2);
        }

        .navbar-links form {
            margin: 0;
        }

        .navbar-links button {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }

        .navbar-links button:hover {
            background: #c0392b;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(session('authenticated'))
    <nav class="navbar">
        <h1>Style AI Agent</h1>
        <div class="navbar-links">
            <a href="{{ url('/upload') }}" class="{{ request()->is('upload') ? 'active' : '' }}">Baza de Date Contabilitate</a>
            <a href="{{ url('/preturi-2026') }}" class="{{ request()->is('preturi-2026') ? 'active' : '' }}">Lista Prețuri 2026</a>
            <a href="{{ url('/terase') }}" class="{{ request()->is('terase') ? 'active' : '' }}">Calculație Terase</a>
            <a href="{{ url('/agent') }}" class="{{ request()->is('agent') ? 'active' : '' }}">AI Agent</a>
            <form action="{{ url('/logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </nav>
    @endif

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>

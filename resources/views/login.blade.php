@extends('layout')

@section('title', 'Login - Style AI Agent')

@section('styles')
<style>
    .login-container {
        max-width: 400px;
        margin: 4rem auto;
    }

    .login-card {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .login-card h2 {
        text-align: center;
        margin-bottom: 2rem;
        color: #2c3e50;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #555;
    }

    .form-group input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .form-group input:focus {
        outline: none;
        border-color: #3498db;
    }

    .btn-login {
        width: 100%;
        padding: 0.75rem;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-login:hover {
        background: #2980b9;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="login-card">
        <h2>Style AI Agent</h2>
        <form action="{{ url('/login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Parola</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</div>
@endsection

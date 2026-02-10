<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        // If already authenticated, redirect to upload page
        if (session('authenticated')) {
            return redirect('/upload');
        }

        return view('login');
    }

    /**
     * Process login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username-ul este obligatoriu.',
            'password.required' => 'Parola este obligatorie.',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Get credentials from .env
        $authUser = env('AUTH_USER', 'admin');
        $authPass = env('AUTH_PASS', 'parola123');

        if ($username === $authUser && $password === $authPass) {
            // Set session
            session(['authenticated' => true]);
            return redirect('/upload')->with('success', 'Autentificare reușită!');
        }

        return back()->withErrors([
            'credentials' => 'Username sau parolă incorectă.',
        ])->withInput($request->only('username'));
    }

    /**
     * Process logout
     */
    public function logout()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Ați fost deconectat cu succes.');
    }
}

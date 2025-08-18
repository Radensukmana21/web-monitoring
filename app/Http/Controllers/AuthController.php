<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Generate token secara manual (contoh: random 64 karakter)
            $token = bin2hex(random_bytes(32)); // atau Str::random(64) jika pakai helper Laravel

            // Simpan ke kolom remember_token
            $user->remember_token = $token;
            $user->save();

            // Jika ingin dikembalikan (opsional)
            session(['token' => $token]);

            return redirect()->intended('/')
                             ->with('welcome', 'Selamat Datang, ' . $user->name);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

    if ($user) {
        // Hapus token dari kolom remember_token
        $user->remember_token = null;
        $user->save();
    }
    
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}


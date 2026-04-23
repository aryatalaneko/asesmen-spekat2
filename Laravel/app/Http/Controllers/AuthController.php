<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan form login.
     * Jika pengguna SUDAH login, langsung redirect ke dashboard sesuai role.
     * Ini memastikan tombol Back browser tidak membawa kembali ke form login.
     */
    public function showLoginForm()
    {
        // Jika sudah terautentikasi, arahkan langsung ke dashboard
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        // Kirim header no-cache agar browser TIDAK menyimpan halaman login
        // sehingga tombol Back selalu memuat ulang dan bukan dari cache
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function login(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string'],
            'password' => ['required'],
        ], [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Cari user berdasarkan nama
        $user = User::where('name', $request->name)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'name' => 'Nama atau password yang Anda masukkan salah.',
            ])->onlyInput('name');
        }

        // Login & regenerate session untuk mencegah session fixation
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectByRole($user->role);
    }

    /**
     * Helper: redirect ke dashboard berdasarkan role.
     */
    private function redirectByRole(string $role)
    {
        return match($role) {
            'admin' => redirect()->intended(route('admin.users.index')),
            'guru'  => redirect()->intended(route('guru.questions.index')),
            default => redirect()->intended(route('siswa.exams.index')),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

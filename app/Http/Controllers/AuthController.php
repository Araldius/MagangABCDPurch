<?php
 
namespace App\Http\Controllers;
 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
 
class AuthController extends Controller
{
    public function showLogin()    { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }
 
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);
 
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }
 
        return back()->withErrors(['email' => 'Email atau password tidak cocok.'])->onlyInput('email');
    }
 
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'department' => ['nullable', 'string', 'max:255'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ]);
 
        $role = 'requester';
        if ($data['email'] === 'admin@dkj.com') {
            $role = 'admin';
        }

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'department' => $data['department'] ?? null,
            'password'   => Hash::make($data['password']),
            'role'       => $role,
        ]);
 
        Auth::login($user);
        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil!');
    }
 
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
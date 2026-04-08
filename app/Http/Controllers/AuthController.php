<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'credential' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $credential = trim($validated['credential']);

        $success = Auth::attempt([
            'username' => $credential,
            'password' => $validated['password'],
            'status_aktif' => 1,
        ]);

        if (! $success) {
            return back()
                ->withInput($request->only('credential'))
                ->withErrors([
                    'credential' => 'Incorrect email or password',
                ]);
        }

        $request->session()->regenerate();

        $this->log((int) Auth::id(), 'Login ke sistem');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $idUser = (int) (Auth::id() ?? 0);

        if ($idUser > 0) {
            $this->log($idUser, 'Logout dari sistem');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function log(int $idUser, string $aktivitas): void
    {
        LogAktivitas::query()->create([
            'id_user' => $idUser,
            'aktivitas' => $aktivitas,
            'waktu' => now(),
        ]);
    }
}

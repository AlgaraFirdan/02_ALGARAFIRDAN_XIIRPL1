<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where('nama', 'like', '%' . $q . '%')
                    ->orWhere('username', 'like', '%' . $q . '%');
            })
            ->orderBy('id_user')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,petugas,owner'],
            'status_aktif' => ['required', 'in:0,1'],
        ]);

        $user = User::query()->create([
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status_aktif' => (int) $validated['status_aktif'],
        ]);

        $this->log((int) $request->user()->id_user, 'Menambah user #' . $user->id_user . ' (' . $user->username . ')');

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'item' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id_user . ',id_user'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:admin,petugas,owner'],
            'status_aktif' => ['required', 'in:0,1'],
        ]);

        $payload = [
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'role' => $validated['role'],
            'status_aktif' => (int) $validated['status_aktif'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        $this->log((int) $request->user()->id_user, 'Memperbarui user #' . $user->id_user . ' (' . $user->username . ')');

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id_user === (int) $user->id_user) {
            return back()->withErrors(['q' => 'Akun sendiri tidak bisa dihapus.']);
        }

        $username = $user->username;
        $userId = $user->id_user;
        $user->delete();

        $this->log((int) $request->user()->id_user, 'Menghapus user #' . $userId . ' (' . $username . ')');

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
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

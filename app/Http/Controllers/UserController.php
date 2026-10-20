<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create', ['user' => new User(['role' => 'staff'])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // password kosong = tidak diganti
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // admin tidak boleh mengubah role akunnya sendiri (supaya tidak mengunci diri)
        if ($user->id === $request->user()->id) {
            unset($data['role']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diubah.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('users.index')->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        if ($user->sales()->exists() || $user->purchases()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Pengguna ini sudah punya transaksi, tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}

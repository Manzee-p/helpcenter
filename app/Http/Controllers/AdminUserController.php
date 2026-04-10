<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Tampilkan daftar user dengan filter + pagination.
     */
    public function index(Request $request)
    {
        $query = User::query()->withTrashed(false); // exclude soft deleted

        // ── Filter Role ──
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // ── Search ──
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name',  'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $users = $query->paginate(10)->withQueryString();

        // ── Stats (dari semua user, tidak terpengaruh filter) ──
        $stats = [
            'total'  => User::count(),
            'admin'  => User::where('role', 'admin')->count(),
            'vendor' => User::where('role', 'vendor')->count(),
            'client' => User::where('role', 'client')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,vendor,client',
        ], [
            'email.unique'    => 'Email sudah terdaftar.',
            'password.min'    => 'Password minimal 8 karakter.',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'is_active'=> true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Update data user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role'     => 'required|in:admin,vendor,client',
        ], [
            'email.unique' => 'Email sudah digunakan user lain.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role'  => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Toggle aktif / nonaktif user.
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // Jangan izinkan admin menonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $label = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil {$label}.");
    }

    /**
     * Hapus user (soft delete jika model mendukung, hard delete jika tidak).
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Jangan izinkan admin menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil dihapus.");
    }
}
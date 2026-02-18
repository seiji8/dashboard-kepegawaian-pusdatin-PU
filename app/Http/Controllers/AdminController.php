<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Hash;

use App\Models\Pegawai; // Tambah Model Pegawai

class AdminController extends Controller
{
    /**
     * Display a listing of admins with search
     */
    public function index(Request $request)
    {
        // 1. Authorize: Hanya Super Admin yang boleh lihat halaman ini
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki hak akses (Super Admin only).');
        }

        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%") // Ganti nip jadi username
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Order by name
        $query->orderBy('nama_lengkap', 'asc');

        // Pagination
        $admins = $query->paginate(10);

        // AMBIL DATA PEGAWAI YG BELUM JADI USER (CANDIDAT ADMIN)
        // Logic: Ambil Pegawai yang NIP-nya TIDAK ADA di tabel users kolom username
        $existingUsernames = User::pluck('username')->toArray();
        $candidates = Pegawai::whereNotIn('nip', $existingUsernames)
                             ->orderBy('nama', 'asc')
                             ->get(['nip', 'nama', 'email']);

        return view('daftar_admin.index', compact('admins', 'candidates'));
    }

    /**
     * Store new admin from employee data
     */
    public function store(Request $request)
    {
        // 1. Authorize: Hanya Super Admin
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses (Super Admin only).'
            ], 403);
        }

        // 2. Validate
        $request->validate([
            'nip_pegawai' => 'required|exists:pegawai,nip|unique:users,username',
        ]);

        try {
            // 3. Ambil Data Pegawai
            $pegawai = Pegawai::where('nip', $request->nip_pegawai)->firstOrFail();

            // 4. Create User
            // Password Default = NIP Pegawai
            $user = User::create([
                'username'      => $pegawai->nip,
                'nama_lengkap'  => $pegawai->nama,
                'email'         => $pegawai->email, // Bisa null jika pegawai gak punya email
                'password'      => Hash::make($pegawai->nip), 
                'role'          => 'admin_pegawai', // Default role
            ]);

            // 5. Log Activity
            ActivityLogger::logAdminAction(
                "Menambahkan admin baru: {$pegawai->nama} ({$pegawai->nip})"
            );

            return response()->json([
                'success' => true,
                'message' => 'Admin berhasil ditambahkan! Password default adalah NIP.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update admin role
     */
    public function updateRole(Request $request, $id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'is_super_admin' => 'required', // Removed boolean strict validation temporarily or ensure frontend sends 0/1
        ]);

        try {
            $admin = User::findOrFail($id);
            $oldRole = $admin->isSuperAdmin() ? 'Admin Super' : 'Admin Kepegawaian';
            
            // Cast input to boolean explicitly
            $isSuper = filter_var($request->is_super_admin, FILTER_VALIDATE_BOOLEAN);

            $admin->role = $isSuper ? 'super_admin' : 'admin_pegawai';
            $admin->save();

            $newRole = $admin->isSuperAdmin() ? 'Admin Super' : 'Admin Kepegawaian';

            // Log activity
            ActivityLogger::logAdminAction(
                "Mengubah role {$admin->nama_lengkap} dari {$oldRole} menjadi {$newRole}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Role admin berhasil diubah!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal mengubah role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete admin
     */
    public function destroy($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $admin = User::findOrFail($id);
            
            // Prevent self-deletion
            if ($admin->id == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus akun sendiri!'
                ], 403);
            }

            $adminName = $admin->nama_lengkap;
            $admin->delete();

            // Log activity
            ActivityLogger::logAdminAction(
                "Menghapus admin: {$adminName}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Admin berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus admin!'
            ], 500);
        }
    }
}

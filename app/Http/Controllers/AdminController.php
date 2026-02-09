<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of admins with search
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Order by name
        $query->orderBy('nama_lengkap', 'asc');

        // Pagination
        $admins = $query->paginate(10);

        return view('daftar_admin.index', compact('admins'));
    }

    /**
     * Update admin role
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'is_super_admin' => 'required|boolean',
        ]);

        $admin = User::findOrFail($id);
        $oldRole = $admin->isSuperAdmin() ? 'Admin Super' : 'Admin Kepegawaian';
        
        $admin->is_super_admin = $request->is_super_admin;
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
    }

    /**
     * Delete admin
     */
    public function destroy($id)
    {
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

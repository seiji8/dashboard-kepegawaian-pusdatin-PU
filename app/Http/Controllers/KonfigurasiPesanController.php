<?php

namespace App\Http\Controllers;

use App\Models\NotifikasiRules;
use Illuminate\Http\Request;

class KonfigurasiPesanController extends Controller
{
    public function index()
    {
        // Filter: Hanya tampilkan Notifikasi Pegawai (Exclude Notif Admin)
        $rules = NotifikasiRules::where('kategori', '!=', 'KGB Penjadwalan')->paginate(10);
        return view('konfigurasi_pesan.index', compact('rules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|string|max:255',
            'template_pesan' => 'required|string',
            'interval_hari' => 'nullable|integer', // 0 means Manual/Template
        ]);

        NotifikasiRules::create([
            'kategori' => $request->kategori,
            'template_pesan' => $request->template_pesan,
            'interval_hari' => $request->interval_hari ?? 0,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Aturan notifikasi berhasil ditambahkan!']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori' => 'required|string|max:255',
            'template_pesan' => 'required|string',
            'interval_hari' => 'nullable|integer',
        ]);

        $rule = NotifikasiRules::find($id);
        if (!$rule) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan!'], 404);
        }

        $rule->update([
            'kategori' => $request->kategori,
            'template_pesan' => $request->template_pesan,
            'interval_hari' => $request->interval_hari ?? 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Aturan notifikasi berhasil diperbarui!']);
    }

    public function destroy($konfigurasi_pesan)
    {
        $id = $konfigurasi_pesan; 

        // Debugging: Ensure ID is valid
        if (!$id) {
             return response()->json(['success' => false, 'message' => 'ID tidak valid!'], 400);
        }

        $rule = NotifikasiRules::where('id', $id)->first();
        
        if ($rule) {
            $rule->delete();
            return response()->json(['success' => true, 'message' => 'Aturan notifikasi berhasil dihapus!']);
        }
        
        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan! (ID: ' . $id . ')'], 404);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\NotifikasiRules;
use Illuminate\Http\Request;

class KonfigurasiPesanController extends Controller
{
    public function index()
    {
        // Tampilkan semua notifikasi termasuk sistem bawaan (Set ke 50 agar filter JS berfungsi dengan baik tanpa memotong halaman)
        $rules = NotifikasiRules::paginate(50);

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
        if (! $rule) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan!'], 404);
        }

        $rule->update([
            'kategori' => $request->kategori,
            'template_pesan' => $request->template_pesan,
            'interval_hari' => $request->interval_hari ?? 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Aturan notifikasi berhasil diperbarui!']);
    }

    public function destroy($id)
    {
        $rule = NotifikasiRules::find($id);
        if ($rule) {
            $rule->delete();

            return response()->json(['success' => true, 'message' => 'Aturan notifikasi berhasil dihapus!']);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan!'], 404);
    }
}

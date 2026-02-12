<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;

class DataPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pegawai::query();

        // Pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('jabatan_saat_ini', 'like', "%{$search}%");
            });
        }

        // Pagination 10 per halaman
        $pegawais = $query->paginate(10);

        return view('data_pegawai.index', compact('pegawais'));
    }

    public function show($nip)
    {
        $pegawai = Pegawai::with(['riwayat_angka_kredit'])->where('nip', $nip)->first();

        if (!$pegawai) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan'], 404);
        }

        // Hitung total angka kredit (contoh sederhana, bisa disesuaikan logicnya)
        $totalKredit = $pegawai->riwayat_angka_kredit->sum('total_kredit');

        return response()->json([
            'success' => true,
            'data' => [
                'nama' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'jabatan' => $pegawai->jabatan_saat_ini ?? '-',
                'tipe_jabatan' => $pegawai->tipe_jabatan ?? '-',
                'pangkat' => $pegawai->pangkat_golongan ?? '-',
                'jenjang' => $pegawai->jenjang ?? '-',
                'tmt_cpns' => $pegawai->tmt_cpns ? date('d/m/Y', strtotime($pegawai->tmt_cpns)) : '-',
                'angka_kredit' => $totalKredit,
                'no_hp' => $pegawai->no_hp ?? '-',
                'email' => $pegawai->email ?? '-',
            ]
        ]);
    }

    public function destroy($nip)
    {
        $pegawai = Pegawai::where('nip', $nip)->first();

        if ($pegawai) {
            $pegawai->delete();
            return response()->json(['success' => true, 'message' => 'Pegawai berhasil dihapus']);
        }

        return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan'], 404);
    }
}

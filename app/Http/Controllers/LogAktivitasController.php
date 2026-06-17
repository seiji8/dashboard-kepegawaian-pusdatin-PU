<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $query = Logs::with(['admin', 'pegawai'])->orderBy('waktu', 'desc');
        $query = $this->applyFilters($query, $request);

        // Pagination (simpan query params agar filter tetap aktif saat paginasi)
        $logs = $query->paginate(10)->appends($request->all());

        return view('log_aktivitas.index', compact('logs'));
    }

    public function exportPdf(Request $request)
    {
        $query = Logs::with(['admin', 'pegawai'])->orderBy('waktu', 'desc');
        $query = $this->applyFilters($query, $request);

        $logs = $query->get(); // Ambil semua data sesuai filter, tanpa paginasi

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('log_aktivitas.pdf', compact('logs', 'request'));

        return $pdf->download('Log_Aktivitas_'.date('Y-m-d_H-i').'.pdf');
    }

    private function applyFilters($query, Request $request)
    {
        // Filter: Jenis Pengguna (berdasarkan role admin atau sistem)
        if ($request->filled('jenis_pengguna')) {
            $jenis = $request->jenis_pengguna;
            if ($jenis === 'sistem') {
                $query->whereNull('user_id');
            } else {
                $query->whereHas('admin', function ($q) use ($jenis) {
                    $q->where('role', $jenis);
                });
            }
        }

        // Filter: Aksi (Cari di deskripsi)
        if ($request->filled('aksi')) {
            $query->where('deskripsi', 'LIKE', '%'.$request->aksi.'%');
        }

        // Filter: Dari Tanggal
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('waktu', '>=', $request->dari_tanggal);
        }

        // Filter: Sampai Tanggal
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('waktu', '<=', $request->sampai_tanggal);
        }

        return $query;
    }
}

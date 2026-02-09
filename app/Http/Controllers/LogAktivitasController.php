<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $query = Logs::with(['admin', 'pegawai'])->orderBy('waktu', 'desc');

        // Filter: Tipe Log
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter: Deskripsi (Search)
        if ($request->filled('search')) {
            $query->where('deskripsi', 'LIKE', '%' . $request->search . '%');
        }

        // Filter: Dari Tanggal
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('waktu', '>=', $request->dari_tanggal);
        }

        // Filter: Sampai Tanggal
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('waktu', '<=', $request->sampai_tanggal);
        }

        // Pagination
        $logs = $query->paginate(10);

        return view('log_aktivitas.index', compact('logs'));
    }

    public function destroy($id)
    {
        try {
            $log = Logs::findOrFail($id);
            $log->delete();

            return response()->json([
                'success' => true,
                'message' => 'Log berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus log'
            ], 500);
        }
    }
}

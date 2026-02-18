<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\NotifikasiRules; // Added
use App\Models\Logs; // Added
use App\Mail\ManualNotification; // Added
use Illuminate\Support\Facades\Mail; // Added
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

        // Ambil template manual
        $templates = NotifikasiRules::where('interval_hari', 0)->get();

        return view('data_pegawai.index', compact('pegawais', 'templates'));
    }

    public function show($nip)
    {
        $pegawai = Pegawai::with(['riwayat_angka_kredit'])->where('nip', $nip)->first();

        if (!$pegawai) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan'], 404);
        }

        // Hitung total angka kredit
        $totalKredit = $pegawai->riwayat_angka_kredit->sum('total_kredit');

        // Logic TMT 2 Tahun (Next KGB)
        $nextKgb = '-';
        $nextKgbDate = null;
        if ($pegawai->tmt_kgb_terakhir) {
            $nextKgbDate = \Carbon\Carbon::parse($pegawai->tmt_kgb_terakhir)->addYears(2);
            $nextKgb = $nextKgbDate->format('d/m/Y');
        } elseif ($pegawai->tmt_cpns) {
             // Jika belum ada KGB (CPNS baru), hitung dari CPNS
            $nextKgbDate = \Carbon\Carbon::parse($pegawai->tmt_cpns)->addYears(2);
            $nextKgb = $nextKgbDate->format('d/m/Y');
        }

        // Ambil dokumen yang belum diupload dari tracker yang aktif
        $missingDocs = [];

        // REVISI: Sertakan juga yang statusnya 'Proses' walaupun sudah dikonfirmasi, agar dokumen tetap muncul
        $activeTrackers = $pegawai->dashboard_tracker()
            ->where(function($q) {
                $q->whereNull('dikonfirmasi_at')
                  ->orWhere('status_saat_ini', 'Proses');
            })
            ->with('kelengkapan_dokumen')
            ->get();

        foreach ($activeTrackers as $tracker) {
            if ($tracker->kategori == 'KGB') {
                // LOGIC KHUSUS KGB (Continuous Check):
                // Cek apakah TMT KGB Terakhir di database SUDAH SAMA dengan Target Berikutnya?
                // Jika SUDAH SAMA, berarti data sudah diupdate -> Tidak perlu upload SK.
                // Jika BELUM SAMA, berarti pegawai masih di status lama -> Perlu upload SK Baru.

                // 1. Hitung Target TMT Berikutnya (karena logic +2 tahun, kita harus hati-hati)
                // Kita asumsikan TMT yang ada di DB sekarang adalah TMT LAMA.
                // Jadi Target-nya adalah TMT LAMA + 2 Tahun.
                
                $tmtLama = $pegawai->tmt_kgb_terakhir ? \Carbon\Carbon::parse($pegawai->tmt_kgb_terakhir) : ($pegawai->tmt_cpns ? \Carbon\Carbon::parse($pegawai->tmt_cpns) : null);
                
                if ($tmtLama) {
                    $targetTmt = $tmtLama->copy()->addYears(2);
                    
                    // Cek: Apakah TMT di database pegawai sudah update ke target ini?
                    // Karena $pegawai->tmt_kgb_terakhir yang kita ambil diatas adalah data CURRENT di DB.
                    // Jika data di DB masih data lama (misal 2024), dan targetnya 2026.
                    // Maka "SK KGB 2026" WAJIB muncul.
                    
                    // Logic sederhananya: Selama ada Tracker KGB Aktif, berarti sistem mendeteksi sudah waktunya naik.
                    // Dan selama TMT di DB belum berubah maju, berarti SK belum diterima sistem.
                    
                    // Format Bulan Tahun SK Target
                    $bulanIndo = [
                        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    $bulan = $bulanIndo[$targetTmt->month];
                    $tahun = $targetTmt->year;

                    $missingDocs[] = [
                        'kategori' => 'KGB',
                        'nama_dokumen' => "SK KGB {$bulan} {$tahun}"
                    ];
                }

            } else {
                // Logic existing untuk kategori lain (KP Jafung, dll)
                $docs = $tracker->kelengkapan_dokumen->where('is_uploaded', false);
                foreach ($docs as $doc) {
                    $missingDocs[] = [
                        'kategori' => $tracker->kategori,
                        'nama_dokumen' => $doc->nama_dokumen
                    ];
                }
            }
        }

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
                'next_kgb' => $nextKgb,
                'missing_documents' => $missingDocs
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

    public function sendManualNotification(Request $request, $nip)
    {
        $pegawai = Pegawai::where('nip', $nip)->first();

        if (!$pegawai) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan'], 404);
        }

        if (!$pegawai->email) {
            return response()->json(['success' => false, 'message' => 'Pegawai ini tidak memiliki email terdaftar'], 400);
        }

        $subject = 'Pemberitahuan Kepegawaian';
        $message = $request->input('message');
        $templateId = $request->input('template_id');

        // Jika pakai template
        if ($templateId) {
            $rule = NotifikasiRules::find($templateId);
            if ($rule) {
                $subject = $rule->kategori;
                $message = $rule->template_pesan;
            }
        }

        // Custom Message Override
        if ($request->input('custom_message')) {
            $message = $request->input('custom_message');
        }

        if (!$message) {
             return response()->json(['success' => false, 'message' => 'Pesan tidak boleh kosong'], 400);
        }

        // Replace Placeholders
        $placeholders = [
            '{nama}' => $pegawai->nama,
            '{nip}' => $pegawai->nip,
            '{jabatan}' => $pegawai->jabatan_saat_ini ?? '-',
            '{pangkat}' => $pegawai->pangkat_saat_ini ?? '-',
        ];

        foreach ($placeholders as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        try {
            Mail::to($pegawai->email)->send(new ManualNotification($pegawai, $subject, $message));

            // Log Aktivitas
            Logs::create([
                'tipe' => 'NOTIF_SENT',
                'deskripsi' => "Mengirim notifikasi manual ke Pegawai {$pegawai->nama}",
                'target_nip' => $pegawai->nip,
                'user_id' => auth()->id(), // Admin yang sedang login
                'waktu' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Email berhasil dikirim ke ' . $pegawai->email]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }
    }
}

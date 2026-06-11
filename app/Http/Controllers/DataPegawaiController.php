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
        if ($request->has('update_danang')) {
            $p = \App\Models\Pegawai::where('nama', 'Danang Setiadi')->first();
            $p->pangkat_golongan = 'III/b';
            $p->tmt_pangkat_terakhir = '2026-04-01';
            $p->sk_pangkat_terakhir = 'sk_pangkat_baru.pdf';
            $p->save();
            \Artisan::call('tracker:run');
            return response('Data updated and tracker run.', 200);
        }

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

        // Filter Tipe Jabatan
        if ($request->has('filter_tipe') && $request->filter_tipe != '') {
            $filterTipe = $request->filter_tipe;
            $query->where('tipe_jabatan', 'like', "%{$filterTipe}%");
        }

        // Default sorting A-Z berdasarkan nama
        $query->orderBy('nama', 'asc');

        // Pagination 10 per halaman (dengan cache query terlampir)
        $pegawais = $query->paginate(10)->withQueryString();

        // Ambil template manual
        $templates = NotifikasiRules::where('interval_hari', 0)->get();

        return view('data_pegawai.index', compact('pegawais', 'templates'));
    }

    public function show(Request $request, $nip)
    {
        $pegawai = Pegawai::with([
            'riwayat_angka_kredit', 
            'riwayat_jabatan',
            'logs' => function($q) {
                $q->with('admin')->orderBy('waktu', 'desc');
            }
        ])->where('nip', $nip)->first();

        if (!$pegawai) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan'], 404);
        }

        // AK Terbaru (Bukan sum, tapi nilai akumulasi dari data terakhir)
        $latestAK = $pegawai->riwayat_angka_kredit->sortByDesc('tmt_angka_kredit')->first();
        $totalKredit = $latestAK ? $latestAK->total_kredit : 0;

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
        $allDocs = [];

        // REVISI: Sertakan juga yang statusnya 'Proses' walaupun sudah dikonfirmasi, agar dokumen tetap muncul
        $activeTrackers = $pegawai->dashboard_tracker()
            ->where(function($q) {
                $q->whereNull('dikonfirmasi_at')
                  ->orWhereIn('status_saat_ini', ['Proses', 'Upload E-HRM', 'Menunggu UKOM', 'Menunggu SKP']);
            })
            ->with('kelengkapan_dokumen')
            ->get();

        foreach ($activeTrackers as $tracker) {
                $targetPangkat = $pegawai->pangkat_golongan ? (
                    ['I/a'=>'I/b','I/b'=>'I/c','I/c'=>'I/d','I/d'=>'II/a',
                     'II/a'=>'II/b','II/b'=>'II/c','II/c'=>'II/d','II/d'=>'III/a',
                     'III/a'=>'III/b','III/b'=>'III/c','III/c'=>'III/d','III/d'=>'IV/a',
                     'IV/a'=>'IV/b','IV/b'=>'IV/c','IV/c'=>'IV/d','IV/d'=>'IV/e'][$pegawai->pangkat_golongan] ?? 'Baru'
                ) : 'Baru';

                $isUploadEhrm = ($tracker->status_saat_ini === 'Upload E-HRM');
                
                $namaSkPangkat = $isUploadEhrm ? "SK Pangkat Baru (Tujuan: {$targetPangkat})" : "SK Pangkat Saat Ini";
                $namaSkJabatan = $isUploadEhrm ? "SK Jabatan Baru" : "SK Jabatan Saat Ini";

            if ($tracker->kategori == 'KGB') {
                // LOGIC KHUSUS KGB (Continuous Check):
                $tmtLama = $pegawai->tmt_kgb_terakhir ? \Carbon\Carbon::parse($pegawai->tmt_kgb_terakhir) : ($pegawai->tmt_cpns ? \Carbon\Carbon::parse($pegawai->tmt_cpns) : null);
                
                if ($tmtLama) {
                    $targetTmt = $tmtLama->copy()->addYears(2);
                    $bulanIndo = [
                        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    $bulan = $bulanIndo[$targetTmt->month];
                    $tahun = $targetTmt->year;

                    $missingDocs[] = [
                        'kategori' => 'KGB',
                        'nama_dokumen' => "SK KGB Baru ({$bulan} {$tahun})"
                    ];
                    $allDocs[] = [
                        'kategori' => 'KGB',
                        'nama_dokumen' => "SK KGB Baru ({$bulan} {$tahun})",
                        'is_uploaded' => false
                    ];
                }
            } elseif ($tracker->kategori == 'KJ_Jafung') {
                $skp = !(empty($pegawai->arsip_skp_2_tahun) || count($pegawai->arsip_skp_2_tahun) < 2);
                $allDocs[] = ['kategori' => 'KJ_Jafung', 'nama_dokumen' => "SKP 2 Tahun Terakhir", 'is_uploaded' => $skp];
                if (!$skp) $missingDocs[] = ['kategori' => 'KJ_Jafung', 'nama_dokumen' => "SKP 2 Tahun Terakhir"];
                
                $docsUploaded = $tracker->kelengkapan_dokumen->where('is_uploaded', true)->pluck('nama_dokumen')->toArray();
                $riwJabatanMatch = $pegawai->riwayat_jabatan
                    ->whereNotNull('file_sk')
                    ->where('file_sk', '!=', '')
                    ->sortByDesc('tmt_jabatan')
                    ->first();
                
                $skjabatan = $isUploadEhrm ? false : (($riwJabatanMatch != null) || in_array("SK Jabatan Terakhir", $docsUploaded));
                $allDocs[] = ['kategori' => 'KJ_Jafung', 'nama_dokumen' => $namaSkJabatan, 'is_uploaded' => $skjabatan];
                if (!$skjabatan) $missingDocs[] = ['kategori' => 'KJ_Jafung', 'nama_dokumen' => $namaSkJabatan];
            } elseif ($tracker->kategori == 'KP_Jafung') {
                $skpangkat = $isUploadEhrm ? false : !empty($pegawai->sk_pangkat_terakhir);
                $allDocs[] = ['kategori' => 'KP_Jafung', 'nama_dokumen' => $namaSkPangkat, 'is_uploaded' => $skpangkat];
                if (!$skpangkat) $missingDocs[] = ['kategori' => 'KP_Jafung', 'nama_dokumen' => $namaSkPangkat];
                
                $skp = !(empty($pegawai->arsip_skp_2_tahun) || count($pegawai->arsip_skp_2_tahun) < 2);
                $allDocs[] = ['kategori' => 'KP_Jafung', 'nama_dokumen' => "SKP 2 Tahun Terakhir", 'is_uploaded' => $skp];
                if (!$skp) $missingDocs[] = ['kategori' => 'KP_Jafung', 'nama_dokumen' => "SKP 2 Tahun Terakhir"];
            } elseif ($tracker->kategori == 'KP_Struktural') {
                $skpangkat = $isUploadEhrm ? false : !empty($pegawai->sk_pangkat_terakhir);
                $allDocs[] = ['kategori' => 'KP_Struktural', 'nama_dokumen' => $namaSkPangkat, 'is_uploaded' => $skpangkat];
                if (!$skpangkat) $missingDocs[] = ['kategori' => 'KP_Struktural', 'nama_dokumen' => $namaSkPangkat];
                
                $riwJabatanMatch = $pegawai->riwayat_jabatan
                    ->where('kd_eselon', $pegawai->kd_eselon)
                    ->whereNotNull('file_sk')
                    ->where('file_sk', '!=', '')
                    ->sortByDesc('tmt_jabatan')
                    ->first();
                
                $docsUploaded = $tracker->kelengkapan_dokumen->where('is_uploaded', true)->pluck('nama_dokumen')->toArray();
                $skjabatan = $isUploadEhrm ? false : (($riwJabatanMatch != null) || in_array("SK Jabatan Terakhir", $docsUploaded));
                $allDocs[] = ['kategori' => 'KP_Struktural', 'nama_dokumen' => $namaSkJabatan, 'is_uploaded' => $skjabatan];
                if (!$skjabatan) $missingDocs[] = ['kategori' => 'KP_Struktural', 'nama_dokumen' => $namaSkJabatan];
            } elseif ($tracker->kategori == 'KP_Reguler') {
                $skpangkat = $isUploadEhrm ? false : !empty($pegawai->sk_pangkat_terakhir);
                $allDocs[] = ['kategori' => 'KP_Reguler', 'nama_dokumen' => $namaSkPangkat, 'is_uploaded' => $skpangkat];
                if (!$skpangkat) $missingDocs[] = ['kategori' => 'KP_Reguler', 'nama_dokumen' => $namaSkPangkat];
            } else {
                // Logic existing untuk kategori lain (KGB, dll)
                $docs = $tracker->kelengkapan_dokumen;
                foreach ($docs as $doc) {
                    $isUploaded = (bool)$doc->is_uploaded;
                    if ($doc->nama_dokumen == "SK Pangkat Terakhir" && !empty($pegawai->sk_pangkat_terakhir)) {
                        $isUploaded = true;
                    }
                    if ($doc->nama_dokumen == "SK Tugas Belajar" && $pegawai->riwayat_tubel->whereNotNull('arsip_izin_belajar')->where('arsip_izin_belajar', '!=', '')->count() > 0) {
                        $isUploaded = true;
                    }

                    $allDocs[] = [
                        'kategori' => $tracker->kategori,
                        'nama_dokumen' => $doc->nama_dokumen,
                        'is_uploaded' => $isUploaded
                    ];
                    if (!$isUploaded) {
                        $missingDocs[] = [
                            'kategori' => $tracker->kategori,
                            'nama_dokumen' => $doc->nama_dokumen
                        ];
                    }
                }
            }
        }

        $trackerStatus = null;
        $trackerId = null;
        $trackerKeterangan = null;
        if ($request->has('kategori')) {
            $cat = $request->kategori;
            $specificTracker = collect($activeTrackers)->where('kategori', $cat)->first();
            if ($specificTracker) {
                $trackerStatus = $specificTracker->status_saat_ini;
                $trackerId = $specificTracker->id;
                $trackerKeterangan = $specificTracker->keterangan;
            }
        }

        // TUBEL-specific data
        $tubelData = null;
        if ($request->input('kategori') === 'TUBEL') {
            $tubelAktif = \App\Models\RiwayatTubel::where('nip', $nip)
                ->whereNotNull('tanggal_mulai')
                ->orderBy('tanggal_mulai', 'desc')
                ->first();
            if ($tubelAktif) {
                $selesaiEfektif = $tubelAktif->tanggal_selesai_efektif;
                $tubelData = [
                    'tanggal_mulai'   => $tubelAktif->tanggal_mulai ? $tubelAktif->tanggal_mulai->format('d/m/Y') : '-',
                    'tanggal_selesai' => $selesaiEfektif ? $selesaiEfektif->format('d/m/Y') : '-',
                    'pendidikan'      => $tubelAktif->pendidikan ?? '-',
                ];
            }
        }

        $history = $pegawai->logs->map(function($log) {
            return [
                'tipe' => $log->tipe,
                'deskripsi' => $log->deskripsi,
                'admin_name' => $log->admin ? $log->admin->nama_lengkap : 'Sistem',
                'waktu' => \Carbon\Carbon::parse($log->waktu)->translatedFormat('d M Y, H:i') . ' WIB',
                'waktu_ago' => \Carbon\Carbon::parse($log->waktu)->diffForHumans()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'nama'             => $pegawai->nama,
                'nip'              => $pegawai->nip,
                'jabatan'          => $pegawai->jabatan_saat_ini ?? '-',
                'tipe_jabatan'     => $pegawai->tipe_jabatan ?? '-',
                'pangkat'          => $pegawai->pangkat_golongan ?? '-',
                'jenjang'          => $pegawai->jenjang ?? '-',
                'tmt_cpns'         => $pegawai->tmt_cpns ? date('d/m/Y', strtotime($pegawai->tmt_cpns)) : '-',
                'angka_kredit'     => $totalKredit,
                'no_hp'            => $pegawai->no_hp ?? '-',
                'email'            => $pegawai->email ?? '-',
                'next_kgb'         => $nextKgb,
                'tmt_kgb_terakhir' => $pegawai->tmt_kgb_terakhir ? date('d/m/Y', strtotime($pegawai->tmt_kgb_terakhir)) : '-',
                'missing_documents'=> $missingDocs,
                'all_documents'    => $allDocs ?? [],
                'tracker_status'   => $trackerStatus,
                'tracker_id'       => $trackerId,
                'tracker_keterangan' => $trackerKeterangan,
                'tubel_data'       => $tubelData,
                'history'          => $history,
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

        // Ambil list riwayat diklat bermasalah untuk placeholder {detail_diklat}
        $listNamaDiklat = "";
        $riwayatDiklat = \App\Models\RiwayatDiklat::where('nip', $pegawai->nip)->get();
        $anomaliDiklat = $riwayatDiklat->filter(function ($d) {
            return $d->status_diklat == 1
                && empty($d->file_sertifikat) && empty($d->arsip);
        });
        foreach ($anomaliDiklat as $d) {
            $listNamaDiklat .= "- " . $d->nama_diklat . "\n";
        }
        $listNamaDiklat = trim($listNamaDiklat);

        // Replace Placeholders
        $placeholders = [
            '{nama}' => $pegawai->nama,
            '{nip}' => $pegawai->nip,
            '{jabatan}' => $pegawai->jabatan_saat_ini ?? '-',
            '{pangkat}' => $pegawai->pangkat_saat_ini ?? '-',
            '{detail_diklat}' => $listNamaDiklat,
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

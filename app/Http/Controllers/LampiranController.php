<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LampiranCetakSurat;
use App\Models\DashboardTracker;
use Illuminate\Support\Facades\Storage;

class LampiranController extends Controller
{
    /**
     * Ambil daftar lampiran milik 1 tracker, dikelompokkan per judul.
     */
    public function index(string $tracker_id)
    {
        $tracker = DashboardTracker::with('pegawai')->findOrFail($tracker_id);

        $lampiran = LampiranCetakSurat::where('dashboard_tracker_id', $tracker_id)
            ->whereNotNull('file_path')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return [
                    'id'             => $item->id,
                    'judul_lampiran' => $item->judul_lampiran ?? $item->nama_dokumen,
                    'nama_dokumen'   => $item->nama_dokumen,
                    'file_path'      => $item->file_path,
                    'mime_type'      => $item->mime_type,
                    'urutan'         => $item->urutan,
                    'halaman_cetak'  => $item->halaman_cetak,
                    'ukuran_bytes'   => $item->ukuran_bytes,
                    'url_preview'    => $item->file_path ? Storage::url($item->file_path) : null,
                ];
            });

        return response()->json([
            'success'  => true,
            'lampiran' => $lampiran,
            'pegawai'  => [
                'nama'     => $tracker->pegawai->nama ?? '-',
                'nip'      => $tracker->pegawai->nip ?? '-',
                'kategori' => $tracker->kategori,
            ],
        ]);
    }

    /**
     * Upload file lampiran baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tracker_id'     => 'required|exists:dashboard_tracker,id',
            'judul_lampiran' => 'nullable|string|max:255',
            'file'           => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $tracker = DashboardTracker::with('pegawai')->findOrFail($request->tracker_id);
        $nip     = $tracker->pegawai->nip ?? 'unknown';

        $file     = $request->file('file');
        $mimeType = $file->getMimeType();
        $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext      = $file->getClientOriginalExtension();
        
        $judulLampiran = $request->judul_lampiran ?: 'Lampiran_' . time();
        $safeJudul = preg_replace('/[^A-Za-z0-9\-_]/', '_', $judulLampiran);
        $filename  = $safeJudul . '_' . time() . '.' . $ext;
        $folder    = 'lampiran/' . $nip;

        // Jika gambar dan fungsi kompresi (GD) tersedia: kompres pakai GD bawaan PHP
        if (in_array($mimeType, ['image/jpeg', 'image/png']) && function_exists('imagecreatefromjpeg') && function_exists('imagecreatefrompng')) {
            $compressedPath = $this->compressImage($file->getRealPath(), $mimeType, $folder, $filename);
            $storedPath     = $compressedPath;
            $mimeType       = 'image/jpeg'; // Paksa set ke jpeg karena output compressImage selalu jpg
        } else {
            // File PDF atau fungsi kompresi tidak tersedia: simpan file aslinya langsung
            $storedPath = $file->storeAs($folder, $filename, 'public');
        }

        // Hitung urutan berikutnya
        $maxUrutan = LampiranCetakSurat::where('dashboard_tracker_id', $request->tracker_id)
            ->whereNotNull('file_path')
            ->max('urutan') ?? 0;

        $halamanCetak = $request->input('halaman_cetak', 1);

        $lampiran = LampiranCetakSurat::create([
            'dashboard_tracker_id' => $request->tracker_id,
            'nip'                  => $nip,
            'nama_dokumen'         => $request->judul_lampiran ?: $judulLampiran, // Fallback if null
            'judul_lampiran'       => $judulLampiran,
            'file_path'            => $storedPath,
            'mime_type'            => $mimeType,
            'urutan'               => $maxUrutan + 1,
            'halaman_cetak'        => $halamanCetak,
            'ukuran_bytes'         => Storage::disk('public')->size($storedPath),
            'is_uploaded'          => true,
            'status_verifikasi'    => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lampiran berhasil diupload!',
            'data'    => [
                'id'             => $lampiran->id,
                'judul_lampiran' => $lampiran->judul_lampiran,
                'mime_type'      => $lampiran->mime_type,
                'urutan'         => $lampiran->urutan,
                'ukuran_bytes'   => $lampiran->ukuran_bytes,
                'url_preview'    => Storage::url($storedPath),
            ],
        ]);
    }

    /**
     * Hapus lampiran (file fisik + record DB).
     */
    public function destroy(string $id)
    {
        $lampiran = LampiranCetakSurat::findOrFail($id);

        if ($lampiran->file_path && Storage::disk('public')->exists($lampiran->file_path)) {
            Storage::disk('public')->delete($lampiran->file_path);
        }

        $lampiran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lampiran berhasil dihapus.',
        ]);
    }

    /**
     * Hapus semua lampiran untuk tracker tertentu.
     */
    public function clearAll(string $tracker_id)
    {
        $lampirans = LampiranCetakSurat::where('dashboard_tracker_id', $tracker_id)
            ->whereNotNull('file_path')
            ->get();

        foreach ($lampirans as $lampiran) {
            if (Storage::disk('public')->exists($lampiran->file_path)) {
                Storage::disk('public')->delete($lampiran->file_path);
            }
            $lampiran->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Semua lampiran berhasil dibersihkan.',
        ]);
    }

    /**
     * Update urutan lampiran.
     */
    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $id) {
            LampiranCetakSurat::where('id', $id)->update(['urutan' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Kompres gambar menggunakan GD Library bawaan PHP (tanpa install package).
     */
    private function compressImage(string $sourcePath, string $mimeType, string $folder, string $filename): string
    {
        $maxWidth = 1400;
        $quality  = 85;

        // Load gambar dari file
        if ($mimeType === 'image/png') {
            $src = imagecreatefrompng($sourcePath);
        } else {
            $src = imagecreatefromjpeg($sourcePath);
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Hitung dimensi baru
        if ($origW > $maxWidth) {
            $ratio  = $maxWidth / $origW;
            $newW   = $maxWidth;
            $newH   = (int) ($origH * $ratio);
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        // Buat gambar baru dengan ukuran yang sudah dihitung
        $dst = imagecreatetruecolor($newW, $newH);

        // Handle transparansi untuk PNG
        if ($mimeType === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // Simpan ke temporary file
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('img_') . '.jpg';
        imagejpeg($dst, $tmpPath, $quality);

        imagedestroy($src);
        imagedestroy($dst);

        // Store ke Laravel Storage
        $storedPath = $folder . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
        Storage::disk('public')->put($storedPath, file_get_contents($tmpPath));
        unlink($tmpPath);

        return $storedPath;
    }

    /**
     * Update judul lampiran (opsional).
     */
    public function updateJudul(Request $request, string $id)
    {
        $request->validate([
            'judul_lampiran' => 'nullable|string|max:255',
        ]);

        $lampiran = LampiranCetakSurat::findOrFail($id);
        $lampiran->update([
            'judul_lampiran' => $request->judul_lampiran ?? $lampiran->nama_dokumen,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Judul lampiran berhasil diperbarui!',
            'judul'   => $lampiran->judul_lampiran,
        ]);
    }
}
